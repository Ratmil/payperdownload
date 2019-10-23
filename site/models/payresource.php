<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;

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
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get resource with id " . $resource_id);

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select('*');
		$query->from($db->quoteName('#__payperdownloadplus_resource_licenses'));
		$query->where($db->quoteName('enabled') . ' <> 0');
		$query->where($db->quoteName('resource_license_id') . ' = ' . (int)$resource_id);

		$db->setQuery($query);

		$resource = null;
		try {
		    $resource = $db->loadObject();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getResource");
		}

		if ($resource) {
		    if($this->cleanHtml($resource->payment_header) == "")
		        $resource->payment_header = $this->getPaymentHeader();
		} else {
		    PayPerDownloadPlusDebug::debug("Error loading resource");
		}

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
			$url = "index.php?option=com_users&view=login";
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
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get resource name with id " . $resource_id);

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName('resource_name'));
		$query->from($db->quoteName('#__payperdownloadplus_resource_licenses'));
		$query->where($db->quoteName('resource_license_id') . ' = ' . (int)$resource_id);

		$db->setQuery($query);

		$resource_name = '';
		try {
		    $resource_name = $db->loadResult();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getResourceName");
		}

		if (empty($resource_name)) {
		    PayPerDownloadPlusDebug::debug("Failed getting resource name");
		}

		return $resource_name;
	}

	function handleResponse()
	{
		require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
		PayPerDownloadPlusDebug::debug("Handle response - download link");
		$this->startTransaction();

		$jinput = JFactory::getApplication()->input;

		$db = JFactory::getDBO();

		$usePayPlugin = $this->getUsePayPluginConfig();
		$payed = 0;
		$dealt = false;
		if($usePayPlugin)
		{
		    $gateway = $jinput->getString('gateway');
			PayPerDownloadPlusDebug::debug("Using payment plugin, gateway: " . $gateway);
			JPluginHelper::importPlugin("payperdownloadplus");
			$dispatcher	= JDispatcher::getInstance();
			$dispatcher->trigger('onPaymentReceived', array($gateway, &$dealt, &$payed, &$user_id, &$license_id, &$resource_id, &$transactionId, &$response, &$validate_response, &$status, &$amount, &$tax, &$fee, &$currency));
			if($dealt)
			{
				PayPerDownloadPlusDebug::debug("Got payment for gateway " . $transactionId);
				PayPerDownloadPlusDebug::debug("Response " . $response);
				if(!$transactionId)
				{
					$this->commitTransaction();
					PayPerDownloadPlusDebug::debug("Invalid transaction id");
					return;
				}
				$payer_email = "";
				$download_id = 0;
				$dispatcher->trigger('onGetDownloadLinkId', array($transactionId, &$download_id));
				$dispatcher->trigger('onGetPayerEmail', array($transactionId, &$payer_email));
				$payed = $payed ? 1 : 0;
				$notify_email = $payer_email;
				$resource_id_from_download_id = $this->getResourceFromDownloadLink($download_id);

				$columns = array(
				    'user_id',
				    'user_email',
				    'resource_id',
				    'payed',
				    'payment_date',
				    'txn_id',
				    'response',
				    'validate_response',
				    'status',
				    'amount',
				    'tax',
				    'fee',
				    'currency'
				);

				$values = array(
				    'NULL',
				    $db->quote($payer_email),
				    (int)$resource_id,
				    (int)$payed,
				    'NOW()',
				    $db->quote($transactionId),
				    $db->quote($response),
				    $db->quote($validate_response),
				    $db->quote($status),
				    (float)$amount,
				    (float)$tax,
				    (float)$fee,
				    $db->quote($currency)
				);

				$query = $db->getQuery(true);

				$query->insert($db->quoteName('#__payperdownloadplus_payments'));
				$query->columns($db->quoteName($columns));
				$query->values(implode(',', $values));

				$db->setQuery($query);

				$query_result = false;
				try {
				    $query_result = $db->execute();
				    $payment_id = $db->insertid();
				} catch (RuntimeException $e) {
				    PayPerDownloadPlusDebug::debug("Failed database query - handleResponse");
				}
			}
		}
		if(!$dealt && $this->getUsePaypal())
		{
			PayPerDownloadPlusDebug::debug("Using paypal gateway");

			$paymentInfo = $this->getPaymentInfo();
			$receiver_email = $jinput->getString('receiver_email'); // cmd (default) removes the @
			$business = $jinput->getString('business'); // cmd (default) removes the @
			if(trim(strtoupper($paymentInfo->paypal_account)) !== trim(strtoupper($receiver_email)) ||
				trim(strtoupper($paymentInfo->paypal_account)) !== trim(strtoupper($business)))
			{
				$this->commitTransaction();
				PayPerDownloadPlusDebug::debug("Configured paypal account is different than target account");
				return;
			}

			$req = 'cmd=_notify-validate';
			$text = '';
			foreach (/*$_POST*/$jinput->post->getArray() as $key => $value)
			{
				$text .= "(" . $key . " = " . $value . ")\r\n";
				$req .= "&" . $key . "=" . urlencode($value);
			}
			$payer_email = $jinput->getString('payer_email'); // cmd (default) removes the @
			$txn_id = $jinput->getString('txn_id');
			if(!$this->isTransactionPayed($txn_id))
			{
			    $validate_response = $this->validatePayment($req, $jinput->getInt('test_ipn', 0), $paymentInfo);
			    $payed = (strpos($validate_response, "VERIFIED") !== false) ? 1 : 0;
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
				PayPerDownloadPlusDebug::debug("Payment verification unsuccessfull");
				return;
			}

			$notify_email = "";

			$validate_response = 'VERIFIED'; //$db->escape($validate_response); because contains header, not just 'VERIFIED'

			$download_id = $jinput->getInt('custom');
			$resource_id = $jinput->getInt('item_number');
			$resource_id_from_download_id = $this->getResourceFromDownloadLink($download_id);
			$status = '';
			if($resource_id != $resource_id_from_download_id)
			{
				$payed = 0;
				$status = 'Invalid download id received;';
			}
			$payed_price = 0;
			if($payed)
			{
			    $payed_price = $jinput->getFloat('mc_gross', 0);
			    $currency_code = $jinput->getString('mc_currency');
				$payed = $this->validateResource($resource_id, $download_id, $payed_price, $currency_code) ? 1 : 0;
				if($payed)
				{
				    $status = trim(strtoupper($jinput->getString('payment_status')));
					$payed = ($status == 'COMPLETED') ? 1 : 0;
				}
			}

			$columns = array(
			    'user_id',
			    'user_email',
			    'resource_id',
			    'payed',
			    'payment_date',
			    'txn_id',
			    'response',
			    'validate_response',
			    'status',
			    'amount',
			    'tax',
			    'fee',
			    'currency',
			    'receiver_email'
			);

			$values = array(
			    'NULL',
			    $db->quote($payer_email),
			    $resource_id,
			    (int)$payed,
			    'NOW()',
			    $db->quote($txn_id),
			    $db->quote($text),
			    $db->quote($validate_response),
			    $db->quote($status),
			    $jinput->getFloat('mc_gross', 0),
			    $jinput->getFloat('tax', 0),
			    $jinput->getFloat('mc_fee', 0),
			    $db->quote($jinput->getString('mc_currency')),
			    $db->quote($receiver_email)
			);

			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__payperdownloadplus_payments'));
			$query->columns($db->quoteName($columns));
			$query->values(implode(',', $values));

			$db->setQuery($query);

			$query_result = false;
			try {
			    $query_result = $db->execute();
			    $payment_id = $db->insertid();
			} catch (RuntimeException $e) {
			    PayPerDownloadPlusDebug::debug("Failed database query - handleResponse " . $payer_email . ' ' . $resource_id . ' ' . $payed . ' ' . $txn_id . ' ' . $status . ' ' . $receiver_email);
			    PayPerDownloadPlusDebug::debug("Failed database query - handleResponse " . $text);
			}

			if($query_result)
				PayPerDownloadPlusDebug::debug("Payment saved");
			else
				PayPerDownloadPlusDebug::debug("Payment not saved");
		}
		$download_link = false;
		if($payed)
		{
		    $returnUrl = $jinput->getBase64('r', '');
			$returnUrl = base64_decode($returnUrl);
			$download_link = $this->setDownloadLinkPayed($download_id, $resource_id, $payer_email, $notify_email, $returnUrl);
			$resource_name = $this->getResourceName( $resource_id_from_download_id );
			$this->notifyPayment($payment_id, $payer_email, $download_link, $notify_email, $download_id, $resource_name);
		}
		else
			PayPerDownloadPlusDebug::debug("Payment was not finally successfull for some reason");
		$this->commitTransaction();
		$redirect = $jinput->getBase64('redirect', '');
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
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Create download link for resource id " . $resource_id);

		$resource_id = (int)$resource_id;

		$db = JFactory::getDBO();

		// Delete expired and not paid download links

		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__payperdownloadplus_download_links'));
		$query->where('(' . $db->quoteName('expiration_date') . ' < NOW() OR (' . $db->quoteName('payed') . ' = 0 AND TO_DAYS(NOW()) - TO_DAYS(' . $db->quoteName('creation_date') . ') > 1))');

		$db->setQuery($query);

		$query_result = false;
		try {
		    $query_result = $db->execute();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - createDownloadLink");
		}

		$secret_word = $this->getRandom();
		$random_value = $this->getRandom();

		$query->clear();

		$query->insert($db->quoteName('#__payperdownloadplus_download_links'));
		$query->columns($db->quoteName(array('resource_id', 'payed', 'creation_date', 'secret_word', 'random_value')));
		$query->values(implode(',', array((int)$resource_id, 0, 'NOW()', $db->quote($secret_word), $db->quote($random_value))));

		$db->setQuery($query);

		$query_result = false;
		try {
		    $query_result = $db->execute();
		    $download_id = $db->insertid();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - handleResponse");
		}

		$downloadLink = new stdClass();
		if($query_result)
		{
			$downloadLink->downloadId = $download_id;
			$downloadLink->secret_word = $secret_word;
			$downloadLink->random_value = $random_value;
			$downloadLink->accessCode = $download_id . "-" . sha1($secret_word . $random_value) . "-" . $random_value;
		}
		return $downloadLink;
	}

	function setDownloadLinkPayed($download_id, $resource_id, $payer_email, $user_email, $returnUrl)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Set download link payed");

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('download_expiration', 'max_download')));
		$query->from($db->quoteName('#__payperdownloadplus_resource_licenses'));
		$query->where($db->quoteName('resource_license_id') . ' = ' . (int)$resource_id);

		$db->setQuery($query);

		$res = null;
		try {
		    $res = $db->loadObject();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - setDownloadLinkPayed");
		}

		if (is_null($res)) {
		    PayPerDownloadPlusDebug::debug("Failed getting resource data");
		    return '';
		}

		$days = (int)$res->download_expiration;
		if($days <= 0)
			$days = 365;

		if($download_id)
		{
		    $query->clear();

		    $query->update($db->quoteName('#__payperdownloadplus_download_links'));

		    $fields = array(
		        $db->quoteName('expiration_date') . ' = DATE_ADD(NOW(), INTERVAL ' . $days . ' DAY)',
		        $db->quoteName('user_email') . ' = ' . $db->quote($user_email),
		        $db->quoteName('payer_email') . ' = ' . $db->quote($payer_email),
		        $db->quoteName('link_max_downloads') . ' = ' . (int)$res->max_download,
		        $db->quoteName('payed') . ' = 1'
		    );

		    $query->set($fields);
		    $query->where($db->quoteName('download_id') . ' = ' . (int)$download_id);

		    $db->setQuery($query);

		    $query_result = false;
		    try {
		        $query_result = $db->execute();

		    } catch (RuntimeException $e) {
		        PayPerDownloadPlusDebug::debug("Failed database query - setDownloadLinkPayed (2)");
		    }

		    if ($query_result) {
		        $query->clear();

		        $query->select($db->quoteName(array('secret_word', 'random_value', 'item_id', 'coupon_code')));
		        $query->from($db->quoteName('#__payperdownloadplus_download_links'));
		        $query->where($db->quoteName('download_id') . ' = ' . (int)$download_id);

		        $db->setQuery($query);

		        $downloadLink = null;
		        try {
		            $downloadLink = $db->loadObject();
		        } catch (RuntimeException $e) {
		            PayPerDownloadPlusDebug::debug("Failed database query - setDownloadLinkPayed (3)");
		        }

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
		}
		return "";
	}

	function getResourceFromDownloadLink($download_id)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get resource from download link with download id " . $download_id);

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName('resource_id'));
		$query->from($db->quoteName('#__payperdownloadplus_download_links'));
		$query->where($db->quoteName('download_id') . ' = ' . (int)$download_id);

		$db->setQuery($query);

		$resource_id = 0;
		try {
		    $resource_id = $db->loadResult();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getResourceFromDownloadLink");
		}

		return $resource_id;
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
		PayPerDownloadPlusDebug::debug("Validating payment");

		//return "VERIFIED";
		if ($test)
		{
			$root = JURI::root();
			if($paymentInfo->usesimulator)
				return "VERIFIED";
			else
			    //$paypal = "https://www.sandbox.paypal.com/cgi-bin/webscr'";
			    $paypal = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
		}
		else
		{
		    //$paypal = "https://www.paypal.com/cgi-bin/webscr'";
		    $paypal = 'https://ipnpb.paypal.com/cgi-bin/webscr';
		}

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_URL, $paypal);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true); // add the header so we can test the status
		curl_setopt($ch, CURLOPT_CAINFO, JPATH_ROOT.'/media/com_payperdownload/certificates/cacert.pem');

		//ob_start();

		$response = curl_exec($ch);
		$response_status = strval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

		if ($response === false || $response_status == '0') {
		    $response = curl_errno($ch).' : '.curl_error($ch);
		} else if (strpos($response_status, '200') === false) {
		    $response = 'Invalid response status: '.$response_status;
		}

		//$res = ob_get_contents();

		curl_close($ch);

		//ob_end_clean();

		PayPerDownloadPlusDebug::debug("Response from validate : " . $response);

		return $response;
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
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get max downloads for download id" . $download_id);

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName('link_max_downloads'));
		$query->from($db->quoteName('#__payperdownloadplus_download_links'));
		$query->where($db->quoteName('download_id') . ' = ' . (int)$download_id);

		$db->setQuery($query);

		$max_downloads = 0;
		try {
		    $max_downloads = $db->loadResult();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getMaxdownloads");
		}

		return $max_downloads;
	}

	function getDownloadObject($download_id)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get download object with download id " . $download_id);

	    $db = JFactory::getDBO();

	    $query = $db->getQuery(true);

	    $query->select('*');
	    $query->from($db->quoteName('#__payperdownloadplus_download_links'));
	    $query->where($db->quoteName('download_id') . ' = ' . (int)$download_id);

	    $db->setQuery($query);

	    $download = null;
	    try {
	        $download = $db->loadObject();
	    } catch (RuntimeException $e) {
	        PayPerDownloadPlusDebug::debug("Failed database query - getDownloadObject");
	    }

	    return $download;
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
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Update download link");

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__payperdownloadplus_download_links'));

		$fields = array(
		    $db->quoteName('email_subject') . ' = ' . $db->quote($subject),
		    $db->quoteName('email_text') . ' = ' . $db->quote($text),
		    $db->quoteName('download_link') . ' = ' . $db->quote($download_link)
		);

		$query->set($fields);
		$query->where($db->quoteName('download_id') . ' = ' . (int)$download_id);

		$db->setQuery($query);

		$query_result = false;
		try {
		    $query_result = $db->execute();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - updateDownloadLink");
		}

		if (!$query_result) {
		    PayPerDownloadPlusDebug::debug("Could not update download link");
		} else {
		    PayPerDownloadPlusDebug::debug("Updated download link");
		}
	}

	function isTransactionPayed($txn_id)
	{
		require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
		PayPerDownloadPlusDebug::debug("Is transaction payed with transaction id " . $txn_id);

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select('COUNT(*)');
		$query->from($db->quoteName('#__payperdownloadplus_payments'));
		$query->where($db->quoteName('txn_id') . ' = ' . $db->quote($txn_id));
		$query->where($db->quoteName('payed') . ' = 1');

		$db->setQuery($query);

		try {
		    $count = $db->loadResult();
		    if ($count > 0) {
		        return true;
		    }
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - isTransactionPayed");
		}

		return false;
	}

	function getRandom()
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for($i = 0; $i < 30; $i++){
			$index = mt_rand(0, strlen($characters) - 1);
        	$randomString .= $characters[$index];
		}
		return $randomString;
	}

	function getPaymentData($payment_id)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get payment data with payment id " . $payment_id);

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('amount', 'fee', 'tax', 'currency')));
		$query->from($db->quoteName('#__payperdownloadplus_payments'));

		$query->where($db->quoteName('payment_id') . ' = ' . (int)$payment_id);

		$db->setQuery($query);

		$payment = null;
		try {
		    $payment = $db->loadObject();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getPaymentData");
		}

		if(!$payment)
		    PayPerDownloadPlusDebug::debug("Failed getting payment data");

		return $payment;
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
	    require_once (JPATH_ADMINISTRATOR . "components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug($text);
	}
}
?>