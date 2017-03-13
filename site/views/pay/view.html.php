<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.view');

class PayPerDownloadViewPay extends JViewLegacy
{
	function display($tpl = null)
	{
		$model = $this->getModel();
		if($model)
		{
			$user = JFactory::getUser();
			$lids = JRequest::getVar( 'lid', '' );
			$resource_id = JRequest::getInt('res', 0);
			$option = JRequest::getVar('option');
			JHtml::stylesheet('components/'. $option . '/css/frontend.css');
			$scriptPath = "components/$option/js/";
			JHtml::script($scriptPath . "pay.js", $scriptPath, false);

			$returnUrl = JRequest::getVar('returnurl');
			if($returnUrl)
				$returnUrl = base64_decode($returnUrl);
			else
				$returnUrl = $_SERVER['HTTP_REFERER'];
			$showMessage = JRequest::getInt('m', 0);
			$usePayPlugin = $model->getUsePayPluginConfig();
			$usePaypal = $model->getUsePaypal();
			$paymentInfo = $model->getPaymentInfo();
			$showResources = $model->getShowResources();
			$header = $model->getPaymentHeader();
			$alternate_header = $model->getAlternatePayLicenseHeader();
			$applyDiscount = $model->getUseDiscount();
			$askEmail = $model->getAskEmail();
			$useQuickRegister = $model->getUseQuickRegister();
			$useOsolCaptcha = $model->getUseOsolCaptcha();
			$alpha_integration = $model->getAlphaIntegration();
			$show_login = $model->getShowLogin();
			$tax_percent = $model->getTaxPercent();
			$download_id = 0;
			$resource = null;
			$resourceAccessParams = "";
			if($resource_id)
			{
				$itemId = JRequest::getInt('item', 0);
				$resource = $model->getResource($resource_id);
				if(!$resource->shared)
				{
					if($itemId == 0)
					{
						echo "Trying to pay a not shared resource as paid";
						exit;
					}
				}
				else
				{
					$itemId = 0;
				}
				$downloadLink = $model->createDownloadLink($resource_id, $itemId); 
				$download_id = $downloadLink->downloadId;
				$resourceAccessParams .= "&accesscode=" . urlencode($downloadLink->accessCode);
			}
			
			$thankyou_url = JURI::base() . "index.php?option=com_payperdownload&view=thankyou&return=" .
					urlencode(base64_encode($returnUrl)) . $resourceAccessParams;
					
			$menuitems = $this->getMenuItems();
			if($menuitems && $menuitems->thankyou_page_menuitem)
				$thankyou_url .= "&Itemid=" . (int)$menuitems->thankyou_page_menuitem;
				
			$hasLicenses = $lids != "";
			$lids = explode(",", $lids);
			$licenses = array();
			
			$min_level = -1;
			foreach($lids as $lid)
			{
				$license = $model->getLicense((int)$lid);
				if($license)
				{
					if($min_level < 0 || $license->level < $min_level)
						$min_level = $license->level;
					$license->resources = $model->getLicenseResources((int)$lid);
					if($applyDiscount && $user->id)
						$license->discount_price = $model->getDiscountLicense($license, $user->id);
					else
						$license->discount_price = $license->price;
					$licenses[] = $license;
				}
			}
			if($min_level > -1 && JRequest::getInt('h', 0))
			{
				$higherLicenses = $model->getHigherLicenses($min_level);
				foreach($higherLicenses as $lid)
				{
					if(array_search($lid, $lids) === false)
					{
						$lids []= $lid;
						$license = $model->getLicense((int)$lid);
						if($license)
						{
							$license->resources = $model->getLicenseResources((int)$lid);
							if($applyDiscount && $user->id)
								$license->discount_price = $model->getDiscountLicense($license, $user->id);
							else
								$license->discount_price = $license->price;
							$license->discount_price = round($license->discount_price, 2);
							$licenses[] = $license;
						}
					}
				}
			}
			$model->sortLicenses($licenses);
			
			$points = 0;
			if($alpha_integration == 2)
				$points = $model->getAUP();
			$protocol = $_SERVER['SERVER_PROTOCOL'];
			if(strtolower(substr($protocol, 0, 5)) == 'https')
				$thisUrl = "https://";
			else
				$thisUrl = "http://";
			$port = $_SERVER['SERVER_PORT'];
			if($port == '80')
				$port = '';
			else
				$port = ':' . $port;
			$thisUrl .= $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
			$thisUrl = base64_encode($thisUrl);
			$multipleLicenseView = $model->getMultipleLicensesView();
			$this->assignRef("usePayPlugin", $usePayPlugin);
			$this->assignRef("usePaypal", $usePaypal);
			$this->assignRef("applyDiscount", $applyDiscount);
			$this->assignRef("multipleLicenseView", $multipleLicenseView);
			$this->assignRef("paymentInfo", $paymentInfo);
			$this->assignRef("licenses", $licenses);
			$this->assignRef("user", $user);
			$this->assignRef("showMessage", $showMessage);
			$this->assignRef("showResources", $showResources);
			$this->assignRef("returnUrl", $returnUrl);
			$this->assignRef("thisUrl", $thisUrl);
			$this->assignRef("thankyouUrl", $thankyou_url);
			$this->assignRef("header", $header);
			$this->assignRef("alternate_header", $alternate_header);
			if($resource)
				$resource->download_id = $download_id;
			$this->assignRef("resourceObj", $resource);
			$this->assignRef("download_id", $download_id);
			$this->assignRef("askEmail", $askEmail);
			$this->assignRef("hasLicenses", $hasLicenses);
			$this->assignRef("show_login", $show_login);
			$this->assignRef("useQuickRegister", $useQuickRegister);
			$this->assignRef("useOsolCaptcha", $useOsolCaptcha);
			$this->assignRef("alpha_integration", $alpha_integration);
			$this->assignRef("points", $points);
			$this->assignRef("tax_percent", $tax_percent);
			
			$this->useDiscountCoupon = $model->getUseDiscountCoupon();
			parent::display($tpl);
		}
		else
			echo "model not found";
	}
	
	function getMenuItems()
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT config_id, payment_page_menuitem, thankyou_page_menuitem FROM #__payperdownloadplus_config", 0, 1);
		return $db->loadObject();
	}
	
	
}

?>