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
		$this->startTransaction();
		$usePayPlugin = $this->getUsePayPluginConfig();
		$payed = 0;
		if(!$payed)
		{
			$paymentInfo = $this->getPaymentInfo();
			$receiver_email = JRequest::getVar('receiver_email');
			$business = JRequest::getVar('business');
		
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
				return;
			}
				
			if(!$payed)
			{
				$this->commitTransaction();
				return;
			}
			
			$txn_id = $db->escape($txn_id);
			$text = $db->escape($text);
			$validate_response = $db->escape($validate_response);
			$affiliate_user_id = JRequest::getInt('item_number');
			$status = '';
			$payed_price = 0;
			if($payed)
			{
				$payed_price = JRequest::getFloat('mc_gross');
				$currency_code = JRequest::getVar('mc_currency');
				$payed = $this->validateAffiliatePayment($affiliate_user_id, $payed_price, $currency_code) ? 1 : 0;
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
				#__payperdownloadplus_payments(user_email, affiliate_user_id, payed, payment_date, txn_id, response, validate_response, status, amount, tax, fee, currency, to_merchant, receiver_email)
				VALUES('$esc_user_email', $affiliate_user_id, $payed, NOW(), '$txn_id', '$text', '$validate_response', '$status', '$amount', '$tax', '$fee', '$mc_currency', 1, '$esc_receiver_email')";
			$db->setQuery( $query );
			$query_result = $db->query();
			$payment_id = $db->insertid();
			
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
		$affiliate_user_id = (int)$affiliate_user_id;
		$db = JFactory::getDBO();
		$db->setQuery("UPDATE #__payperdownloadplus_affiliates_users SET credit = 0 WHERE affiliate_user_id = " . (int)$affiliate_user_id);
		$db->query();
	}
	
	function validateAffiliatePayment($affiliate_user_id, $payed_price, $currency_code)
	{
		$affiliate = $this->getAffiliateUserData($affiliate_user_id);
		return ($affiliate && (float)$payed_price - $affiliate->credit >= 0.00 && trim(strtoupper($affiliate->currency_code)) == trim(strtoupper($currency_code)));
	}
	
	function getAffiliateUserData($affiliate_user_id)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT #__payperdownloadplus_affiliates_users.credit, #__payperdownloadplus_licenses.currency_code
			FROM #__payperdownloadplus_affiliates_users
			INNER JOIN #__payperdownloadplus_affiliates_programs
			ON #__payperdownloadplus_affiliates_users.affiliate_program_id = #__payperdownloadplus_affiliates_programs.affiliate_program_id
			INNER JOIN #__payperdownloadplus_licenses
			ON #__payperdownloadplus_affiliates_programs.license_id = #__payperdownloadplus_licenses.license_id
			WHERE affiliate_user_id = ' . (int)$affiliate_user_id);
		return $db->loadObject();
	}
	
	function isTransactionPayed($txn_id)
	{
		$db = JFactory::getDBO();
		$txn_id = $db->escape($txn_id);
		$query = "SELECT payment_id FROM #__payperdownloadplus_payments WHERE txn_id = '$txn_id' AND payed = 1";
		$db->setQuery( $query );
		return $db->loadResult();
	}
	
	
	function validatePayment($req, $test, $paymentInfo)
	{
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
	
	function trace($text)
	{
		/*$db = JFactory::getDBO();
		$text = $db->escape($text);
		$query = "INSERT INTO #__payperdownloadplus_debug(debug_text, debug_time)
		   VALUES('$text', NOW())";
		$db->setQuery($query);
		$db->query();*/
	}
}
?>