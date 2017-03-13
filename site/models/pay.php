<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );

require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");

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

class PayPerDownloadModelPay extends JModelLegacy
{ 
	var $config = null;

	function getConfig()
	{
		if($this->config)
			return $this->config;
		return new ConfigObject();
	}
	
	function sortLicenses(&$licenses)
	{
		$license_sort = $this->getLicenseOrdering();
		$this->field1 = "level";
		$this->field2 = "license_name";
		switch($license_sort)
		{
			case 1:
				$this->field1 = "level";
				$this->field2 = "license_name";
				break;
			case 2:
				$this->field1 = "level";
				$this->field2 = "price";
				break;
			case 3:
				$this->field1 = "level";
				$this->field2 = "expiration";
				break;
			case 4:
				$this->field1 = "license_name";
				$this->field2 = "level";
				break;
			case 5:
				$this->field1 = "price";
				$this->field2 = "level";
				break;
			case 6:
				$this->field1 = "expiration";
				$this->field2 = "level";
				break;
		}
		usort($licenses, array($this, "compare_licenses"));
	}
	
	function compare_licenses($l1, $l2)
	{
		if($l1->{$this->field1} > $l2->{$this->field1})
			return 1;
		else if($l1->{$this->field1} < $l2->{$this->field1})
			return -1;
		else
		{
			if($l1->{$this->field2} > $l2->{$this->field2})
				return 1;
			else if($l1->{$this->field2} < $l2->{$this->field2})
				return -1;
			else
				return 0;
		}
	}
	
	function getTaxPercent()
	{
		$config = $this->getConfig();
		return $config->get('tax_rate', 0);
	}
	
	function getLicenseOrdering()
	{
		$config = $this->getConfig();
		return $config->get('license_sort', 1);
	}
	
	function getRedirectToLogin()
	{
		$config = $this->getConfig();
		return $config->get('redirect_to_login', 0);
	}
	
	function getShowLogin()
	{
		$config = $this->getConfig();
		return $config->get('show_login', 1);
	}
	
	function getUseQuickRegister()
	{
		$config = $this->getConfig();
		return $config->get('show_quick_register', 1);
	}
	
	function getUseOsolCaptcha()
	{
		$config = $this->getConfig();
		return $config->get('use_osol_captcha', 1);
	}
	
	function licensesRequireRegistration($licenses)
	{
		return true;
	}
	
	function getPaymentHeader()
	{
		$config = $this->getConfig();
		return $config->get('payment_header', "");
	}
	
	function getResourcePaymentHeader()
	{
		$config = $this->getConfig();
		return $config->get('resource_payment_header', "");
	}
	
