<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;

jimport( 'joomla.application.component.model' );

class PayPerDownloadModelThankyou extends JModelLegacy
{
	function getThankyou()
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get thank you");

	    $db = JFactory::getDBO();

	    $query = $db->getQuery(true);

	    $query->select($db->quoteName(array('config_id', 'thank_you_page')));
	    $query->from($db->quoteName('#__payperdownloadplus_config'));

	    $db->setQuery($query, 0, 1);

		$thank_you_content = '';
		try {
		    $config = $db->loadObject();
		    if (isset($config) && $config != null)
		        $thank_you_content = $config->thank_you_page;
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getThankyou");
		}

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
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get resource thank you for download id " . $download_id);

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('config_id', 'thank_you_page_resource')));
		$query->from($db->quoteName('#__payperdownloadplus_config'));

		$db->setQuery($query, 0, 1);

		$thank_you_content = '';
		try {
		    $config = $db->loadObject();
		    if(isset($config) && $config != null)
		        $thank_you_content = $config->thank_you_page_resource;
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getResourceThankyou");
		}

		if(!$thank_you_content)
		{
			$thank_you_content = "<span size=\"16\">" . JText::_("PAYPERDOWNLOADPLUS_RESOURCE_THANK_YOU") . "</span>";
		}
		if(strpos($thank_you_content, "{continue}") === false)
		{
			$thank_you_content .= "<div class=\"front_thank_you_continue_url\"><a href=\"{continue}\">" . JText::_("PAYPERDOWNLOADPLUS_CONTINUE") . "</a></div>";
		}

		$query->clear();

		$query->select($db->quoteName('download_link'));
		$query->from($db->quoteName('#__payperdownloadplus_download_links'));
		$query->where($db->quoteName('download_id') . ' = ' . (int)$download_id);

		$db->setQuery($query);

		$download_link = '';
		try {
		    $download_link = $db->loadResult();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getResourceThankyou (2)");
		}

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
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get license thank you for license id " . $license_id);

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('license_id', 'thankyou_text')));
		$query->from($db->quoteName('#__payperdownloadplus_licenses'));
		$query->where($db->quoteName('license_id') . ' = ' . (int)$license_id);

		$db->setQuery($query);

		$thank_you_content = '';
		try {
		    $config = $db->loadObject();
		    if(isset($config) && $config != null)
		        $thank_you_content = $config->thankyou_text;
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getLicenseThankyouText");
		}

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
	    $continueUrl = JFactory::getApplication()->input->getBase64('return', '');
		return base64_decode($continueUrl);
	}

	function askEmail()
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Ask email");

	    $db = JFactory::getDBO();

	    $query = $db->getQuery(true);

	    $query->select($db->quoteName('askemail'));
	    $query->from($db->quoteName('#__payperdownloadplus_config'));

	    $db->setQuery($query, 0, 1);

	    $email = '';
	    try {
	        $email = $db->loadResult();
	    } catch (RuntimeException $e) {
	        PayPerDownloadPlusDebug::debug("Failed database query - askEmail");
	    }

	    return $email;
	}

	function getDownloadLinkAccessCode($download_id)
	{
		$accessCode = "";
		$downloadParams = $this->_getDownloadLinkAccessParameters($download_id);
		if($downloadParams)
		{
			$accessCode = $download_id . "-" . sha1($downloadParams->secret_word . $downloadParams->random_value) . "-" . $downloadParams->random_value;
			if($downloadParams->item_id)
				$accessCode .= "-" . $downloadParams->item_id;
		}
		return $accessCode;
	}

	function _getDownloadLinkAccessParameters($download_id)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get download link access parameters for download id " . $download_id);

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('secret_word', 'random_value', 'item_id')));
		$query->from($db->quoteName('#__payperdownloadplus_download_links'));
		$query->where($db->quoteName('download_id') . ' = ' . (int)$download_id);

		$db->setQuery($query);

		$object = null;
		try {
		    $object = $db->loadObject();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - _getDownloadLinkAccessParameters");
		}

		return $object;
	}

	function validateAccessCode($download_id, $hash, $random)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Validate access code for download id " . $download_id);

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName('secret_word'));
		$query->from($db->quoteName('#__payperdownloadplus_download_links'));
		$query->where($db->quoteName('download_id') . ' = ' . (int)$download_id);

		$db->setQuery($query);

		$secret_word = '';
		try {
		    $secret_word = $db->loadResult();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - validateAccessCode");
		}

		return $hash == sha1($secret_word . $random);
	}

	function isDownloadPaid($download_id)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Is download paid with download id " . $download_id);

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName('payed'));
		$query->from($db->quoteName('#__payperdownloadplus_download_links'));
		$query->where($db->quoteName('download_id') . ' = ' . (int)$download_id);

		$db->setQuery($query);

		$payed = false;
		try {
		    $payed = $db->loadResult();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - isDownloadPaid");
		}

		if ($payed) {
		    PayPerDownloadPlusDebug::debug("Download id " . $download_id . " has been paid");
		} else {
		    PayPerDownloadPlusDebug::debug("Download id " . $download_id . " has not been paid");
		}

		return $payed;
	}
}
?>