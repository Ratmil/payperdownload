<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );

class ConfigObject
{
	var $config = null;
	function load()
	{
		if(!$this->config)
		{
			$db = JFactory::getDBO();
			$db->setQuery("SELECT * FROM #__payperdownloadplus_config", 0, 1);
			$this->config = $db->loadObject();
		}
	}
	
	function get($param, $defaultValue = null)
	{
		if(!$this->config)
			$this->load();
		if(!$this->config)
			return $defaultValue;
		return $this->config->{$param};
	}
}

class PayPerDownloadModelPayResource extends JModelLegacy
{ 
	var $config = null;

	function getConfig()
	{
		if($this->config)
			return $this->config;
		return new ConfigObject();
	}

	function getTaxPercent()
	{
		$config = $this->getConfig();
		return $config->get('tax_rate', 0);
	}
	
	function getResource($resource_id)
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT * FROM #__payperdownloadplus_resource_licenses WHERE resource_license_id = " . (int)$resource_id);
		$resource = $db->loadObject();
		if($this->cleanHtml($resource->payment_header) == "")
			$resource->payment_header = $this->getPaymentHeader();
		return $resource;
	}
	
	function getPaymentHeader()
	{
		$config = $this->getConfig();
		return $config->get('resource_payment_header', "");
	}
	
	function cleanHtml($text)
	{
		$text = preg_replace("/<\/?[a-zA-Z0-9]+[^>]*>/", "", $text);
		$text = preg_replace("/&[a-zA-Z]{1,6};/", "", $text);
		$text = preg_replace('/\s/', "", $text);
		return trim($text);
	}
	
	function getUsePayPluginConfig()
	{
		$config = $this->getConfig();
		return $config->get('usepayplugin', 0);
	}
	
	function getUsePaypal()
	{
		$config = $this->getConfig();
		return $config->get('usepaypal', 1);
	}
	
	function getUseDiscount()
	{
		$config = $this->getConfig();
		return $config->get('apply_discount', 1);
	}
	
	function getAskEmail()
	{
		$config = $this->getConfig();
		return $config->get('askemail', 0);
	}
	
	function getLoginURL($returnUrl)
	{
		$config = $this->getConfig();
		$url = $config->get('loginurl', "");
		if(!$url)
		{
			$user_component = "com_users";
			$version = new JVersion;
			if($version->RELEASE == "1.5")
			{
				$user_component = "com_user";
			}
			$url = "index.php?option=$user_component&view=login";
		}
		$return_param = $config->get('return_param', 'return');
		if($return_param)
			$url .= "&" . $return_param. "=" . base64_encode($returnUrl);
		return $url;
	}
	
	function getPaymentInfo()
	{
		$config = $this->getConfig();
		$paymentInfo = new StdClass();
		$paymentInfo->paypal_account = $config->get('paypalaccount');
		$paymentInfo->paymentnotificationemail = $config->get('paymentnotificationemail');
		$paymentInfo->test_mode = $config->get('testmode', 1);
		$paymentInfo->usesimulator = $config->get('usesimulator', 0);
		return $paymentInfo;
	}
	
	function startTransaction()
	{
		$db = JFactory::getDBO();
		$db->setQuery("START TRANSACTION");
		$db->query();
	}
	
	function commitTransaction()
	{
		$db = JFactory::getDBO();
		$db->setQuery("COMMIT");
		$db->query();
	}
	
	function getResourceName($resource_id)
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT resource_license_id, resource_name FROM #__payperdownloadplus_resource_licenses WHERE resource_license_id = " . (int)$resource_id);
		$resource = $db->loadObject();
		if($resource)
			return $resource->resource_name;
		else
			return "";
	}
	
	function handleResponse()
	{
		require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
		PayPerDownloadPlusDebug::debug("Called from Paypal or other payment gateway, buying download link");
		$this->startTransaction();
		$usePayPlugin = $this->getUsePayPluginConfig();
		$payed = 0;
		$dealt = false;
		if($usePayPlugin)
		{
			$gateway = JRequest::getVar('gateway');
			PayPerDownloadPlusDebug::debug("Using payment plugin, gateway: " . $gateway);
			$db = JFactory::getDBO();
			JPluginHelper::importPlugin("payperdownloadplus");
			$dispatcher	= JDispatcher::getInstance();
			$dealt = false;
			$dispatcher->trigger('onPaymentReceived', array($gateway, &$dealt, &$payed, &$user_id, &$license_id, &$resource_id, &$transactionId,
				&$response, &$validate_response, &$status, &$amount, &$tax, &$fee, &$currency));
			if($dealt)
			{
				PayPerDownloadPlusDebug::debug("Got payment for gateway " . $transactionId);
				PayPerDownloadPlusDebug::debug("Response " . $response);
				if(!$transactionId)
				{
					$this->commitTransaction();
					return;
				}
				$payer_email = "";
				$download_id = 0;
				$dispatcher->trigger('onGetDownloadLinkId', array($transactionId, &$download_id));
				$dispatcher->trigger('onGetPayerEmail', array($transactionId, &$payer_email));
				$payed = $payed ? 1 : 0;
				$notify_email = $payer_email;
				$txn_id = $db->escape($transactionId);
				$response = $db->escape($response);
				$validate_response = $db->escape($validate_response);
				$status = $db->escape($status);
				$amount = $db->escape((float)$amount);
				$fee = $db->escape((float)$fee);
				$tax = $db->escape((float)$tax);
				$mc_currency = $db->escape($currency);
				$esc_user_email = $db->escape($payer_email);
				$resource_id = (int)$resource_id;
				$resource_id_from_download_id = $this->getResourceFromDownloadLink($download_id);
				$query = "INSERT INTO 
					#__payperdownloadplus_payments(user_id, user_email, resource_id, payed, payment_date, txn_id, response, validate_response, 
						status, amount, tax, fee, currency)
					VALUES(NULL, '$esc_user_email', $resource_id, $payed, NOW(), '$txn_id', '$response', '$validate_response', 
						'$status', '$amount', '$tax', '$fee', '$mc_currency')";
				$db->setQuery( $query );
				$db->query();
				$payment_id = $db->insertid();
			}
		}
		if(!$dealt && $this->getUsePaypal())
		{
			PayPerDownloadPlusDebug::debug("Using paypal gateway");
			$paymentInfo = $this->getPaymentInfo();
			$receiver_email = JRequest::getVar('receiver_email');
			$business = JRequest::getVar('business');
			if(trim(strtoupper($paymentInfo->paypal_account)) != trim(strtoupper($receiver_email)) && 
				trim(strtoupper($paymentInfo->paypal_account)) != trim(strtoupper($business)))
			{
				$this->commitTransaction();
				PayPerDownloadPlusDebug::debug("Configured paypal account is different that target account");
				return;
			}
		
			$req = 'cmd=_notify-validate';
			$text = "";
			foreach ($_POST as $key => $value)
			{
				$save_value = JRequest::getVar($key);
				$text .= "(" . $key . " = " . $save_value . ")\r\n";
				$req .= "&" . $key . "=" . urlencode($save_value);
			}
			$payer_email = JRequest::getVar('payer_email');
			$db = JFactory::getDBO();
			$txn_id = JRequest::getVar('txn_id');
			if(!$this->isTransactionPayed($txn_id))
			{
				$validate_response = $this->validatePayment($req, JRequest::getInt('test_ipn', 0), $paymentInfo);
				$payed = (strcmp ($validate_response, "VERIFIED") == 0) ? 1 : 0;
			}
			else
			{
				$this->commitTransaction();
				PayPerDownloadPlusDebug::debug("Invalid payment");
				return;
			}
				
			if(!$payed)
			{
				$this->commitTransaction();
				PayPerDownloadPlusDebug::debug("Payment was not successfull for some reason");
				return;
			}
			
			$txn_id = $db->escape($txn_id);
			$notify_email = "";
			$text = $db->escape($text);
			$validate_response = $db->escape($validate_response);
			$download_id = JRequest::getInt('custom');
			$resource_id = JRequest::getInt('item_number');
			$resource_id_from_download_id = $this->getResourceFromDownloadLink($download_id);
			$status = '';
			if($resource_id != $resource_id_from_download_id)
			{
				$payed = false;
				$status = 'Invalid download id received;';
			}
			$payed_price = 0;
			if($payed)
			{
				$payed_price = JRequest::getFloat('mc_gross');
				$currency_code = JRequest::getVar('mc_currency');
				$payed = $this->validateResource($resource_id, $download_id, $payed_price, $currency_code) ? 1 : 0;
				if($payed)
				{
					$status = trim(strtoupper(JRequest::getVar('payment_status')));
					$payed = ($status == 'COMPLETED') ? 1 : 0;
				}
			}
			$status = $db->escape($status);
			$amount = $db->escape(JRequest::getFloat('mc_gross'));
			$fee = $db->escape(JRequest::getFloat('mc_fee', 0));
			$tax = $db->escape(JRequest::getFloat('tax'));
			$mc_currency = $db->escape(JRequest::getVar('mc_currency'));
			$esc_receiver_email = $db->escape($receiver_email);
			$esc_user_email = $db->escape($payer_email);
			$query = "INSERT INTO 
				#__payperdownloadplus_payments(user_id, user_email, resource_id, payed, payment_date, txn_id, response, validate_response, status, amount, tax, fee, currency, receiver_email)
				VALUES(NULL, '$esc_user_email', $resource_id, $payed, NOW(), '$txn_id', '$text', '$validate_response', '$status', '$amount', '$tax', '$fee', '$mc_currency', '$esc_receiver_email')";
			$db->setQuery( $query );
			$query_result = $db->query();
			$payment_id = $db->insertid();
			if($query_result)
				PayPerDownloadPlusDebug::debug("Payment saved");
			else
				PayPerDownloadPlusDebug::debug("Payment not saved");
		}
		$download_link = false;
		if($payed)
		{
			$returnUrl = JRequest::getVar('r');
			$returnUrl = base64_decode($returnUrl);
			$download_link = $this->setDownloadLinkPayed($download_id, $resource_id,
				$payer_email, $notify_email, $returnUrl);
			$resource_name = $this->getResourceName( $resource_id_from_download_id ); 
			$this->notifyPayment($payment_id, $payer_email, $download_link, $notify_email, $download_id, $resource_name);
		}
		else
			PayPerDownloadPlusDebug::debug("Payment was not finally successfull for some reason");
		$this->commitTransaction();
		$redirect = JRequest::getVar('redirect', '');
		if($redirect)
		{
			if($download_link)
				$redirect = $download_link;
			else
				$redirect = base64_decode($redirect);
			$mainframe = JFactory::getApplication();
			$mainframe->redirect($redirect);
		}
		else
			exit;
	}
	
	function createDownloadLink($resource_id)
	{
		$resource_id = (int)$resource_id;
		$db = JFactory::getDBO();
		//Delete expired and not paid download links 
		$db->setQuery("DELETE FROM #__payperdownloadplus_download_links WHERE expiration_date < NOW() || (payed = 0 AND TO_DAYS(NOW()) - TO_DAYS(creation_date) > 1)");
		$db->query();
		
		$secret_word = $this->getRandom($resource_id . $resource_id);
		$random_value = $this->getRandom($resource_id);
		
		$e_secret_word = $db->escape($secret_word);
		$e_random_value = $db->escape($random_value);
		
		$query = "INSERT INTO #__payperdownloadplus_download_links
			(resource_id, payed, creation_date, secret_word, random_value)
			VALUES($resource_id, 0, NOW(), '$e_secret_word', '$e_random_value')";
		$db->setQuery($query);
		$db->query();
		$download_id = (int)$db->insertid();
		$downloadLink = new stdClass();
		if($downloadLink)
		{
			$downloadLink->downloadId = $download_id;
			$downloadLink->secret_word = $secret_word;
			$downloadLink->random_value = $random_value;
			$downloadLink->accessCode = $download_id . "-" . sha1($secret_word . $random_value) . 
				"-" . $random_value;
		}
		return $downloadLink;
	}
	
	function setDownloadLinkPayed($download_id, $resource_id, 
		$payer_email, $user_email, $returnUrl)
	{
		$download_id = (int)$download_id;
		$resource_id = (int)$resource_id;
		$db = JFactory::getDBO();
		
		$query = "SELECT download_expiration, max_download FROM #__payperdownloadplus_resource_licenses WHERE resource_license_id = " . $resource_id;
		$db->setQuery( $query );
		$res = $db->loadObject();
		$days = (int)$res->download_expiration;
		if($days <= 0)
			$days = 365;
		$max_downloads = (int)$res->max_download;
		
		$payer_email = $db->escape($payer_email);
		$user_email = $db->escape($user_email);
		
		if($download_id)
		{
			$query = "UPDATE #__payperdownloadplus_download_links 
				SET expiration_date = DATE_ADD(NOW(), INTERVAL $days DAY),
				user_email = '$user_email', payer_email = '$payer_email',
				link_max_downloads = $max_downloads,
				payed = 1
				WHERE download_id = " . $download_id;
			$db->setQuery($query);
			$db->query();
			$query = "SELECT secret_word, random_value, item_id, coupon_code FROM #__payperdownloadplus_download_links 
				WHERE download_id = " . $download_id;
			$db->setQuery($query);
			$downloadLink = $db->loadObject();
			if($downloadLink)
			{
				/*if($downloadLink->coupon_code)
				{
					$coupon_code = $db->escape($downloadLink->coupon_code);
					$query = "SELECT user_id FROM #__payperdownloadplus_coupons_users 
						WHERE coupon_code = '$coupon_code' AND user_id = $user_id";
					$db->setQuery($query);
					$result = $db->loadResult();
					if(!$result)
					{
						$query = "INSERT INTO #__payperdownloadplus_coupons_users(coupon_code, user_id) 
							VALUES('$coupon_code', $user_id)";
						$db->setQuery($query);
						$db->query();
					}
				}*/
				$hash = sha1($downloadLink->secret_word . $downloadLink->random_value);
				if(strstr($returnUrl, "?") === false)
					$returnUrl .= "?";
				else
					$returnUrl .= "&";
				$returnUrl .= "ppdaccess=" . urlencode($resource_id . "-" . $hash . "-" . $downloadLink->random_value);
				if($downloadLink->item_id)
					$returnUrl .= "-" . $downloadLink->item_id;
				return $returnUrl;
			}
		}
		return "";
	}
	
	function getResourceFromDownloadLink($download_id)
	{
		$download_id = (int)$download_id;
		$db = JFactory::getDBO();
		$query = "SELECT resource_id FROM #__payperdownloadplus_download_links WHERE download_id = " . $download_id;
		$db->setQuery( $query );
		return (int)$db->loadResult();
	}
	
	
	function validateResource($resource_id, $download_id, $payed_price, $currency_code)
	{
		$resource = $this->getResource($resource_id);
		$price = $resource->resource_price;
		/*$downloadObject = $this->getDownloadObject($download_id);
		if($downloadObject && $downloadObject->discount && $downloadObject->discount > 0)
		{
			$price = $price * (1.0 - $downloadObject->discount/100.0);
		}*/
		return ($resource && (float)$payed_price - $price >= 0.00 && trim(strtoupper($resource->resource_price_currency)) == trim(strtoupper($currency_code)));
	}
	
	function validatePayment($req, $test, $paymentInfo)
	{
		require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
		PayPerDownloadPlusDebug::debug("validating payment");
		//return "VERIFIED";
		if ($test) 
		{
			PayPerDownloadPlusDebug::debug("validating payment in test mode");
			$root = JURI::root();
			if($paymentInfo->usesimulator)
				return "VERIFIED";
			else
				$paypal = "https://www.sandbox.paypal.com/cgi-bin/webscr'";
		}
		else 
		{
			PayPerDownloadPlusDebug::debug("validating payment in real mode");
			$paypal = "https://www.paypal.com/cgi-bin/webscr'";
		}
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_URL, $paypal);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);

		ob_start();
		curl_exec($ch);
		$res = ob_get_contents();
		curl_close($ch);
		ob_end_clean();
		PayPerDownloadPlusDebug::debug("response from validate : " . $res);
		return $res;
	}
	
	function doEmailReplacements($text, $payment, $payer_email = "", $downloadlink = "", $downloadObject = null, $resource_name = "")
	{
		if($payment)
		{
			$text = preg_replace("/{amount}/", $payment->amount, $text);
			$text = preg_replace("/{fee}/", $payment->fee, $text);
			$text = preg_replace("/{tax}/", $payment->tax, $text);
			$text = preg_replace("/{currency}/", $payment->currency, $text);
			$text = preg_replace("/{payer_email}/", $payer_email, $text);
			$text = preg_replace("/{download_link}/", $downloadlink, $text);
		}
		$text = preg_replace("/{license}/", "", $text);
		if($resource_name)
			$text = preg_replace("/{resource}/", $resource_name, $text);
		else
			$text = preg_replace("/{resource}/", "", $text);
		if($downloadObject)
		{
			$max_downloads = $downloadObject->link_max_downloads;
			$expiration_date = $downloadObject->expiration_date;
		}
		if($max_downloads == 0)
			$text = preg_replace("/{max_downloads}/", JText::_("PAYPERDOWNLOADPLUS_UNLIMITED_DOWNLOADS"), $text);
		else if($max_downloads > 0)
			$text = preg_replace("/{max_downloads}/", $max_downloads, $text);
		else
			$text = preg_replace("/{max_downloads}/", "", $text);
		$text = preg_replace("/{expiration_date}/", $expiration_date, $text);
		$text = preg_replace("/{user}/", "guest user", $text);
		$text = preg_replace("/{user_email}/", "", $text);
		return $text;
	}
	
	function getMaxdownloads($download_id)
	{
		$db = JFactory::getDBO();
		$query = "SELECT link_max_downloads FROM #__payperdownloadplus_download_links WHERE download_id = " . (int)$download_id;
		$db->setQuery( $query );
		$max_downloads = $db->loadResult();
		return $max_downloads;
	}
	
	function getDownloadObject($download_id)
	{
		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__payperdownloadplus_download_links WHERE download_id = " . (int)$download_id;
		$db->setQuery( $query );
		return $db->loadObject();
	}
	
	function notifyPayment($payment_id, $payer_email, $downloadlink, $notify_email, $download_id, $resource_name)
	{
		$emailConfig = $this->getEmailConfig();
		$email = $emailConfig->paymentnotificationemail;
		
		$text = $emailConfig->notificationtext;
		$subject = $emailConfig->notificationsubject;
		$guest_subject = $emailConfig->guestnotificationsubject;
		$guest_text = $emailConfig->guestnotificationtext;
		if(strstr($guest_text, "{download_link}") === false)
			$guest_text .= "{download_link}";
		$payment = $this->getPaymentData($payment_id);
		$downloadObject = $this->getDownloadObject($download_id);
		if(!$downloadObject)
			return;
		$text = $this->doEmailReplacements($text, $payment, $payer_email, "", $downloadObject, $resource_name);
		$subject = $this->doEmailReplacements($subject, $payment, "", "", $downloadObject, $resource_name);
		$guest_subject = $this->doEmailReplacements($guest_subject, $payment, $payer_email, $downloadlink, $downloadObject, $resource_name);
		$guest_text = $this->doEmailReplacements($guest_text, $payment, $payer_email, $downloadlink, $downloadObject, $resource_name);
		
		if($email)
		{
			$mail = JFactory::getMailer();
			$mail->setSubject($subject);
			$mail->setBody($text);
			$emails = explode(';', $email);
			$mail->ClearAddresses();
			foreach($emails as $addr)
			{
				$mail->addRecipient($addr);
			}
			$mail->IsHTML(true);
			$joomla_config = new JConfig();
			$mail->setSender(array($joomla_config->mailfrom, $joomla_config->fromname));
			$mail->Send();
		}
			
		if($payer_email && $guest_text)
		{
			$mail = JFactory::getMailer();
			$mail->ClearAddresses();
			$mail->setSubject($guest_subject);
			$mail->setBody($guest_text);
			$mail->addRecipient($payer_email);
			if($notify_email)
				$mail->addRecipient($notify_email);
			$mail->IsHTML(true);
			$joomla_config = new JConfig();
			$mail->setSender(array($joomla_config->mailfrom, $joomla_config->fromname));
			$mail->Send();
			$this->updateDownloadLink($download_id, $guest_subject, $guest_text, $downloadlink);
		}
	}
	
	function updateDownloadLink($download_id, $subject, $text, $download_link)
	{
		$download_id = (int)$download_id;
		$db = JFactory::getDBO();
		$subject = $db->escape($subject);
		$text = $db->escape($text);
		$download_link = $db->escape($download_link);
		$query = "UPDATE #__payperdownloadplus_download_links 
			SET email_subject = '$subject', email_text = '$text', download_link = '$download_link'
			WHERE download_id = " . $download_id;
		$db->setQuery($query);
		$db->query();
	}
	
	function isTransactionPayed($txn_id)
	{
		$db = JFactory::getDBO();
		$txn_id = $db->escape($txn_id);
		$query = "SELECT payment_id FROM #__payperdownloadplus_payments WHERE txn_id = '$txn_id' AND payed = 1";
		$db->setQuery( $query );
		return $db->loadResult();
	}
	
	function getRandom($seed)
	{
		$config = JFactory::getConfig();
		$secret = "";
		if($config)
			$secret .= $config->get( 'config.secret' ) . $config->get( 'config.password' );
		$rand = $seed;
		for($i = 0; $i < 100; $i++)
		{
			$rand .= microtime() . $secret . mt_rand();
			$rand = sha1($rand);
		}
		return $rand;
	}
	
	function getPaymentData($payment_id)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT 
			#__payperdownloadplus_payments.amount, 
			#__payperdownloadplus_payments.fee, 
			#__payperdownloadplus_payments.tax, 
			#__payperdownloadplus_payments.currency
			FROM #__payperdownloadplus_payments 
			WHERE #__payperdownloadplus_payments.payment_id = ' . (int)$payment_id;
		$db->setQuery($query);
		$result = $db->loadObject();
		return $result;
	}
	
	function getEmailConfig()
	{
		$config = $this->getConfig();
		$emailConfig = new StdClass();
		$emailConfig->notificationsubject = $config->get('notificationsubject');
		$emailConfig->paymentnotificationemail = $config->get('paymentnotificationemail');
		$emailConfig->notificationtext = $config->get('notificationtext');
		$emailConfig->usernotificationsubject = $config->get('usernotificationsubject');
		$emailConfig->usernotificationtext = $config->get('usernotificationtext');
		$emailConfig->guestnotificationsubject = $config->get('guestnotificationsubject');
		$emailConfig->guestnotificationtext = $config->get('guestnotificationtext');
		return $emailConfig;
	}
	
	function trace($text)
	{
		$db = JFactory::getDBO();
		$text = $db->escape($text);
		$query = "INSERT INTO #__payperdownloadplus_debug(debug_text, debug_time)
		   VALUES('$text', NOW())";
		$db->setQuery($query);
		$db->query();
	}
}
?>