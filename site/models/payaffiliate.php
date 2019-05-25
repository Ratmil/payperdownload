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

class PayPerDownloadModelPayAffiliate extends JModelLegacy
{
	var $config = null;

	function getConfig()
	{
		if($this->config)
			return $this->config;
		return new ConfigObject();
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

	function getPaymentInfo()
	{
		$config = $this->getConfig();
		$paymentInfo = new StdClass();
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

	function handleResponse()
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Handle response - affiliate");
		$this->startTransaction();

		$jinput = JFactory::getApplication()->input;

		$db = JFactory::getDBO();

		$usePayPlugin = $this->getUsePayPluginConfig();
		$payed = 0;
		if(!$payed)
		{
			$paymentInfo = $this->getPaymentInfo();
			$receiver_email = $jinput->getString('receiver_email'); // cmd (default) removes the @
			$business = $jinput->getString('business'); // cmd (default) removes the @

			$req = 'cmd=_notify-validate';
			$text = '';
			foreach (/*$_POST*/$jinput->post->getArray() as $key => $value) // $jinput->post->getArray() should work, test it
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

			$affiliate_user_id = $jinput->getInt('item_number');
			$status = '';
			$payed_price = 0;
			if($payed)
			{
			    $payed_price = $jinput->getFloat('mc_gross');
			    $currency_code = $jinput->getString('mc_currency');
				$payed = $this->validateAffiliatePayment($affiliate_user_id, $payed_price, $currency_code) ? 1 : 0;
				if($payed)
				{
				    $status = trim(strtoupper($jinput->getString('payment_status')));
					$payed = ($status == 'COMPLETED') ? 1 : 0;
				}
			}

			$columns = array(
			    'user_email',
			    'affiliate_user_id',
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
			    'to_merchant',
			    'receiver_email'
			);

			$values = array(
			    $db->quote($payer_email),
			    $affiliate_user_id,
			    (int)$payed,
			    'NOW()',
			    $db->quote($txn_id),
			    $db->quote($text),
			    $db->quote($validate_response),
			    $db->quote($status),
			    $jinput->getFloat('mc_gross'),
			    $jinput->getFloat('tax'),
			    $jinput->getFloat('mc_fee', 0),
			    $db->quote($jinput->getString('mc_currency')),
			    1,
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
		}
		if($payed)
		{
			if($affiliate_user_id)
			{
				$this->resetUserCredit($affiliate_user_id);
			}
		}
		$this->commitTransaction();
	}

	function resetUserCredit($affiliate_user_id)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
        PayPerDownloadPlusDebug::debug("Reset user credit");

        $db = JFactory::getDBO();

        $query = $db->getQuery(true);

        $query->update($db->quoteName('#__payperdownloadplus_affiliates_users'));

        $fields = array(
            $db->quoteName('credit') . ' = 0'
        );

        $query->set($fields);
        $query->where($db->quoteName('affiliate_user_id') . ' = ' . (int)$affiliate_user_id);

        $db->setQuery($query);

        $query_result = false;
        try {
            $query_result = $db->execute();

        } catch (RuntimeException $e) {
            PayPerDownloadPlusDebug::debug("Failed database query - resetUserCredit");
        }

        if (!$query_result) {
            PayPerDownloadPlusDebug::debug("Failed resetting user credit");
        }
	}

	function validateAffiliatePayment($affiliate_user_id, $payed_price, $currency_code)
	{
		$affiliate = $this->getAffiliateUserData($affiliate_user_id);
		return ($affiliate && (float)$payed_price - $affiliate->credit >= 0.00 && trim(strtoupper($affiliate->currency_code)) == trim(strtoupper($currency_code)));
	}

	function getAffiliateUserData($affiliate_user_id)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get affiliate user data");

		$db = JFactory::getDBO();

		$query->select($db->quoteName(array('affiliates_users.credit', 'licenses.currency_code')));
		$query->from($db->quoteName('#__payperdownloadplus_affiliates_users', 'affiliates_users'));
		$query->innerJoin($db->quoteName('#__payperdownloadplus_affiliates_programs', 'affiliates_programs') . ' ON (' . $db->quoteName('affiliates_users.affiliate_program_id') . ' = ' . $db->quoteName('affiliates_programs.affiliate_program_id') . ')');
		$query->innerJoin($db->quoteName('#__payperdownloadplus_licenses', 'licenses') . ' ON (' . $db->quoteName('affiliates_programs.license_id') . ' = ' . $db->quoteName('licenses.license_id') . ')');
		$query->where($db->quoteName('affiliates_users.affiliate_user_id') . ' = ' . (int)$affiliate_user_id);

		$db->setQuery($query);

		$data = null;
		try {
		    $data = $db->loadObject();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getAffiliateUserData");
		}

		return $data;
	}

	function isTransactionPayed($txn_id)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Check if transaction payed");

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

	function trace($text)
	{
		require_once (JPATH_ADMINISTRATOR . "components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug($text);
	}
}
?>