<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );
class PayPerDownloadModelAlllicenses extends JModelLegacy
{ 
	var $config = null;

	function getAllLicenses($start, $limit)
	{
		$user = JFactory::getUser();
		$user_id = (int)$user->id;
		require_once(JPATH_COMPONENT.'/models/pay.php');
		$query = "SELECT 
				#__payperdownloadplus_licenses.license_id,
				#__payperdownloadplus_licenses.member_title,
				#__payperdownloadplus_licenses.license_name,
				#__payperdownloadplus_licenses.level,
				#__payperdownloadplus_licenses.price,
				#__payperdownloadplus_licenses.currency_code,
				#__payperdownloadplus_licenses.description,
				#__payperdownloadplus_licenses.expiration,
				#__payperdownloadplus_licenses.max_download
				FROM #__payperdownloadplus_licenses 
				WHERE #__payperdownloadplus_licenses.enabled = 1";
		$field1 = "level";
		$field2 = "price";
		$sort = $this->getLicensesSort();
		switch($sort)
		{
			case 1:
				$field1 = "level";
				$field2 = "license_name";
				break;
			case 2:
				$field1 = "level";
				$field2 = "price";
				break;
			case 3:
				$field1 = "level";
				$field2 = "expiration";
				break;
			case 4:
				$field1 = "license_name";
				$field2 = "level";
				break;
			case 5:
				$field1 = "price";
				$field2 = "level";
				break;
			case 6:
				$field1 = "expiration";
				$field2 = "level";
				break;
		}
		$query .= " ORDER BY #__payperdownloadplus_licenses.$field1, #__payperdownloadplus_licenses.$field2";
		$db = JFactory::getDBO();
		$db->setQuery($query, $start, $limit);
		$licenses = $db->loadObjectList();
		$paymodel = new PayPerDownloadModelPay();
		foreach($licenses as $license)
		{
			if($user_id)
				$license->discount_price = $paymodel->getDiscountLicense($license, $user_id);
			else
				$license->discount_price = $license->price;
			$license->resources = $this->getLicenseResources($license);
			$license->canRenew = $this->canLicenseBeRenewed($license->license_id);
		}
		return $licenses;
	}
	
	function getLicensesSort()
	{
		$db = JFactory::getDBO();
		$query = "SELECT license_sort FROM #__payperdownloadplus_config";
		$db->setQuery($query, 0, 1);
		$sort = $db->loadResult();
		if(!$sort)
			$sort = 2;
		return $sort;
	}
	
	function getTotalLicenses()
	{
		$db = JFactory::getDBO();
		$query = "SELECT COUNT(*)			
				FROM #__payperdownloadplus_licenses 
				WHERE #__payperdownloadplus_licenses.enabled = 1";
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	function getShowResources()
	{
		if(!$this->config)
		{
			$db = JFactory::getDBO();
			$db->setQuery("SELECT * FROM #__payperdownloadplus_config", 0, 1);
			$this->config = $db->loadObject();
		}
		return $this->config->showresources;
	}
	
	function getLicenseResources($license)
	{
		if($license)
		{
			$db = JFactory::getDBO();
			$level = (int)$license->level;
			$lid = (int)$license->license_id;
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
					#__payperdownloadplus_licenses.`level` < $level);";
			$db->setQuery( $query );
			$resources = $db->loadObjectList();
			return $resources;
		}
		return null;
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
}
?>