<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;

jimport( 'joomla.application.component.model' );

class PayPerDownloadModelMembership extends JModelLegacy
{
	function getMembers($start, $limit)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get members");

	    $db = JFactory::getDBO();

	    $query = $db->getQuery(true);

	    $query->select($db->quoteName(array('licenses.license_id', 'users_licenses.user_id', 'licenses.member_title', 'users.name')));
	    $query->from($db->quoteName('#__payperdownloadplus_users_licenses', 'users_licenses'));
	    $query->innerJoin($db->quoteName('#__payperdownloadplus_licenses', 'licenses') . ' ON (' . $db->quoteName('users_licenses.license_id') . ' = ' . $db->quoteName('licenses.license_id') . ')');
	    $query->innerJoin($db->quoteName('#__users', 'users') . ' ON (' . $db->quoteName('users_licenses.user_id') . ' = ' . $db->quoteName('users.id') . ')');
	    $query->where($db->quoteName('users_licenses.expiration_date') . ' >= NOW()');
	    $query->where($db->quoteName('users_licenses.enabled') . ' = 1');
	    $query->where($db->quoteName('licenses.enabled') . ' = 1');
	    $query->order($db->quoteName('licenses.level') . ' DESC');

		$db->setQuery($query, $start, $limit);

		$members = null;
		try {
		    $members = $db->loadObjectList();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getMembers");
		}

		return $members;
	}

	function getTotalMembers()
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
    	PayPerDownloadPlusDebug::debug("Get total members");

    	$db = JFactory::getDBO();

    	$query = $db->getQuery(true);

    	$query->select('COUNT(*)');
    	$query->from($db->quoteName('#__payperdownloadplus_users_licenses', 'users_licenses'));
    	$query->innerJoin($db->quoteName('#__payperdownloadplus_licenses', 'licenses') . ' ON (' . $db->quoteName('users_licenses.license_id') . ' = ' . $db->quoteName('licenses.license_id') . ')');
    	$query->where($db->quoteName('users_licenses.expiration_date') . ' >= NOW()');
    	$query->where($db->quoteName('users_licenses.enabled') . ' = 1');
    	$query->where($db->quoteName('licenses.enabled') . ' = 1');

    	$db->setQuery($query);

    	$count = 0;
    	try {
    	    $count = $db->loadResult();
    	} catch (RuntimeException $e) {
    	    PayPerDownloadPlusDebug::debug("Failed database query - getTotalMembers");
    	}

    	return $count;
	}
}
?>