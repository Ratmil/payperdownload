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

class PayPerDownloadModelAffiliate extends JModelLegacy
{ 
	var $config = null;

	function getConfig()
	{
		if($this->config)
			return $this->config;
		return new ConfigObject();
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
	
	function getAffiliateProgram($affiliate_program)
	{
		$db = JFactory::getDBO();
		$query = "SELECT 
			#__payperdownloadplus_affiliates_programs.affiliate_program_id, 
			#__payperdownloadplus_affiliates_programs.program_name, 
			#__payperdownloadplus_affiliates_programs.program_description,
			#__payperdownloadplus_affiliates_programs.percent, 
			#__payperdownloadplus_licenses.license_id,
			#__payperdownloadplus_licenses.license_name, 
			#__payperdownloadplus_licenses.price,
			#__payperdownloadplus_licenses.currency_code
			FROM #__payperdownloadplus_affiliates_programs
			LEFT JOIN #__payperdownloadplus_licenses
			ON #__payperdownloadplus_affiliates_programs.license_id = #__payperdownloadplus_licenses.license_id
			WHERE #__payperdownloadplus_affiliates_programs.affiliate_program_id = " . (int)$affiliate_program;
		$db->setQuery( $query );
		return $db->loadObject();
	}
	
	function getAffiliateUserData($affiliate_program)
	{
		$user = JFactory::getUser();
		if(!$user->id)
			return null;
		$affiliate_program = (int)$affiliate_program;
		$db = JFactory::getDBO();
		$query = "SELECT affiliate_user_id, paypal_account, website, credit FROM #__payperdownloadplus_affiliates_users WHERE 
			affiliate_program_id = $affiliate_program AND user_id = " . (int)$user->id;
		$db->setQuery( $query );
		return $db->loadObject();
	}
	
	function updateAffiliateData(&$isUpdate)
	{
		$user = JFactory::getUser();
		if(!$user->id)
			return false;
		$user_id = (int)$user->id;
		$aff = JRequest::getInt('aff');
		$paypal_account = JRequest::getVar('paypal_account');
		$website = JRequest::getVar('website');
		$db = JFactory::getDBO();
		$paypal_account = $db->escape($paypal_account);
		$website = $db->escape($website);
		if($this->getAffiliateUserData($aff))
		{
			$query = "UPDATE #__payperdownloadplus_affiliates_users SET paypal_account = '$paypal_account', website = '$website' 
				WHERE user_id = $user_id AND affiliate_program_id = $aff";
			$isUpdate = true;
		}
		else
		{
			$query = "INSERT INTO #__payperdownloadplus_affiliates_users(user_id, affiliate_program_id, paypal_account, website, credit)
				VALUES($user_id, $aff, '$paypal_account', '$website', 0)";
			$isUpdate = false;
		}
		$db->setQuery($query);
		return $db->query();
	}
	
	function getBanners($affiliate_program)
	{
		$affiliate_program = (int)$affiliate_program;
		$db = JFactory::getDBO();
		$query = "SELECT affiliate_banner_id, banner_title, image FROM #__payperdownloadplus_affiliates_banners WHERE affiliate_program_id = " . 
			(int)$affiliate_program;
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	function getCredit($program_id)
	{
		$user = JFactory::getUser();
		if(!$user->id)
			return 0;
		$program_id = (int)$program_id;
		$db = JFactory::getDBO();
		$query = "SELECT SUM(credit) FROM #__payperdownloadplus_affiliates_users WHERE affiliate_program_id = $program_id AND user_id = " . (int)$user->id;
		$db->setQuery( $query );
		return $db->loadResult();
	}
	
	function getReferedCount()
	{
		$user = JFactory::getUser();
		if(!$user->id)
			return 0;
		$db = JFactory::getDBO();
		$query = "SELECT COUNT(*) FROM #__payperdownloadplus_affiliates_users_refered WHERE referer_user = " . (int)$user->id;
		$db->setQuery( $query );
		return $db->loadResult();
	}
}
?>