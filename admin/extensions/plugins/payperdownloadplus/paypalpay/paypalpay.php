<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

defined( '_JEXEC' ) or die;

jimport('joomla.event.plugin');

class plgPayperDownloadPlusPayPalPay extends JPlugin
{
    protected static $tax_percentage;
    protected static $use_coupons;

	public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        $this->loadLanguage();
	}

	public function onRenderPaymentForm($user, $license, $resource, $returnUrl, $thankyouUrl)
	{
	    //$account_email = $this->params->get('paypal_account', '');

	    $paypal = 'https://www.paypal.com/cgi-bin/webscr';
	    if ($this->params->get('test_mode', 0)) {
	        if ($this->params->get('use_simulator', 0)) {
				$paypal = 'index.php?option=com_payperdownload&amp;task=paypalsim';
	        } else {
				$paypal = "https://www.sandbox.paypal.com/cgi-bin/webscr";
	        }
	    }

	    if ($license || $resource) {

	        $task = '';
	        $type = '';

	        if ($resource) {
	            $amount = $resource->resource_price;
	            $currency = $resource->resource_price_currency;
	            $item_id = $resource->resource_license_id;
	            $name = $resource->resource_name;
	            //$download_id = $resource->download_id;
	            $custom = $resource->download_id;

	            if ($resource->alternate_resource_description) {
	                $description = $resource->alternate_resource_description;
	            } else {
	                $description = $resource->resource_description;
	            }

	            $task = 'confirmres';
	            $type = 'resource';
	        } else {
	            $amount = $license->price;
	            $currency = $license->currency_code;
	            $item_id = $license->license_id;
	            $name = $license->license_name;
	            $custom = $user->id;
	            $description = $license->description;

	            $task = 'confirm';
	            $type = 'license';
	        }

	        $js_declaration = <<< JS
                window.addEventListener('load', function() {
                    document.getElementById('paypal-checkout-button').addEventListener('click', function () {
                        document.getElementById("paypal-payment-form").submit();
                    });
                }, false);
JS;
	        JFactory::getDocument()->addScriptDeclaration($js_declaration);

	        echo '<form id="paypal-payment-form" name="paypal-payment-form" action="index.php?option=com_payperdownload" method="post">';

	        echo '<input type="hidden" name="task" value="paypalpay"/>';
	        echo '<input type="hidden" name="ppd_item_type" value="' . $type . '"/>';
	        echo '<input type="hidden" name="cmd" value="_xclick"/>';
	        echo '<input type="hidden" name="custom" value="' . htmlspecialchars($custom) . '"/>';
	        echo '<input type="hidden" name="item_number" value="' . htmlspecialchars($item_id) . '"/>';
	        echo '<input type="hidden" name="item_name" value="' . htmlspecialchars($name) . '"/>';
	        echo '<input type="hidden" name="amount" value="' . htmlspecialchars($amount) . '"/>';
	        echo '<input type="hidden" name="currency_code" value="' . htmlspecialchars($currency) . '"/>';

	        if ($this->_getTaxPercentage() >= 0.01) {
	            echo '<input type="hidden" name="tax" value="' . htmlspecialchars(sprintf("%01.2f", $amount * $this->_getTaxPercentage() / 100.0)) . '"/>';
	        }

	        $notifyParams = '';
	        if ($type == 'resource') {
	            $notifyParams .= '&r=' . base64_encode($returnUrl);
	        }
	        echo '<input type="hidden" name="notify_url" value="' . htmlspecialchars(JURI::root() . 'index.php?option=com_payperdownload&gateway=paypal&task=' . $task . $notifyParams) . '"/>';

    		if ($thankyouUrl) {
    			$returnParams = '';
			    if ($type == 'license') {
			        if (strstr($thankyouUrl, "?") === false) {
			            $returnParams = '?lid=' . (int)$license->license_id;
			        } else {
			            $returnParams = '&lid=' . (int)$license->license_id;
			        }
			    }
    			echo '<input type="hidden" name="return" value="' . htmlspecialchars($thankyouUrl . $returnParams) . '"/>';
    		}

			echo '<input type="hidden" name="no_note" value="1"/>';
			echo '<input type="hidden" name="no_shipping" value="1"/>';
			echo '<input type="hidden" name="rm" value="2"/>';

			$variables = array('classes' => trim($this->params->get('btn_classes', '')), 'image' => $this->params->get('btn_image', '0'), 'use_coupons' => ($type == 'license' ? $this->_getUseCoupons() : false));
	        echo $this->_loadTemplate('default.php', $variables);

	        echo '</form>';
	    }
	}

	public function onPaymentReceived($gateway, &$dealt, &$payed,
		&$user_id, &$license_id, &$resource_id, &$transactionId,
		&$response, &$validate_response, &$status,
	    &$amount, &$tax, &$fee, &$currency)
	{
		if ($gateway == "paypal") {

		    $dealt = true;
		    $payed = false;

		    $jinput = JFactory::getApplication()->input;

		    $receiver_email = $jinput->getString('receiver_email'); // cmd (default) removes the @
		    $business = $jinput->getString('business'); // cmd (default) removes the @

		    if (trim(strtoupper($this->params->get('paypal_account', ''))) !== trim(strtoupper($receiver_email)) ||
		        trim(strtoupper($this->params->get('paypal_account', ''))) !== trim(strtoupper($business)))
		    {
		        //$this->commitTransaction();
		        //PayPerDownloadPlusDebug::debug("Configured paypal account is different than target account");
		        return;
		    }

		    $req = 'cmd=_notify-validate';
		    $response = '';
		    foreach (/*$_POST*/$jinput->post->getArray() as $key => $value)
		    {
		        $response .= "(" . $key . " = " . $value . ")\r\n";
		        $req .= "&" . $key . "=" . urlencode($value);
		    }

		    //$payer_email = $jinput->getString('payer_email'); // cmd (default) removes the @

		    $transactionId = $jinput->getString('txn_id');
		    if(!$this->isTransactionPayed($transactionId))
		    {
		        $validate_response = $this->validatePayment($req, $jinput->getInt('test_ipn', 0), $this->params->get('use_simulator', 0)); // TODO test_ipn is test mode ?
		        $payed = (strpos($validate_response, "VERIFIED") !== false) ? 1 : 0;
		    }
		    else
		    {
		        //$this->commitTransaction();
		        //PayPerDownloadPlusDebug::debug("Invalid payment ". $transactionId);
		        return;
		    }

		    $validate_response = 'VERIFIED'; //$db->escape($validate_response); because contains header, not just 'VERIFIED'



		    // if r param in jinput, we know it's a resource



		    $user_id = $jinput->getInt('custom', 0); // license
		    // $download_id = $jinput->getInt('custom'); // resource

		    $license_id = $jinput->getInt('item_number');
		    //$resource_id = $jinput->getInt('item_number');

		    $status = '';

// 		    $resource_id_from_download_id = $this->getResourceFromDownloadLink($download_id);
// 		    if($resource_id != $resource_id_from_download_id)
// 		    {
// 		        $payed = 0;
// 		        $status = 'Invalid download id received;';
// 		    }

		    $payed_price = 0;
		    if($payed)
		    {
		        $payed_price = $jinput->getFloat('mc_gross', 0);
		        $currency_code = $jinput->getString('mc_currency');
		        $payed = $this->validateLicense($license_id, $payed_price, $currency_code, $user_id) ? 1 : 0;
		        //$payed = $this->validateResource($resource_id, $download_id, $payed_price, $currency_code) ? 1 : 0;
		        if($payed)
		        {
		            $status = trim(strtoupper($jinput->getString('payment_status')));
		            $payed = ($status == 'COMPLETED') ? 1 : 0;
		        }
		    }

		    $amount = $jinput->getFloat('mc_gross', 0);
		    $tax = $jinput->getFloat('tax', 0);
		    $fee = $jinput->getFloat('mc_fee', 0);
		    $currency = $jinput->getString('mc_currency');
		}
	}

	public function onGetPayerEmail($transactionId, &$payer_email)
	{
		$session = JFactory::getSession();
		$transactions = $session->get("trans", array());
		if (isset($transactions[$transactionId])) {
			$payer_email = $transactions[$transactionId]["payeremail"];
			unset($transactions[$transactionId]);
			$session->set("trans", $transactions);
		}
	}

	public function onGetDownloadLinkId($transactionId, &$download_id)
	{
		$session = JFactory::getSession();
		$transactions = $session->get("trans", array());
		if (isset($transactions[$transactionId])) {
			$download_id = $transactions[$transactionId]["download_id"];
		}
	}

	protected function isTransactionPayed($transactionId)
	{
	    //require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    //PayPerDownloadPlusDebug::debug("Is transaction payed for transaction id " . $transactionId);

	    $db = JFactory::getDBO();

	    $query = $db->getQuery(true);

	    $query->select('COUNT(*)');
	    $query->from($db->quoteName('#__payperdownloadplus_payments'));
	    $query->where($db->quoteName('txn_id') . ' = ' . $db->quote($transactionId));
	    $query->where($db->quoteName('payed') . ' = 1');

	    $db->setQuery($query);

	    try {
	        $count = $db->loadResult();
	        if ($count > 0) {
	            return true;
	        }
	    } catch (RuntimeException $e) {
	        //PayPerDownloadPlusDebug::debug("Failed database query - isTransactionPayed");
	    }

	    return false;
	}

	protected function validatePayment($req, $test = false, $use_simulator = false)
	{
	    //require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    //PayPerDownloadPlusDebug::debug("Validating payment");

	    $paypal = 'https://ipnpb.paypal.com/cgi-bin/webscr';
	    if ($test) {
	        if ($use_simulator) {
	            return "VERIFIED";
	        } else {
                $paypal = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
	        }
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


	    $response = curl_exec($ch);
	    $response_status = strval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

	    if ($response === false || $response_status == '0') {
	        $response = curl_errno($ch).' : '.curl_error($ch);
	    } else if (strpos($response_status, '200') === false) {
	        $response = 'Invalid response status: '.$response_status;
	    }

	    curl_close($ch);

	    //PayPerDownloadPlusDebug::debug("Response from validate : " . $response);

	    return $response;
	}

	function validateLicense($license_id, $payed_price, $currency_code, $user_id)
	{


	    return true;


// 	    $license = $this->getLicense($license_id);
// 	    $price = $this->getDiscountLicense($license, $user_id);
// 	    $license_discount = $this->getLicenseDiscount( $user_id, $license_id );
// 	    if($license_discount)
// 	    {
// 	        $price = $price * (1 - $license_discount->discount / 100.0);
// 	    }
// 	    $price = round($price, 2);
// 	    $result = ($license && (float)$payed_price - $price >= 0.00 && trim(strtoupper($license->currency_code)) == trim(strtoupper($currency_code)));
// 	    return $result;
	}

	protected function _getTaxPercentage()
	{
	    if (!isset(self::$tax_percentage)) {

    	    $db = JFactory::getDBO();

    	    $db->setQuery('SELECT tax_rate FROM #__payperdownloadplus_config');

    	    self::$tax_percentage = $db->loadResult();
	    }

	    return self::$tax_percentage;
	}

	protected function _getUseCoupons()
	{
	    if (!isset(self::$use_coupons)) {

	        $db = JFactory::getDBO();

	        $db->setQuery('SELECT use_discount_coupon FROM #__payperdownloadplus_config');

	        self::$use_coupons = $db->loadResult();
	    }

	    return self::$use_coupons;
	}

	protected function _loadTemplate($file = null, $variables = array())
	{
	    $template = JFactory::getApplication()->getTemplate();
	    $overridePath = JPATH_THEMES.'/'.$template.'/html/plg_payperdownloadplus_stripepay';

	    if (is_file($overridePath.'/'.$file)) {
	        $file = $overridePath.'/'.$file;
	    } else {
	        $file = __DIR__.'/tmpl/'.$file;
	    }

	    unset($template);
	    unset($overridePath);

	    if (!empty($variables)) {
	        foreach ($variables as $name => $value) {
	            $$name = $value;
	        }
	    }

	    unset($variables);
	    unset($name);
	    unset($value);
	    if (isset($this->this)) {
	        unset($this->this);
	    }

	    @ob_start();
	    include $file;
	    $html = ob_get_contents();
	    @ob_end_clean();

	    return $html;
	}

}
?>