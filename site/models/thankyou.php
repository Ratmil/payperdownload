<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );
class PayPerDownloadModelThankyou extends JModelLegacy
{ 
	function getThankyou()
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT config_id, thank_you_page FROM #__payperdownloadplus_config", 0, 1);
		$thank_you_content = null;
		$config = $db->loadObject();
		if(isset($config) && $config != null)
			$thank_you_content = $config->thank_you_page;
		if(!$thank_you_content)
		{
			$thank_you_content = "<span size=\"16\">" . JText::_("PAYPERDOWNLOADPLUS_THANK_YOU") . "</span>";
		}
		if(strpos($thank_you_content, "{continue}") === false)
		{
			$thank_you_content .= "<div class=\"front_thank_you_continue_url\"><a href=\"{continue}\">" . JText::_("PAYPERDOWNLOADPLUS_CONTINUE") . "</a></div>";
		}
		return $thank_you_content;
	}
	
	function getResourceThankyou($download_id)
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT config_id, thank_you_page_resource FROM #__payperdownloadplus_config", 0, 1);
		$thank_you_content = null;
		$config = $db->loadObject();
		if(isset($config) && $config != null)
			$thank_you_content = $config->thank_you_page_resource;
		if(!$thank_you_content)
		{
			$thank_you_content = "<span size=\"16\">" . JText::_("PAYPERDOWNLOADPLUS_RESOURCE_THANK_YOU") . "</span>";
		}
		if(strpos($thank_you_content, "{continue}") === false)
		{
			$thank_you_content .= "<div class=\"front_thank_you_continue_url\"><a href=\"{continue}\">" . JText::_("PAYPERDOWNLOADPLUS_CONTINUE") . "</a></div>";
		}
		$db->setQuery("SELECT download_link FROM #__payperdownloadplus_download_links WHERE download_id = " . (int)$download_id);
		$download_link = $db->loadResult();
		$thank_you_content = str_replace("{continue}", $download_link, $thank_you_content);
		return $thank_you_content;
	}
	
	function replaceContinueUrl($text)
	{
		$continueURL = $this->getContinueURL();
		if($continueURL)
			$text = str_replace("{continue}", htmlspecialchars($continueURL), $text);
		else
			$text = str_replace("{continue}", "", $text);
		return $text;
	}
	
	function getLicenseThankyouText($license_id)
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT license_id, thankyou_text FROM #__payperdownloadplus_licenses WHERE license_id = " . (int)$license_id);
		$thank_you_content = null;
		$config = $db->loadObject();
		if(isset($config) && $config != null)
			$thank_you_content = $config->thankyou_text;
		if($this->cleanHtml($thank_you_content) != '')
		{
			if(strpos($thank_you_content, "{continue}") === false)
			{
				$thank_you_content .= "<div class=\"front_thank_you_continue_url\"><a href=\"{continue}\">" . JText::_("PAYPERDOWNLOADPLUS_CONTINUE") . "</a></div>";
			}
		}
		return $thank_you_content;
	}
	
	function cleanHtml($text)
	{
		$text = preg_replace("/<\/?[a-zA-Z0-9]+[^>]*>/", "", $text);
		$text = preg_replace("/&[a-zA-Z]{1,6};/", "", $text);
		$text = preg_replace('/\s/', "", $text);
		return trim($text);
	}
	
	function getContinueURL()
	{
		$continueUrl = JRequest::getVar("return");
		return base64_decode($continueUrl);
	}
	
	function askEmail()
	{
		$db = JFactory::getDBO();
		$query = "SELECT askemail FROM #__payperdownloadplus_config";
		$db->setQuery( $query );
		return $db->loadResult();
	}
	
	function getDownloadLinkAccessCode($downloadId)
	{
		$accessCode = "";
		$downloadParams = $this->_getDownloadLinkAccessParameters($downloadId);
		if($downloadParams)
		{
			$accessCode = $downloadId . "-" . 
				sha1($downloadParams->secret_word . $downloadParams->random_value) . "-" .
				$downloadParams->random_value;
			if($downloadParams->item_id)
				$accessCode .= "-" . $downloadParams->item_id;
		}
		return $accessCode;
	}
	
	function _getDownloadLinkAccessParameters($downloadId)
	{
		$db = JFactory::getDBO();
		$query = "SELECT secret_word, random_value, item_id FROM #__payperdownloadplus_download_links
			WHERE download_id = " . (int)$downloadId;
		$db->setQuery( $query );
		return $db->loadObject();
	}
	
	function validateAccessCode($downloadId, $hash, $random)
	{
		$db = JFactory::getDBO();
		$e_random = $db->escape($random);
		$downloadId = (int)$downloadId;
		$query = "SELECT secret_word FROM #__payperdownloadplus_download_links 
				WHERE download_id = $downloadId";
		$db->setQuery( $query );
		$secret_word = $db->loadResult();
		return $hash == sha1($secret_word . $random);
	}
	
	function isDownloadPaid($downloadId)
	{
		$downloadId = (int)$downloadId;
		$db = JFactory::getDBO();
		$query = "SELECT payed FROM #__payperdownloadplus_download_links 
				WHERE download_id = $downloadId";
		$db->setQuery( $query );
		return $db->loadResult();
	}
}
?>