	function getAlternatePayLicenseHeader()
	{
		$config = $this->getConfig();
		$header = $config->get('alternate_pay_license_header', "");
		if($this->cleanHtml($header) == "")
			$header = JText::_("PAYPERDOWNLOADPLUS_ALSO_AVAILABLE_BUYING_LICENSES");
		return $header;
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
	
	function getUseDiscountCoupon()
	{
		$config = $this->getConfig();
		return $config->get('use_discount_coupon', 0);
	}
	
	function getAskEmail()
	{
		$config = $this->getConfig();
		return $config->get('askemail', 0);
	}
	
	function getUseDiscount()
	{
		$config = $this->getConfig();
		return $config->get('apply_discount', 1) || $config->get('apply_discount_renew', 1);
	}
	
	function getAlphaIntegration()
	{
		$config = $this->getConfig();
		return $config->get('alphapoints', 0);
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
	
	function getShowResources()
	{
		$config = $this->getConfig();
		return $config->get('showresources', 1);
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
	
	function getMultipleLicensesView()
	{
		$config = $this->getConfig();
		return $config->get('multilicenseview', 0);
	}
	
	function getLicense($licenseId)
	{
		$db = JFactory::getDBO();
		$query = "SELECT license_id, license_name, price, currency_code, description,
			notify_url, expiration, level, max_download, aup, user_group
			FROM #__payperdownloadplus_licenses
			WHERE enabled <> 0 AND license_id =" . (int)$licenseId;
		$db->setQuery( $query );
		$result = $db->loadObject();
		if(!$result)
		{
			require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
			PayPerDownloadPlusDebug::debug("Error loading license");
		}
		else
			$result->canRenew = $this->canLicenseBeRenewed($licenseId);
		return $result;
	}
	
	function getRenovationOptions($licenseId)
	{
		$db = JFactory::getDBO();
		$query = "SELECT renew FROM #__payperdownloadplus_licenses WHERE license_id = " .
			(int)$licenseId;
		$db->setQuery( $query, 0, 1);
		$this->renew = $db->loadResult();
		return $this->renew;
	}
	
	function canLicenseBeRenewed($licenseId)
	{
		$renew = $this->getRenovationOptions($licenseId);
		if($renew == 0) // Can be renewed always
			return true;
		$user = JFactory::getUser();
		if($user->id)
		{
			$user_id = (int)$user->id;
			$licenseId = (int)$licenseId;
			if($renew == 1)
				$query = "SELECT COUNT(*)
					FROM #__payperdownloadplus_users_licenses
					WHERE user_id = $user_id AND license_id = $licenseId AND
					 (expiration_date IS NULL OR expiration_date > NOW()) AND
					(license_max_downloads = 0 OR download_hits < license_max_downloads)";
			else
				$query = "SELECT COUNT(*)
					FROM #__payperdownloadplus_users_licenses
					WHERE user_id = $user_id AND license_id = $licenseId";
			$db = JFactory::getDBO();
			$db->setQuery( $query );
			$active = $db->loadResult();
			return $active <= 0;
		}
		else
			return true;
	}
	
	function getHigherLicenses($min_level)
	{
		if($min_level <= 0)
			return array();
		$db = JFactory::getDBO();
		$query = "SELECT license_id
			FROM #__payperdownloadplus_licenses
			WHERE level > " . (int)$min_level;
		$db->setQuery( $query );
		return $db->loadColumn();
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
	
	function handleResponse()
	{
		require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
		PayPerDownloadPlusDebug::debug("Called from Paypal or other payment gateway, buying license");
		$this->startTransaction();
		$db = JFactory::getDBO();
		$usePayPlugin = $this->getUsePayPluginConfig();
		$payed = 0;
		$dealt = false;
		if($usePayPlugin)
		{
			$gateway = JRequest::getVar('gateway');
			PayPerDownloadPlusDebug::debug("Using payment plugin, gateway: " . $gateway);
			JPluginHelper::importPlugin("payperdownloadplus");
			$dispatcher	= JDispatcher::getInstance();
			$dispatcher->trigger('onPaymentReceived', array($gateway, &$dealt, &$payed, &$user_id, &$license_id, &$resource_id, &$transactionId,
				&$response, &$validate_response, &$status, &$amount, &$tax, &$fee, &$currency));
			if($dealt)
			{
				if(!$transactionId)
				{
					$this->commitTransaction();
					PayPerDownloadPlusDebug::debug("Invalid transaction id");
					return;
				}
				$payer_email = "";
				$dispatcher->trigger('onGetPayerEmail', array($transactionId, &$payer_email));
				$payed = $payed ? 1 : 0;
				$user_id = (int)$user_id;
				if(!$payer_email && $user_id)
				{
					$user = $this->getUserData($user_id);
					if($user)
						$payer_email = $user->email;
				}
				$notify_email = $payer_email;
				$license_id = (int)$license_id;
				$txn_id = $db->escape($transactionId);
				$response = $db->escape($response);
				$validate_response = $db->escape($validate_response);
				$status = $db->escape($status);
				$amount = $db->escape((float)$amount);
				$fee = $db->escape((float)$fee);
				$tax = $db->escape((float)$tax);
				$mc_currency = $db->escape($currency);
				$esc_user_email = $db->escape($payer_email);
				$query = "INSERT INTO 
					#__payperdownloadplus_payments(user_id, user_email, license_id, payed, payment_date, txn_id, response, validate_response, 
						status, amount, tax, fee, currency)
					VALUES($user_id, '$esc_user_email', $license_id, $payed, NOW(), '$txn_id', '$response', '$validate_response', 
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
			$txn_id = JRequest::getVar('txn_id');
			if(!$this->isTransactionPayed($txn_id))
			{
				$validate_response = $this->validatePayment($req, JRequest::getInt('test_ipn', 0), $paymentInfo);
				$payed = (strcmp ($validate_response, "VERIFIED") == 0) ? 1 : 0;
			}
			else
			{
				$this->commitTransaction();
				PayPerDownloadPlusDebug::debug("Invalid payment ". $txn_id);
				return;
			}
				
			if(!$payed)
			{
				$this->commitTransaction();
				PayPerDownloadPlusDebug::debug("Payment was not successfull for some reason");
				return;
			}
			
			$txn_id = $db->escape($txn_id);
			$user_id = JRequest::getInt('custom', 0);
			$text = $db->escape($text);
			$validate_response = $db->escape($validate_response);
			$license_id = JRequest::getInt('item_number');
			$status = '';
			$payed_price = 0;
			if($payed)
			{
				$payed_price = JRequest::getFloat('mc_gross');
				$currency_code = JRequest::getVar('mc_currency');
				$payed = $this->validateLicense($license_id, $payed_price, $currency_code, $user_id) ? 1 : 0;
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
			$esc_user_email = $db->escape($payer_email);
			$esc_receiver_email = $db->escape($receiver_email);
			$query = "INSERT INTO 
				#__payperdownloadplus_payments(user_id, user_email, license_id, payed, payment_date, txn_id, response, validate_response, status, amount, tax, fee, currency, receiver_email)
				VALUES($user_id, '$esc_user_email', $license_id, $payed, NOW(), '$txn_id', '$text', '$validate_response', '$status', '$amount', '$tax', '$fee', '$mc_currency', '$esc_receiver_email')";
			$db->setQuery( $query );
			$query_result = $db->query();
			$payment_id = $db->insertid();
			if($query_result)
				PayPerDownloadPlusDebug::debug("Payment saved");
			else
				PayPerDownloadPlusDebug::debug("Payment not saved");
		}
		if($payed)
		{
			if($user_id)
			{
				$user_license_id = $this->assignLicense($user_id, $license_id, $amount, false);
				$this->assignAffiliateCredit($user_id, $license_id, $amount);
				$this->assignAUPToUser($user_id, $license_id);
			}
			else
				PayPerDownloadPlusDebug::debug("Invalid user id received");
			if($user_id)
			{
				$this->notifyPayment($user_id, $payment_id, $user_license_id);
				PayPerDownloadPlusDebug::debug("Notified");
			}
		}
		$this->commitTransaction();
		$redirect = JRequest::getVar('redirect', '');
		if($redirect)
		{
			$redirect = base64_decode($redirect);
			$mainframe = JFactory::getApplication();
			$mainframe->enqueueMessage(JText::_("PAYPERDOWNLOADPLUS_THANK_YOU") . " - Status: " . 
				$validate_response);
			$mainframe->redirect($redirect);
		}
		else
			exit;
	}
	
	function getAUP()
	{
		$user = JFactory::getUser();
		if($user->id)
		{
			$api_AUP = JPATH_SITE.'/components/com_alphauserpoints/helper.php';
			if ( file_exists($api_AUP) )
			{
				require_once ($api_AUP);
				$profile = AlphaUserPointsHelper::getUserInfo('', $user->id);
				if($profile)
					return $profile->points;
			}
		}
		return 0;
	}
	
	function assignAUPToUser($user_id, $license_id)
	{
		$config = $this->getConfig();
		if($config->get('alphapoints', 0) == 1)
		{
			$license = $this->getLicense($license_id);
			if(!$license || $license->aup <= 0)
				return;
			$api_AUP = JPATH_SITE.'/components/com_alphauserpoints/helper.php';
			if ( file_exists($api_AUP))
			{
				require_once ($api_AUP);
				$aupid = AlphaUserPointsHelper::getAnyUserReferreID($user_id);
				AlphaUserPointsHelper::newpoints( 'plgaup_payperdownloadplus_buy', $aupid, '', $license->license_name, $license->aup, false, 1);
			}
		}
	}
	
	function removeAUPFromUser($user_id, $license)
	{
		$api_AUP = JPATH_SITE.'/components/com_alphauserpoints/helper.php';
		if ( file_exists($api_AUP))
		{
			require_once ($api_AUP);
			$aupid = AlphaUserPointsHelper::getAnyUserReferreID($user_id);
			AlphaUserPointsHelper::newpoints( 'plgaup_payperdownloadplus_buy', $aupid, '', $license->license_name, -$license->aup, false, 1);
			return true;
		}
		else
			return false;
	}
	
	function assignAffiliateCredit($user_id, $license_id, $amount)
	{
		$user_id = (int)$user_id;
		$license_id = (int)$license_id;
		$db = JFactory::getDBO();
		$query = "SELECT referer_user FROM #__payperdownloadplus_affiliates_users_refered WHERE refered_user = " . (int)$user_id;
		$db->setQuery( $query );
		$referer_user = (int)$db->loadResult();
		if($referer_user)
		{
			$query = "SELECT #__payperdownloadplus_affiliates_programs.percent,
				#__payperdownloadplus_affiliates_programs.affiliate_program_id
				FROM #__payperdownloadplus_affiliates_users
				INNER JOIN #__payperdownloadplus_affiliates_programs
				ON #__payperdownloadplus_affiliates_users.affiliate_program_id = #__payperdownloadplus_affiliates_programs.affiliate_program_id 
				WHERE #__payperdownloadplus_affiliates_programs.license_id = $license_id AND 
					#__payperdownloadplus_affiliates_users.user_id = $referer_user";
			$db->setQuery( $query );
			$data = $db->loadObject();
			$percent = (float)$data->percent;
			$credit = ($percent / 100.00) * $amount;
			$affiliate_program_id = (int)$data->affiliate_program_id;
			$query = "UPDATE #__payperdownloadplus_affiliates_users
				SET credit = credit + " . (float)$credit . 
				" WHERE user_id = $referer_user AND affiliate_program_id = $affiliate_program_id";
			$db->setQuery( $query );
			$db->query();
		}
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
			$rand .= microtime() . $secret;
			$rand = sha1($rand);
		}
		return $rand;
	}
	
	function validateLicense($license_id, $payed_price, $currency_code, $user_id)
	{
		$license = $this->getLicense($license_id);
		$price = $this->getDiscountLicense($license, $user_id);
		$license_discount = $this->getLicenseDiscount( $user_id, $license_id );
		if($license_discount)
		{
			$price = $price * (1 - $license_discount->discount / 100.0);
		}
		$price = round($price, 2);
		$result = ($license && (float)$payed_price - $price >= 0.00 && trim(strtoupper($license->currency_code)) == trim(strtoupper($currency_code)));
		return $result;
	}
	
	function deleteLicenseDiscount($user_id, $license_id)
	{
		$db = JFactory::getDBO();
		$user_id = (int)$user_id;
		$license_id = (int)$license_id;
		$query = "DELETE FROM #__payperdownloadplus_users_licenses_discount
			WHERE license_id = $license_id AND user_id = $user_id";
		$db->setQuery( $query );
		$db->query();
	}
	
	function getLicenseDiscount($user_id, $license_id)
	{
		$db = JFactory::getDBO();
		$user_id = (int)$user_id;
		$license_id = (int)$license_id;
		$query = "SELECT discount, coupon_code FROM #__payperdownloadplus_users_licenses_discount
			WHERE license_id = $license_id AND user_id = $user_id";
		$db->setQuery( $query );
		return $db->loadObject();
	}
	
	function getUserData($user_id)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT name, email FROM #__users WHERE id = ' . (int)$user_id);
		return $db->loadObject();
	}
	
	function getPaymentData($payment_id)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT 
			#__payperdownloadplus_payments.amount, 
			#__payperdownloadplus_payments.fee, 
			#__payperdownloadplus_payments.tax, 
			#__payperdownloadplus_payments.txn_id,
			#__payperdownloadplus_payments.currency, 
			#__payperdownloadplus_licenses.license_id, 
			#__payperdownloadplus_licenses.license_name,
			#__payperdownloadplus_licenses.member_title, 
			#__payperdownloadplus_licenses.level,
			#__payperdownloadplus_licenses.max_download,
			#__payperdownloadplus_licenses.expiration,
			#__payperdownloadplus_licenses.price,
			#__payperdownloadplus_licenses.currency_code as license_currency_code
			FROM #__payperdownloadplus_payments 
			LEFT JOIN #__payperdownloadplus_licenses
			ON #__payperdownloadplus_payments.license_id = #__payperdownloadplus_licenses.license_id
			WHERE #__payperdownloadplus_payments.payment_id = ' . (int)$payment_id;
		$db->setQuery($query);
		$result = $db->loadObject();
		PayPerDownloadPlusDebug::debug("Get payment data with query : $query");
		if(!$result)
			PayPerDownloadPlusDebug::debug("Failed query: $query");
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
	
	function doEmailReplacements($text, $payment, $user, $expirationDate, $payer_email = "", $downloadlink = "")
	{
		if($payment)
		{
			$text = preg_replace("/{amount}/", $payment->amount, $text);
			$text = preg_replace("/{fee}/", $payment->fee, $text);
			$text = preg_replace("/{tax}/", $payment->tax, $text);
			$text = preg_replace("/{transaction}/", $payment->txn_id, $text);
			$text = preg_replace("/{currency}/", $payment->currency, $text);
			$text = preg_replace("/{license}/", $payment->license_name, $text);
			$text = preg_replace("/{member_title}/", $payment->member_title, $text);
			$text = preg_replace("/{level}/", $payment->level, $text);
			$text = preg_replace("/{expiration}/", $payment->expiration, $text);
			if(preg_match("/{expiration_date}/", $text))
			{
				if($payment->expiration > 0)
				{
					$text = preg_replace("/{expiration_date}/", $expirationDate, $text);
				}
				else
				{
					$text = preg_replace("/{expiration_date}/", JText::_("PAYPERDOWNLOADPLUS_EXPIRES_NEVER"), $text);
				}
			}
			$text = preg_replace("/{price}/", $payment->price, $text);
			$text = preg_replace("/{license_currency}/", $payment->license_currency_code, $text);
			$text = preg_replace("/{payer_email}/", "", $text);
			$text = preg_replace("/{download_link}/", "", $text);
			$text = preg_replace("/{resource}/", "", $text);
			if($payment->max_download)
				$text = preg_replace("/{max_downloads}/", $payment->max_download, $text);
			else
				$text = preg_replace("/{max_downloads}/", JText::_("PAYPERDOWNLOADPLUS_UNLIMITED_DOWNLOADS"), $text);
		}
		if($user)
		{
			$text = preg_replace("/{user}/", $user->name, $text);
			$text = preg_replace("/{user_email}/", $user->email, $text);
		}
		return $text;
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
	
	function getUserLicenseExpiration($user_license_id)
	{
		$db = JFactory::getDBO();
		$query = "SELECT expiration_date FROM #__payperdownloadplus_users_licenses WHERE user_license_id = " . 
			(int)$user_license_id;
		$db->setQuery( $query );
		return $db->loadResult();
	}
	
	function notifyPayment($user_id, $payment_id, $user_license_id)
	{
		$emailConfig = $this->getEmailConfig();
		$email = $emailConfig->paymentnotificationemail;
		$text = $emailConfig->notificationtext;
		$subject = $emailConfig->notificationsubject;
		$client_subject = $emailConfig->usernotificationsubject;
		$client_text = $emailConfig->usernotificationtext;
		$payment = $this->getPaymentData($payment_id);
		$user = $this->getUserData($user_id);
		$expirationDate = $this->getUserLicenseExpiration($user_license_id);
		$text = $this->doEmailReplacements($text, $payment, $user, $expirationDate);
		$subject = $this->doEmailReplacements($subject, $payment, $user, $expirationDate);
		$client_subject = $this->doEmailReplacements($client_subject, $payment, $user, $expirationDate);
		$client_text = $this->doEmailReplacements($client_text, $payment, $user, $expirationDate);
		if($email)
		{
			$mail = JFactory::getMailer();
			$mail->setSubject($subject);
			$mail->setBody($text);
			$emails = explode(';', $email);
			foreach($emails as $addr)
			{
				$mail->addRecipient($addr);
			}
			$mail->IsHTML(true);
			$joomla_config = new JConfig();
			$mail->setSender(array($joomla_config->mailfrom, $joomla_config->fromname));
			$mail->Send();
		}
		if($user_id && $user && $user->email && $client_subject)
		{
			$mail = JFactory::getMailer();
			$mail->ClearAddresses();
			$mail->setSubject($client_subject);
			$mail->setBody($client_text);
			$mail->addRecipient($user->email);
			$mail->IsHTML(true);
			$joomla_config = new JConfig();
			$mail->setSender(array($joomla_config->mailfrom, $joomla_config->fromname));
			$mail->Send();
		}
	}
	
	function assignLicense($user_id, $license_id, $credit = 0, $set_credit_days = false)
	{
		require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
		PayPerDownloadPlusDebug::debug("Assigning license");
		$credit = (int)$credit;
		$user_id = (int)$user_id;
		$license_id = (int)$license_id;
		$db = JFactory::getDBO();
		$license = $this->getLicense($license_id);
		if(!$license)
		{
			PayPerDownloadPlusDebug::debug("Error loading license");
			return;
		}
		$days = (int)$license->expiration;
		$max_downloads = (int)$license->max_download;
		$str_credit_days = "";
		$credit_days = 0;
		$duration = $days;
		if($set_credit_days)
		{
			$str_credit_days = ", credit_days_used = duration ";
			$credit_days = $duration;
		}
		$userLicense = $this->getUserLicense($user_id, $license_id);
		$assigned_user_group = "NULL";
		if($license->user_group)
			$assigned_user_group = (int)$license->user_group;
		$user_license_id = 0;
		if(!$userLicense)
		{
			if($days > 0)
				$query = "INSERT INTO #__payperdownloadplus_users_licenses(license_id, user_id, expiration_date, credit, duration, credit_days_used, license_max_downloads, assigned_user_group)
					VALUES($license_id, $user_id, DATE_ADD(NOW(), INTERVAL $days DAY), $credit, $duration, $credit_days, $max_downloads, $assigned_user_group)";
			else
				$query = "INSERT INTO #__payperdownloadplus_users_licenses(license_id, user_id, expiration_date, credit, duration, credit_days_used, license_max_downloads, assigned_user_group)
					VALUES($license_id, $user_id, NULL, 0, 0, 0, $max_downloads, $assigned_user_group)";
			$db->setQuery( $query );
			$query_result = $db->query();
			$user_license_id = $db->insertid();
			PayPerDownloadPlusDebug::debug("Create license with query : $query");
			if(!$query_result)
				PayPerDownloadPlusDebug::debug("Failed query : $query");
		}
		else
		{
			if($days > 0)
			{
				$query = "UPDATE #__payperdownloadplus_users_licenses
						SET expiration_date = DATE_ADD(expiration_date, INTERVAL $days DAY),
						credit = credit + $credit,
						license_max_downloads = license_max_downloads + $max_downloads,
						duration = duration + $duration,
						assigned_user_group = $assigned_user_group
						$str_credit_days
						WHERE user_license_id = " . (int)$userLicense->user_license_id;
			}
			else
			{
				$query = "UPDATE #__payperdownloadplus_users_licenses
						SET expiration_date = DATE_ADD(expiration_date, INTERVAL $days DAY),
						credit = credit + $credit,
						expiration_date = NULL,
						license_max_downloads = license_max_downloads + $max_downloads,
						duration = 0, credit_days_used = 0,
						assigned_user_group = $assigned_user_group
						WHERE user_license_id = " . (int)$userLicense->user_license_id;
			}
			$db->setQuery( $query );
			$query_result = $db->query();
			PayPerDownloadPlusDebug::debug("Update license with query : $query");
			if(!$query_result)
				PayPerDownloadPlusDebug::debug("Failed query : $query");
			$user_license_id = $userLicense->user_license_id;
		}
		$this->removeUsedCredit($license, $user_id);
		$this->assignUserGroup($license, $user_id);
		$license_discount = $this->getLicenseDiscount( $user_id, $license_id );
		if($license_discount)
		{
			PayPerDownloadPlusDebug::debug("searching coupon user");
			$coupon_code = $db->escape($license_discount->coupon_code);
			$query = "SELECT user_id FROM #__payperdownloadplus_coupons_users 
				WHERE coupon_code = '$coupon_code' AND user_id = $user_id";
			$db->setQuery($query);
			$result = $db->loadResult();
			if(!$result)
			{
				PayPerDownloadPlusDebug::debug("inserting coupon user");
				$query = "INSERT INTO #__payperdownloadplus_coupons_users(coupon_code, user_id) 
					VALUES('$coupon_code', $user_id)";
				$db->setQuery($query);
				$db->query();
			}
			$this->deleteLicenseDiscount( $user_id, $license_id );
		}
		return $user_license_id;
	}
	
	function assignUserGroup($license, $user_id)
	{
		if($license->user_group)
		{
			$version = new JVersion();
			if($version->RELEASE >= "1.6")
			{
				$user = JFactory::getUser($user_id);
				if(array_search($license->user_group,  $user->groups) === false)
				{
					$user->groups []= $license->user_group;
					$user->save();
				}
			}
			else
			{
			}
		}
	}
	
	function getUserLicense($user_id, $license_id)
	{
		$license_id = (int)$license_id;
		$db = JFactory::getDBO();
		$query = "SELECT #__payperdownloadplus_licenses.license_id, 
			#__payperdownloadplus_users_licenses.user_license_id,
			#__payperdownloadplus_users_licenses.expiration_date,
			#__payperdownloadplus_licenses.expiration, 
			#__payperdownloadplus_licenses.level, 
			#__payperdownloadplus_users_licenses.user_id
			FROM #__payperdownloadplus_users_licenses 
			INNER JOIN #__payperdownloadplus_licenses
			ON #__payperdownloadplus_users_licenses.license_id = #__payperdownloadplus_licenses.license_id
			WHERE 
			(#__payperdownloadplus_users_licenses.expiration_date > NOW() ||
			#__payperdownloadplus_users_licenses.expiration_date IS NULL
			)AND 
			#__payperdownloadplus_users_licenses.license_id = $license_id AND 
			#__payperdownloadplus_users_licenses.enabled <> 0 AND
			#__payperdownloadplus_users_licenses.user_id = " .(int)$user_id;
		$db->setQuery( $query );
		$result = $db->loadObject();
		PayPerDownloadPlusDebug::debug("Get user licenses with query : $query");
		if(!$result)
			PayPerDownloadPlusDebug::debug("Failed query : $query");
		return $result;
	}
	
	function isTransactionPayed($txn_id)
	{
		$db = JFactory::getDBO();
		$txn_id = $db->escape($txn_id);
		$query = "SELECT payment_id FROM #__payperdownloadplus_payments WHERE txn_id = '$txn_id' AND payed = 1";
		$db->setQuery( $query );
		return $db->loadResult();
	}
	
	function getLicenseResources($lid)
	{
		$db = JFactory::getDBO();
		$lid = (int)$lid;
		$license = $this->getLicense($lid);
		if($license)
		{
			$level = (int)$license->level;
			$query = "SELECT DISTINCT
				#__payperdownloadplus_resource_licenses.resource_license_id, 
				#__payperdownloadplus_resource_licenses.resource_name,
				#__payperdownloadplus_resource_licenses.resource_description,
				#__payperdownloadplus_resource_licenses.alternate_resource_description
				FROM #__payperdownloadplus_resource_licenses
				INNER JOIN #__payperdownloadplus_licenses
				ON #__payperdownloadplus_resource_licenses.license_id =
					#__payperdownloadplus_licenses.license_id
				WHERE #__payperdownloadplus_licenses.license_id = $lid 
					OR 
					(
					#__payperdownloadplus_licenses.`level` > 0 AND
					#__payperdownloadplus_licenses.`level` < $level
					);";
			$db->setQuery( $query );
			$resources = $db->loadObjectList();
			return $resources;
		}
		return null;
	}
	
	function validatePayment($req, $test, $paymentInfo)
	{
		//return "VERIFIED";
		if ($test) 
		{
			$root = JURI::root();
			if($paymentInfo->usesimulator)
				return "VERIFIED";
			else
				$paypal = "https://www.sandbox.paypal.com/cgi-bin/webscr'";
		}
		else 
		{
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
		
		return $res;
	}
	
	function getDiscountLicense($license, $user_id)
	{
		$level = (int)$license->level;
		$db = JFactory::getDBO();
		$cur = $db->escape($license->currency_code);
		
		$config = $this->getConfig();
		$price = $license->price;
		if($config->get('apply_discount', 1) == 1)
		{
			$query = "SELECT 
				TO_DAYS(#__payperdownloadplus_users_licenses.expiration_date) - 
					TO_DAYS(NOW()) - credit_days_used AS remaining,
				#__payperdownloadplus_users_licenses.duration - credit_days_used AS total,
				#__payperdownloadplus_users_licenses.credit
				FROM #__payperdownloadplus_users_licenses
				INNER JOIN #__payperdownloadplus_licenses
				ON #__payperdownloadplus_users_licenses.license_id = #__payperdownloadplus_licenses.license_id
				WHERE #__payperdownloadplus_users_licenses.expiration_date > NOW() AND 
					#__payperdownloadplus_users_licenses.enabled <> 0 AND
					#__payperdownloadplus_licenses.enabled <> 0 AND
					#__payperdownloadplus_licenses.level > 0 AND
					#__payperdownloadplus_licenses.level < $level AND
					#__payperdownloadplus_licenses.currency_code = '$cur' AND
					#__payperdownloadplus_users_licenses.user_id = " . (int)$user_id;
			$db->setQuery($query);
			$valids = $db->loadObjectList();
			$sum = 0;
			foreach($valids as $valid)
			{
				if($valid->total > 0 && $valid->remaining)
				{
					$sum += $valid->credit * ((float)$valid->remaining / (float)$valid->total);
				}
			}
			$sum = round($sum, 2);
			if($license->price > $sum)
				$price = $license->price - $sum;
			else
				$price = 0; // free license
		}
		$percent = $config->get('renew_discount_percent', 10);
		if($percent > 0 && $percent < 100)
		{
			$license_id = (int)$license->license_id;
			$query = "SELECT 
				COUNT(*)
				FROM #__payperdownloadplus_users_licenses
				INNER JOIN #__payperdownloadplus_licenses
				ON #__payperdownloadplus_users_licenses.license_id = #__payperdownloadplus_licenses.license_id
				WHERE #__payperdownloadplus_users_licenses.expiration_date > NOW() AND 
					#__payperdownloadplus_users_licenses.enabled <> 0 AND
					#__payperdownloadplus_licenses.enabled <> 0 AND
					#__payperdownloadplus_licenses.license_id = $license_id AND
					#__payperdownloadplus_users_licenses.user_id = " . (int)$user_id;
			$db->setQuery($query);
			$count = $db->loadResult();
			if($count > 0)
			{
				$price -= $price * $percent / 100.0;
				$price = round($price, 2);
			}
		}
		return $price;
	}
	
	function removeUsedCredit($license, $user_id)
	{
		$level = (int)$license->level;
		$db = JFactory::getDBO();
		$cur = $db->escape($license->currency_code);
		
		$query = "SELECT 
			#__payperdownloadplus_users_licenses.user_license_id
			FROM #__payperdownloadplus_users_licenses
			INNER JOIN #__payperdownloadplus_licenses
			ON #__payperdownloadplus_users_licenses.license_id = #__payperdownloadplus_licenses.license_id
			WHERE #__payperdownloadplus_users_licenses.expiration_date > NOW() AND 
				#__payperdownloadplus_users_licenses.enabled <> 0 AND
				#__payperdownloadplus_licenses.enabled <> 0 AND
				#__payperdownloadplus_licenses.level > 0 AND
				#__payperdownloadplus_licenses.level < $level AND
				#__payperdownloadplus_licenses.currency_code = '$cur' AND
				#__payperdownloadplus_users_licenses.user_id = " . (int)$user_id;
		$db->setQuery($query);
		$user_licenses = $db->loadColumn();
		if(count($user_licenses) > 0)
		{
			$licenses = implode(",", $user_licenses);
			$query = "UPDATE #__payperdownloadplus_users_licenses SET credit = 0, credit_days_used = duration 
				WHERE #__payperdownloadplus_users_licenses.user_license_id IN ($licenses)";
			$db->setQuery($query);
			$db->query();
		}
	}
	
	function getFree($license_id, $user_id)
	{
		$license = $this->getLicense($license_id);
		if($license)
		{
			if($this->canLicenseBeRenewed($license_id))
			{
				$discount_price = $this->getDiscountLicense($license, $user_id);
				if($discount_price <= 0.001)
				{
					$this->assignLicense($user_id, $license_id, 0, true);
					return true;
				}
			}
		}
		return false;
	}
	
	function createDownloadLink($resource_id, $itemId)
	{
		$resource_id = (int)$resource_id;
		$user_id = (int)JFactory::getUser()->id;
		$db = JFactory::getDBO();
		//Delete expired and not paid download links 
		$db->setQuery("DELETE FROM #__payperdownloadplus_download_links WHERE expiration_date < NOW() || (payed = 0 AND TO_DAYS(NOW()) - TO_DAYS(creation_date) > 1)");
		$db->query();
		
		$secret_word = $this->getRandom($resource_id . $resource_id);
		$random_value = $this->getRandom($resource_id);
		
		$e_secret_word = $db->escape($secret_word);
		$e_random_value = $db->escape($random_value);
		if($itemId)
			$dbItemId = "'" . $db->escape($itemId) . "'";
		else
			$dbItemId = "NULL";
		
		$query = "INSERT INTO #__payperdownloadplus_download_links
			(resource_id, item_id, payed, creation_date, secret_word, random_value, user_id)
			VALUES($resource_id, $dbItemId, 0, NOW(), '$e_secret_word', '$e_random_value', $user_id)";
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
			if($itemId)
				$downloadLink->accessCode .= "-" . $itemId;
		}
		return $downloadLink;
	}
	
	function cleanHtml($text)
	{
		$text = preg_replace("/<\/?[a-zA-Z0-9]+[^>]*>/", "", $text);
		$text = preg_replace("/&[a-zA-Z]{1,6};/", "", $text);
		$text = preg_replace('/\s/', "", $text);
		return trim($text);
	}
	
	function getResource($resource_id)
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT * FROM #__payperdownloadplus_resource_licenses WHERE resource_license_id = " . (int)$resource_id);
		$resource = $db->loadObject();
		if($this->cleanHtml($resource->payment_header) == "")
			$resource->payment_header = $this->getResourcePaymentHeader();
		if($this->cleanHtml($resource->payment_header) == "")
			$resource->payment_header = JText::_('PAYPERDOWNLOAD_PLUS_PAYRESOURCE_HEADER');
		return $resource;
	}
	
	function applyDiscountCoupon($coupon_code, $price, $payItemId, $itemIsLicense)
	{
		$db = JFactory::getDBO();
		$coupon_code = $db->escape(strtoupper($coupon_code));
		$query = "SELECT * FROM #__payperdownloadplus_coupons 
			WHERE expire_time >= NOW() AND code = '$coupon_code'";
		$db->setQuery( $query );
		$coupon = $db->loadObject();
		if($coupon)
		{
			$user_id = (int)JFactory::getUser()->id;
			$coupon_code = $db->escape($coupon->code);
			$query = "SELECT user_id FROM #__payperdownloadplus_coupons_users
				WHERE coupon_code = '$coupon_code' AND user_id = $user_id";
			$db->setQuery( $query );
			$result = $db->loadResult();
			if($result) //If user already used this code then ignore
				return null;
			$newPrice = $price * (1.0 - $coupon->discount/100.0);
			if($itemIsLicense)
			{
				$query = "SELECT user_id FROM #__payperdownloadplus_users_licenses_discount 
					WHERE license_id = " . (int)$payItemId . " AND user_id = " . 
					(int)$user_id;
				$db->setQuery( $query );
				$result = $db->loadResult();
				if($result)
				{
					$query = "UPDATE #__payperdownloadplus_users_licenses_discount
						SET discount = " . (float)$coupon->discount . 
						", coupon_code = '" . $db->escape($coupon->code) . "' " .
						" WHERE license_id = " . (int)$payItemId . " AND user_id = " .
						$user_id;
					$db->setQuery( $query );
					$db->query();
				}
				else
				{
					$query = "INSERT INTO #__payperdownloadplus_users_licenses_discount
						(license_id, user_id, discount, coupon_code)
						VALUES(" . (int)$payItemId . "," . $user_id . "," . 
						(float)$coupon->discount . ", '" . $db->escape($coupon->code) . "')";
					$db->setQuery( $query );
					$db->query();
				}
			}
			else
			{
				$query = "UPDATE #__payperdownloadplus_download_links SET discount = " .
					(float)$coupon->discount . 
					", coupon_code = '" . $db->escape($coupon->code) ."' " .
					" WHERE download_id = " . (int)$payItemId;
				$db->setQuery( $query );
				$db->query();
			}
			return $newPrice;
		}
		else
		{
			return null;
		}
	}
	
	function trace($text)
	{
		require_once (JPATH_ADMINISTRATOR . "components/com_payperdownload/classes/debug.php");
		PayPerDownloadPlusDebug::debug($text);
	}
}
?>