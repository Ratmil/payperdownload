<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;

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
			$url = "index.php?option=com_users&view=login";
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
		require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
		PayPerDownloadPlusDebug::debug("Get license with id " . $licenseId);

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('license_id', 'license_name', 'price', 'currency_code', 'description', 'notify_url', 'expiration', 'level', 'max_download', 'aup', 'user_group')));
		$query->from($db->quoteName('#__payperdownloadplus_licenses'));
		$query->where($db->quoteName('enabled') . ' <> 0');
		$query->where($db->quoteName('license_id') . ' = ' . (int)$licenseId);

		$db->setQuery($query);

		$license = null;
		try {
		    $license = $db->loadObject();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getLicense");
		}

		if ($license) {
		    $license->canRenew = $this->canLicenseBeRenewed($licenseId);
		} else {
			PayPerDownloadPlusDebug::debug("Error loading license");
		}

		return $license;
	}

	function getRenovationOptions($licenseId)
	{
		require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
		PayPerDownloadPlusDebug::debug("Get renewal options for license id " . $licenseId);

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName('renew'));
		$query->from($db->quoteName('#__payperdownloadplus_licenses'));
		$query->where($db->quoteName('license_id') . ' = ' . (int)$licenseId);

		$db->setQuery($query, 0, 1);

		$this->renew = 2; // never renewed
		try {
		    $this->renew = $db->loadResult();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getRenovationOptions");
		}

		return $this->renew;
	}

	function canLicenseBeRenewed($licenseId)
	{
		require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
		PayPerDownloadPlusDebug::debug("Check if license with id ' . $licenseId . ' can be renewed");

		$renew = $this->getRenovationOptions($licenseId);
		if($renew == 0) // Can be renewed always
			return true;
		$user = JFactory::getUser();
		if($user->id)
		{
    		$db = JFactory::getDBO();

			$query = $db->getQuery(true);

			$query->select('COUNT(*)');
			$query->from($db->quoteName('#__payperdownloadplus_users_licenses'));
			$query->where($db->quoteName('user_id') . ' = ' . (int)$user->id);
			$query->where($db->quoteName('license_id') . ' = ' . (int)$licenseId);
			if ($renew == 1) {
			    $query->where('(' . $db->quoteName('expiration_date') . ' IS NULL OR ' . $db->quoteName('expiration_date') . ' > NOW())');
			    $query->where('(' . $db->quoteName('license_max_downloads') . ' = 0 OR ' . $db->quoteName('download_hits') . ' < ' . $db->quoteName('license_max_downloads') . ')');
			}

			$db->setQuery($query);

			$active = 0;
			try {
			    $active = $db->loadResult();
			} catch (RuntimeException $e) {
			    PayPerDownloadPlusDebug::debug("Failed database query - canLicenseBeRenewed");
			}

			return $active <= 0;
		}

		return true;
	}

	function getHigherLicenses($min_level)
	{
		require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
		PayPerDownloadPlusDebug::debug("Get higher licenses");

		if($min_level <= 0)
			return array();

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName('license_id'));
		$query->from($db->quoteName('#__payperdownloadplus_licenses'));
		$query->where($db->quoteName('level') . ' > ' . (int)$min_level);

		$db->setQuery($query);

		$licenses = array();
		try {
		    $licenses = $db->loadColumn();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getHigherLicenses");
		}

		return $licenses;
	}

	function startTransaction()
	{
		$db = JFactory::getDBO();
		$db->setQuery("START TRANSACTION");
		$db->execute();
	}

	function commitTransaction()
	{
		$db = JFactory::getDBO();
		$db->setQuery("COMMIT");
		$db->execute();
	}

	function handleResponse()
	{
		require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
		PayPerDownloadPlusDebug::debug("Handle response - license");
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
			$dispatcher->trigger('onPaymentReceived', array($gateway, &$dealt, &$payed, &$user_id, &$license_id, &$resource_id, &$transactionId,
				&$response, &$validate_response, &$status, &$amount, &$tax, &$fee, &$currency));
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
				$dispatcher->trigger('onGetPayerEmail', array($transactionId, &$payer_email));
				$payed = $payed ? 1 : 0;
				if(!$payer_email && $user_id)
				{
					$user = $this->getUserData($user_id);
					if($user)
						$payer_email = $user->email;
				}
				$notify_email = $payer_email; // never used

				$columns = array(
				    'user_id',
				    'user_email',
				    'license_id',
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
			        (int)$user_id,
			        $db->quote($payer_email),
			        (int)$license_id,
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

				if($query_result)
				    PayPerDownloadPlusDebug::debug("Payment saved");
			    else
			        PayPerDownloadPlusDebug::debug("Payment not saved");
			}
		}
		if(!$dealt && $this->getUsePaypal())
		{
			PayPerDownloadPlusDebug::debug("Using paypal gateway");

			$paymentInfo = $this->getPaymentInfo();
			$receiver_email = $jinput->getString('receiver_email'); // cmd (default) removes the @
			$business = $jinput->getString('business'); // cmd (default) removes the @
			if (trim(strtoupper($paymentInfo->paypal_account)) !== trim(strtoupper($receiver_email)) ||
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

			$user_id = $jinput->getInt('custom', 0);

            $validate_response = 'VERIFIED'; //$db->escape($validate_response); because contains header, not just 'VERIFIED'

			$license_id = $jinput->getInt('item_number');
			$status = '';
			$payed_price = 0;
			if($payed)
			{
			    $payed_price = $jinput->getFloat('mc_gross', 0);
			    $currency_code = $jinput->getString('mc_currency');
				$payed = $this->validateLicense($license_id, $payed_price, $currency_code, $user_id) ? 1 : 0;
				if($payed)
				{
				    $status = trim(strtoupper($jinput->getString('payment_status')));
					$payed = ($status == 'COMPLETED') ? 1 : 0;
				}
			}

			$amount = $jinput->getFloat('mc_gross', 0);

			$columns = array(
			    'user_id',
			    'user_email',
			    'license_id',
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
			    $user_id,
			    $db->quote($payer_email),
			    $license_id,
			    (int)$payed,
			    'NOW()',
			    $db->quote($txn_id),
			    $db->quote($text),
			    $db->quote($validate_response),
			    $db->quote($status),
			    $amount,
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
			    PayPerDownloadPlusDebug::debug("Failed database query - handleResponse");
			}

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
		$redirect = $jinput->getBase64('redirect', '');
		if($redirect)
		{
			$redirect = base64_decode($redirect);
			$mainframe = JFactory::getApplication();
			$mainframe->enqueueMessage(JText::_("PAYPERDOWNLOADPLUS_THANK_YOU") . " - Status: " . $validate_response);
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
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Assign affiliate credit");

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName('referer_user'));
		$query->from($db->quoteName('#__payperdownloadplus_affiliates_users_refered'));
		$query->where($db->quoteName('refered_user') . ' = ' . (int)$user_id);

		$db->setQuery($query);

		$referer_user = 0;
		try {
		    $referer_user = (int)$db->loadResult();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - assignAffiliateCredit");
		}

		if($referer_user > 0)
		{
		    $query->clear();

		    $query->select($db->quoteName(array('affiliates_programs.percent', 'affiliates_programs.affiliate_program_id')));
		    $query->from($db->quoteName('#__payperdownloadplus_affiliates_users', 'affiliates_users'));
		    $query->innerJoin($db->quoteName('#__payperdownloadplus_affiliates_programs', 'affiliates_programs') . ' ON (' . $db->quoteName('affiliates_users.affiliate_program_id') . ' = ' . $db->quoteName('affiliates_programs.affiliate_program_id') . ')');
		    $query->where($db->quoteName('affiliates_programs.license_id') . ' = ' . (int)$license_id);
		    $query->where($db->quoteName('affiliates_users.user_id') . ' = ' . $referer_user);

		    $db->setQuery($query);

		    $data = null;
		    try {
		        $data = $db->loadObject();
		    } catch (RuntimeException $e) {
		        PayPerDownloadPlusDebug::debug("Failed database query - assignAffiliateCredit (2)");
		    }

		    if ($data) {
    			$percent = (float)$data->percent;
    			$credit = ($percent / 100.00) * $amount;

				$query->clear();

    			$query->update($db->quoteName('#__payperdownloadplus_affiliates_users'));
    			$query->set($db->quoteName('credit') . ' = ' . $db->quoteName('credit') . ' + ' .(float)$credit);
    			$query->where($db->quoteName('user_id') . ' = ' . $referer_user);
    			$query->where($db->quoteName('affiliate_program_id') . ' = ' . (int)$data->affiliate_program_id);

    			$db->setQuery($query);

    			$query_result = false;
    			try {
    			    $query_result = $db->execute();
    			} catch (RuntimeException $e) {
    			    PayPerDownloadPlusDebug::debug("Failed database query - assignAffiliateCredit (3)");
    			}

    			if ($query_result) {
    			    PayPerDownloadPlusDebug::debug("Assigned affiliate credit");
    			} else {
    			    PayPerDownloadPlusDebug::debug("Didn't assign affiliate credit");
    			}
		    }
		}
	}

	function getRandom()
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
		$randomString = ''; 
		for($i = 0; $i < 30; $i++){
			$index = rand(0, strlen($characters) - 1); 
        	$randomString .= $characters[$index]; 
		}
		return $randomString;
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
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
		PayPerDownloadPlusDebug::debug("Delete license discount");

	    $db = JFactory::getDBO();

	    $query = $db->getQuery(true);

		$query->delete($db->quoteName('#__payperdownloadplus_users_licenses_discount'));

		$conditions = array(
		    $db->quoteName('user_id') . ' = ' . (int)$user_id,
		    $db->quoteName('license_id') . ' = ' . (int)$license_id
		);

		$query->where($conditions);

		$db->setQuery($query);

		$query_result = false;
		try {
		    $query_result = $db->execute();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - deleteLicenseDiscount");
		}

		if ($query_result)
		    PayPerDownloadPlusDebug::debug("License discount deleted");
		else
            PayPerDownloadPlusDebug::debug("License discount not deleted");
	}

	function getLicenseDiscount($user_id, $license_id)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get license discount data");

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('discount', 'coupon_code')));
		$query->from($db->quoteName('#__payperdownloadplus_users_licenses_discount'));
		$query->where($db->quoteName('user_id') . ' = ' . (int)$user_id);
		$query->where($db->quoteName('license_id') . ' = ' . (int)$license_id);

		$db->setQuery($query);

		$discount = null;
		try {
		    $discount = $db->loadObject();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getLicenseDiscount");
		}

		return $discount;
	}

	function getUserData($user_id)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get user data");

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('name', 'email')));
		$query->from($db->quoteName('#__users'));
		$query->where($db->quoteName('id') . ' = ' . (int)$user_id);

		$db->setQuery($query);

		$user = null;
		try {
		    $user = $db->loadObject();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getUserData");
		}

		return $user;
	}

	function getPaymentData($payment_id)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get payment data with payment id " . $payment_id);

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('payments.amount', 'payments.fee', 'payments.tax', 'payments.txn_id', 'payments.currency',
		    'licenses.license_id', 'licenses.license_name', 'licenses.member_title', 'licenses.level', 'licenses.max_download', 'licenses.expiration', 'licenses.price')));
		$query->select($db->quoteName('licenses.currency_code', 'license_currency_code'));
		$query->from($db->quoteName('#__payperdownloadplus_payments', 'payments'));
		$query->leftJoin($db->quoteName('#__payperdownloadplus_licenses', 'licenses') . ' ON (' . $db->quoteName('payments.license_id') . ' = ' . $db->quoteName('licenses.license_id') . ')');
		$query->where($db->quoteName('payments.payment_id') . ' = ' . (int)$payment_id);

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

		if ($query_result)
		    PayPerDownloadPlusDebug::debug("Download link updated");
	    else
	        PayPerDownloadPlusDebug::debug("Download link not updated");
	}

	function getUserLicenseExpiration($user_license_id)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get user license expiration for user license id " . $user_license_id);

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName('expiration_date'));
		$query->from($db->quoteName('#__payperdownloadplus_users_licenses'));
		$query->where($db->quoteName('user_license_id') . ' = ' . (int)$user_license_id);

		$db->setQuery($query);

		$expiration_date = null;
		try {
		    $expiration_date = $db->loadResult();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getUserLicenseExpiration");
		}

		return $expiration_date;
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

		$db = JFactory::getDBO();

		$license = $this->getLicense($license_id);
		if(!$license)
		{
			PayPerDownloadPlusDebug::debug("Error loading license");
			return;
		}
		$days = (int)$license->expiration;
		$max_downloads = (int)$license->max_download;
		$credit_days = 0;
		$duration = $days;
		if($set_credit_days)
		{
			$credit_days = $duration;
		}
		$userLicense = $this->getUserLicense($user_id, $license_id);
		$assigned_user_group = 'NULL';
		if($license->user_group)
			$assigned_user_group = (int)$license->user_group;
		$user_license_id = 0;
		if(!$userLicense)
		{
		    PayPerDownloadPlusDebug::debug("Create license");

		    $query = $db->getQuery(true);

		    $columns = array(
		        'license_id',
		        'user_id',
		        'expiration_date',
		        'credit',
		        'duration',
		        'credit_days_used',
		        'license_max_downloads',
		        'assigned_user_group'
		        , 'item', // used for download id
		    );
		    if ($days > 0) {
		        $values = array(
		            (int)$license_id,
		            (int)$user_id,
		            'DATE_ADD(NOW(), INTERVAL ' . $days . ' DAY)',
		            (int)$credit,
		            (int)$duration,
		            (int)$credit_days,
		            (int)$max_downloads,
		            (int)$assigned_user_group
		            , 'MD5(CONCAT(' . (int)$user_id . ', NOW()))' // download id
		        );
		    } else {
		        $values = array(
		            (int)$license_id,
		            (int)$user_id,
		            'NULL',
		            0,
		            0,
		            0,
		            (int)$max_downloads,
		            (int)$assigned_user_group
		            , 'MD5(CONCAT(' . (int)$user_id . ', NOW()))' // download id
		        );
		    }

		    $query->insert($db->quoteName('#__payperdownloadplus_users_licenses'));
		    $query->columns($db->quoteName($columns));
		    $query->values(implode(',', $values));

		    $db->setQuery($query);

		    try {
		        $query_result = $db->execute();
                $user_license_id = $db->insertid();
		    } catch (Exception $e) {
		        PayPerDownloadPlusDebug::debug("Failed database query - assignLicense");
		    }

		    if (!$query_result) {
		        PayPerDownloadPlusDebug::debug("Failed creating license");
		    }
		}
		else
		{
		    PayPerDownloadPlusDebug::debug("Update license");

		    $query = $db->getQuery(true);

		    $query->update($db->quoteName('#__payperdownloadplus_users_licenses'));

			if($days > 0)
			{
			    $fields = array(
			        $db->quoteName('expiration_date') . ' = DATE_ADD(' . $db->quoteName('expiration_date') . ', INTERVAL ' . $days . ' DAY)',
			        $db->quoteName('credit') . ' = ' . $db->quoteName('credit') . ' + ' . (int)$credit,
			        $db->quoteName('license_max_downloads') . ' = ' . $db->quoteName('license_max_downloads') . ' + ' . (int)$max_downloads,
			        $db->quoteName('duration') . ' = ' . $db->quoteName('duration') . ' + ' . (int)$duration,
			        $db->quoteName('assigned_user_group') . ' = ' . (int)$assigned_user_group
			    );

			    if($set_credit_days)
			    {
			        $fields[] = $db->quoteName('credit_days_used') . ' = ' .$db->quoteName('duration');
			    }
			}
			else
			{
			    $fields = array(
			        $db->quoteName('expiration_date') . ' = DATE_ADD(' . $db->quoteName('expiration_date') . ', INTERVAL ' . $days . ' DAY)',
			        $db->quoteName('credit') . ' = ' . $db->quoteName('credit') . ' + ' . (int)$credit,
			        $db->quoteName('expiration_date') . ' = NULL',
			        $db->quoteName('license_max_downloads') . ' = ' . $db->quoteName('license_max_downloads') . ' + ' . (int)$max_downloads,
			        $db->quoteName('duration') . ' = 0',
			        $db->quoteName('credit_days_used') . ' = 0',
			        $db->quoteName('assigned_user_group') . ' = ' . (int)$assigned_user_group
			    );
			}

			$query->set($fields);
			$query->where($db->quoteName('user_license_id') . ' = ' . $db->quote($userLicense->user_license_id));

			$db->setQuery($query);

			$query_result = false;
			try {
			    $query_result = $db->execute();
			} catch (RuntimeException $e) {
			    PayPerDownloadPlusDebug::debug("Failed database query - assignLicense");
			}

			if(!$query_result)
				PayPerDownloadPlusDebug::debug("Failed updating license");

			$user_license_id = $userLicense->user_license_id;
		}
		$this->removeUsedCredit($license, $user_id);
		$this->assignUserGroup($license, $user_id);
		$license_discount = $this->getLicenseDiscount( $user_id, $license_id );
		if($license_discount)
		{
			PayPerDownloadPlusDebug::debug("Searching coupon user");

			$query->clear();

			$query->select($db->quoteName('user_id'));
			$query->from($db->quoteName('#__payperdownloadplus_coupons_users'));
			$query->where($db->quoteName('user_id') . ' = ' . (int)$user_id);
			$query->where($db->quoteName('coupon_code') . ' = ' . $db->quote($license_discount->coupon_code));

			$db->setQuery($query);

			try {
			    $user = $db->loadResult();

			    if(!$user)
			    {
			        PayPerDownloadPlusDebug::debug("Inserting coupon user");

			        $query->clear();

			        $query->insert($db->quoteName('#__payperdownloadplus_coupons_users'));
			        $query->columns($db->quoteName(array('coupon_code', 'user_id')));
			        $query->values(implode(',', array($db->quote($license_discount->coupon_code), (int)$user_id)));

			        $db->setQuery($query);

			        if (!$db->execute()) {
			            PayPerDownloadPlusDebug::debug("Failed inserting coupon user");
			        }
			    }
			} catch (RuntimeException $e) {
			    PayPerDownloadPlusDebug::debug("Failed database query - assignLicense (2)");
			}

			$this->deleteLicenseDiscount( $user_id, $license_id );
		}
		return $user_license_id;
	}

	function assignUserGroup($license, $user_id)
	{
		if($license->user_group)
		{
			$user = JFactory::getUser($user_id);
			if(array_search($license->user_group,  $user->groups) === false)
			{
				$user->groups []= $license->user_group;
				$user->save();
			}
		}
	}

	function getUserLicense($user_id, $license_id)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get user licenses");

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('licenses.license_id', 'users_licenses.user_license_id', 'users_licenses.expiration_date', 'licenses.expiration', 'licenses.level', 'users_licenses.user_id')));
		$query->from($db->quoteName('#__payperdownloadplus_users_licenses', 'users_licenses'));
		$query->innerJoin($db->quoteName('#__payperdownloadplus_licenses', 'licenses') . ' ON (' . $db->quoteName('users_licenses.license_id') . ' = ' . $db->quoteName('licenses.license_id') . ')');
		$query->where('(' . $db->quoteName('users_licenses.expiration_date') . ' > NOW() OR ' . $db->quoteName('users_licenses.expiration_date') . ' IS NULL)');
		$query->where($db->quoteName('users_licenses.license_id') . ' = ' . (int)$license_id);
		$query->where($db->quoteName('users_licenses.enabled') . ' <> 0');
		$query->where($db->quoteName('users_licenses.user_id') . ' = ' . (int)$user_id);

		$db->setQuery($query);

		$result = null;
		try {
		    $result = $db->loadObject();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getUserLicense");
		}

		if(!$result)
		    PayPerDownloadPlusDebug::debug("No current license exists"); // not really a fail, it checks if the license is still current to later update it rather than creating a new one
		return $result;
	}

	function isTransactionPayed($txn_id)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Is transaction payed for transaction id " . $txn_id);

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

	function getLicenseResources($lid)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get license resources for license id " . $lid);

		$db = JFactory::getDBO();

		$license = $this->getLicense($lid);
		if($license)
		{
			$query = $db->getQuery(true);

			$fields = $db->quoteName(array('resource_licenses.resource_license_id', 'resource_licenses.resource_name', 'resource_licenses.resource_description', 'resource_licenses.resource_type', 'resource_licenses.alternate_resource_description'));
			$fields[0] = 'DISTINCT ' . $fields[0]; // prepend distinct to the first quoted field

			$query->select($fields);
			$query->from($db->quoteName('#__payperdownloadplus_resource_licenses', 'resource_licenses'));
			$query->innerJoin($db->quoteName('#__payperdownloadplus_licenses', 'licenses') . ' ON (' . $db->quoteName('resource_licenses.license_id') . ' = ' . $db->quoteName('licenses.license_id') . ')');
			$query->where('(' . $db->quoteName('licenses.license_id') . ' = ' . (int)$lid . ' OR (' . $db->quoteName('licenses.level') . ' > 0 AND ' . $db->quoteName('licenses.level') . ' < ' . (int)$license->level . ' ))');
			$query->where($db->quoteName('resource_licenses.enabled') . ' = 1');
			$query->order($db->quoteName('resource_licenses.resource_type') . ' DESC');
			$query->order($db->quoteName('resource_licenses.resource_name') . ' ASC');

			$db->setQuery($query);

			$resources = null;
			try {
			    $resources = $db->loadObjectList();
			} catch (RuntimeException $e) {
			    PayPerDownloadPlusDebug::debug("Failed database query - getLicenseResources");
			}

			return $resources;
		}
		return null;
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

	function getDiscountLicense($license, $user_id)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
		PayPerDownloadPlusDebug::debug("Get discount license");

		$db = JFactory::getDBO();

		$config = $this->getConfig();
		$price = $license->price;
		if($config->get('apply_discount', 1) == 1)
		{
		    $query = $db->getQuery(true);

		    $query->select('TO_DAYS(' . $db->quoteName('users_licenses.expiration_date') . ') - TO_DAYS(NOW()) - ' . $db->quoteName('users_licenses.credit_days_used') . ' AS remaining');
		    $query->select($db->quoteName('users_licenses.duration') . ' - ' . $db->quoteName('users_licenses.credit_days_used') . ' AS total');
		    $query->select($db->quoteName('users_licenses.credit'));
		    $query->from($db->quoteName('#__payperdownloadplus_users_licenses', 'users_licenses'));
		    $query->innerJoin($db->quoteName('#__payperdownloadplus_licenses', 'licenses') . ' ON (' . $db->quoteName('users_licenses.license_id') . ' = ' . $db->quoteName('licenses.license_id') . ')');
		    $query->where($db->quoteName('users_licenses.expiration_date') . ' > NOW()');
		    $query->where($db->quoteName('users_licenses.enabled') . ' <> 0');
		    $query->where($db->quoteName('users_licenses.user_id') . ' = ' . (int)$user_id);
		    $query->where($db->quoteName('licenses.enabled') . ' <> 0');
		    $query->where($db->quoteName('licenses.level') . ' > 0');
		    $query->where($db->quoteName('licenses.level') . ' < ' . (int)$license->level);
		    $query->where($db->quoteName('licenses.currency_code') . ' = ' . $db->quote($license->currency_code));

		    $db->setQuery($query);

		    $valids = null;
		    try {
		        $valids = $db->loadObjectList();
		    } catch (RuntimeException $e) {
		        PayPerDownloadPlusDebug::debug("Failed database query - getDiscountLicense");
		    }

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
		    $query = $db->getQuery(true);

		    $query->select('COUNT(*)');
		    $query->from($db->quoteName('#__payperdownloadplus_users_licenses', 'users_licenses'));
		    $query->innerJoin($db->quoteName('#__payperdownloadplus_licenses', 'licenses') . ' ON (' . $db->quoteName('users_licenses.license_id') . ' = ' . $db->quoteName('licenses.license_id') . ')');
		    $query->where($db->quoteName('users_licenses.expiration_date') . ' > NOW()');
		    $query->where($db->quoteName('users_licenses.enabled') . ' <> 0');
		    $query->where($db->quoteName('users_licenses.user_id') . ' = ' . (int)$user_id);
		    $query->where($db->quoteName('licenses.enabled') . ' <> 0');
		    $query->where($db->quoteName('licenses.license_id') . ' = ' . (int)$license->license_id);

			$db->setQuery($query);

			$count = 0;
			try {
			    $count = $db->loadResult();
			} catch (RuntimeException $e) {
			    PayPerDownloadPlusDebug::debug("Failed database query - getDiscountLicense (2)");
			}

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
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
		PayPerDownloadPlusDebug::debug("Remove used credit");

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName('users_licenses.user_license_id'));
		$query->from($db->quoteName('#__payperdownloadplus_users_licenses', 'users_licenses'));
		$query->innerJoin($db->quoteName('#__payperdownloadplus_licenses', 'licenses') . ' ON (' . $db->quoteName('users_licenses.license_id') . ' = ' . $db->quoteName('licenses.license_id') . ')');
		$query->where($db->quoteName('users_licenses.expiration_date') . ' > NOW()');
		$query->where($db->quoteName('users_licenses.enabled') . ' <> 0');
		$query->where($db->quoteName('users_licenses.user_id') . ' = ' . (int)$user_id);
		$query->where($db->quoteName('licenses.enabled') . ' <> 0');
		$query->where($db->quoteName('licenses.level') . ' > 0');
		$query->where($db->quoteName('licenses.level') . ' < ' . (int)$license->level);
		$query->where($db->quoteName('licenses.currency_code') . ' = ' . $db->quote($license->currency_code));

		$db->setQuery($query);

		$user_licenses = array();
		try {
		    $user_licenses = $db->loadColumn();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - removeUsedCredit");
		}

		if(count($user_licenses) > 0)
		{
			$query->clear();

			$query->update($db->quoteName('#__payperdownloadplus_users_licenses'));

		    $fields = array(
		        $db->quoteName('credit') . ' = 0',
		        $db->quoteName('credit_days_used') . ' = ' . $db->quoteName('duration')
		    );

			$query->set($fields);
			$query->where($db->quoteName('user_license_id') . ' IN (' . implode(",", $user_licenses) . ')');

			$db->setQuery($query);

			$query_result = false;
			try {
			    $query_result = $db->execute();
			} catch (RuntimeException $e) {
			    PayPerDownloadPlusDebug::debug("Failed database query - removeUsedCredit (2)");
			}
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
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
		PayPerDownloadPlusDebug::debug("Create download link");

		$user_id = JFactory::getUser()->id;

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

		if($itemId)
			$dbItemId = $db->quote($itemId);
		else
			$dbItemId = 'NULL';

		$query->clear();

		$query->insert($db->quoteName('#__payperdownloadplus_download_links'));
		$query->columns($db->quoteName(array('resource_id', 'item_id', 'payed', 'creation_date', 'secret_word', 'random_value', 'user_id')));
		$query->values(implode(',', array((int)$resource_id, $dbItemId, 0, 'NOW()', $db->quote($secret_word), $db->quote($random_value), (int)$user_id)));

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
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get resource with id " . $resource_id);

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select('*');
		$query->from($db->quoteName('#__payperdownloadplus_resource_licenses'));
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
		        $resource->payment_header = $this->getResourcePaymentHeader();
		    if($this->cleanHtml($resource->payment_header) == "")
		        $resource->payment_header = JText::_('PAYPERDOWNLOAD_PLUS_PAYRESOURCE_HEADER');
		}

		return $resource;
	}

	function applyDiscountCoupon($coupon_code, $price, $payItemId, $itemIsLicense)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Apply discount coupon");

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select('*');
		$query->from($db->quoteName('#__payperdownloadplus_coupons'));
		$query->where($db->quoteName('expire_time') . ' >= NOW()');
		$query->where($db->quoteName('code') . ' = ' . $db->quote(strtoupper($coupon_code)));

		$db->setQuery($query);

		$coupon = null;
		try {
		    $coupon = $db->loadObject();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - applyDiscountCoupon");
		}

		if($coupon)
		{
			$user_id = JFactory::getUser()->id;

			$query->clear();

			$query->select($db->quoteName('user_id'));
			$query->from($db->quoteName('#__payperdownloadplus_coupons_users'));
			$query->where($db->quoteName('coupon_code') . ' = ' . $db->quote($coupon->code));
			$query->where($db->quoteName('user_id') . ' = ' . (int)$user_id);

			$db->setQuery($query);

			$result = '';
			try {
			    $result = $db->loadResult();
			} catch (RuntimeException $e) {
			    PayPerDownloadPlusDebug::debug("Failed database query - applyDiscountCoupon (2)");
			}

			if($result) // If user already used this code then ignore
			    return null;

			$newPrice = $price * (1.0 - $coupon->discount/100.0);
			if($itemIsLicense)
			{
			    $query->clear();

			    $query->select($db->quoteName('user_id'));
			    $query->from($db->quoteName('#__payperdownloadplus_users_licenses_discount'));
			    $query->where($db->quoteName('license_id') . ' = ' . (int)$payItemId);
			    $query->where($db->quoteName('user_id') . ' = ' . (int)$user_id);

			    $db->setQuery($query);

			    $result = '';
			    try {
			        $result = $db->loadResult();
			    } catch (RuntimeException $e) {
			        PayPerDownloadPlusDebug::debug("Failed database query - applyDiscountCoupon (3)");
			    }

				if($result)
				{
				    $query->clear();

				    $query->update($db->quoteName('#__payperdownloadplus_users_licenses_discount'));

				    $fields = array(
				        $db->quoteName('discount') . ' = ' . (float)$coupon->discount,
				        $db->quoteName('coupon_code') . ' = ' . $db->quote($coupon->code)
				    );

				    $query->set($fields);
				    $query->where($db->quoteName('license_id') . ' = ' . (int)$payItemId);
				    $query->where($db->quoteName('user_id') . ' = ' . (int)$user_id);

				    $db->setQuery($query);

				    $query_result = false;
				    try {
				        $query_result = $db->execute();
				    } catch (RuntimeException $e) {
				        PayPerDownloadPlusDebug::debug("Failed database query - updateDownloadLink (4)");
				    }
				}
				else
				{
				    $query->clear();

				    $query->insert($db->quoteName('#__payperdownloadplus_users_licenses_discount'));
				    $query->columns($db->quoteName(array('license_id', 'user_id', 'discount', 'coupon_code')));
				    $query->values(implode(',', array((int)$payItemId, (int)$user_id, (float)$coupon->discount, $db->quote($coupon->code))));

				    $db->setQuery($query);

				    $query_result = false;
				    try {
				        $query_result = $db->execute();
				    } catch (RuntimeException $e) {
				        PayPerDownloadPlusDebug::debug("Failed database query - applyDiscountCoupon (4)");
				    }
				}
			}
			else
			{
			    $query->clear();

			    $query->update($db->quoteName('#__payperdownloadplus_download_links'));

			    $fields = array(
			        $db->quoteName('discount') . ' = ' . (float)$coupon->discount,
			        $db->quoteName('coupon_code') . ' = ' . $db->quote($coupon->code)
			    );

			    $query->set($fields);
			    $query->where($db->quoteName('download_id') . ' = ' . (int)$payItemId);

			    $db->setQuery($query);

			    $query_result = false;
			    try {
			        $query_result = $db->execute();
			    } catch (RuntimeException $e) {
			        PayPerDownloadPlusDebug::debug("Failed database query - updateDownloadLink (3)");
			    }
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