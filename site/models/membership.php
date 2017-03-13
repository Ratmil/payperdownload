<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );
class PayPerDownloadModelMembership extends JModelLegacy
{ 
	function getMembers($start, $limit)
	{
		$query = "SELECT 
			#__payperdownloadplus_licenses.license_id, 
			#__payperdownloadplus_users_licenses.user_id, 
			#__payperdownloadplus_licenses.member_title,
			#__users.name
			FROM #__payperdownloadplus_users_licenses 
			INNER JOIN #__payperdownloadplus_licenses 
			ON #__payperdownloadplus_users_licenses.license_id = #__payperdownloadplus_licenses.license_id
			INNER JOIN #__users ON #__payperdownloadplus_users_licenses.user_id = #__users.id
			WHERE expiration_date >= NOW() AND
			#__payperdownloadplus_users_licenses.enabled = 1 AND
			#__payperdownloadplus_licenses.enabled = 1
			ORDER BY #__payperdownloadplus_licenses.level DESC";
		$db = JFactory::getDBO();
		$db->setQuery($query, $start, $limit);
		return $db->loadObjectList();
	}
	 
	function getTotalMembers()
	{
		$query = "SELECT COUNT(*) 
			FROM #__payperdownloadplus_users_licenses 
			INNER JOIN #__payperdownloadplus_licenses 
			ON #__payperdownloadplus_users_licenses.license_id = #__payperdownloadplus_licenses.license_id
			WHERE expiration_date >= NOW() AND
			#__payperdownloadplus_users_licenses.enabled = 1 AND
			#__payperdownloadplus_licenses.enabled = 1";
		$db = JFactory::getDBO();
		$db->setQuery($query);
		return $db->loadResult();
	}
}
?>