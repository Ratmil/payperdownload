<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );
class PayPerDownloadModelLicenses extends JModelLegacy
{ 
	function getUserLicenses($start, $limit)
	{
		$user = JFactory::getUser();
		if($user && $user->id)
		{
			$user_id = (int)$user->id;
			$query = "SELECT 
				#__payperdownloadplus_licenses.license_id, 
				#__payperdownloadplus_users_licenses.user_id, 
				#__payperdownloadplus_users_licenses.expiration_date,
				#__payperdownloadplus_licenses.member_title,
				#__payperdownloadplus_licenses.license_name,
				#__payperdownloadplus_licenses.level,
				#__payperdownloadplus_licenses.expiration,
				#__payperdownloadplus_users_licenses.license_max_downloads, 
				#__payperdownloadplus_users_licenses.download_hits
				FROM #__payperdownloadplus_users_licenses 
				INNER JOIN #__payperdownloadplus_licenses 
				ON #__payperdownloadplus_users_licenses.license_id = #__payperdownloadplus_licenses.license_id
				WHERE (expiration_date >= NOW() || expiration_date IS NULL) AND 
				(#__payperdownloadplus_users_licenses.license_max_downloads = 0 OR #__payperdownloadplus_users_licenses.download_hits < #__payperdownloadplus_users_licenses.license_max_downloads)  AND
				#__payperdownloadplus_users_licenses.user_id = $user_id AND
				#__payperdownloadplus_users_licenses.enabled = 1 
				ORDER BY #__payperdownloadplus_licenses.level";
			$db = JFactory::getDBO();
			$db->setQuery($query, $start, $limit);
			$licenses = $db->loadObjectList();
			foreach($licenses as $license)
			{
				$license->resources = $this->getLicenseResources($license);
				$license->canRenew = $this->canLicenseBeRenewed($license->license_id);
			}
			return $licenses;
		}
		else
			return null;
	}
	
	function getTotalLicenses()
	{
		$user = JFactory::getUser();
		if($user && $user->id)
		{
			$user_id = (int)$user->id;
			$db = JFactory::getDBO();
			$query = "SELECT COUNT(*) FROM #__payperdownloadplus_users_licenses 
				INNER JOIN #__payperdownloadplus_licenses 
				ON #__payperdownloadplus_users_licenses.license_id = #__payperdownloadplus_licenses.license_id
				WHERE (expiration_date >= NOW() || expiration_date IS NULL) AND #__payperdownloadplus_users_licenses.user_id = $user_id AND
				#__payperdownloadplus_users_licenses.enabled = 1";
			$db->setQuery($query);
			return $db->loadResult();
		}
		return 0;
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
					OR (
					#__payperdownloadplus_licenses.`level` > 0 AND
					#__payperdownloadplus_licenses.`level` < $level );";
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