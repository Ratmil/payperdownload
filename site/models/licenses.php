<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;

jimport( 'joomla.application.component.model' );

class PayPerDownloadModelLicenses extends JModelLegacy
{
	function getUserLicenses($start, $limit)
	{
	    $user = JFactory::getUser();

	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    if ($user) {
	        PayPerDownloadPlusDebug::debug("Get user licenses for user id " . $user->id);
	    } else {
	        PayPerDownloadPlusDebug::debug("Get user licenses for unknown user");
	    }

		if ($user)
		{
			$db = JFactory::getDBO();

			$query = $db->getQuery(true);

			$query->select($db->quoteName(array('licenses.license_id', 'users_licenses.user_id', 'users_licenses.expiration_date', 'licenses.member_title', 'licenses.license_name', 'licenses.level', 'licenses.expiration', 'users_licenses.license_max_downloads', 'users_licenses.download_hits')));
			$query->select($db->quoteName('users_licenses.item', 'download_id'));
			$query->from($db->quoteName('#__payperdownloadplus_users_licenses', 'users_licenses'));
			$query->innerJoin($db->quoteName('#__payperdownloadplus_licenses', 'licenses') . ' ON (' . $db->quoteName('users_licenses.license_id') . ' = ' . $db->quoteName('licenses.license_id') . ')');
            $query->where('(' . $db->quoteName('users_licenses.expiration_date') . ' IS NULL OR ' . $db->quoteName('users_licenses.expiration_date') . ' >= NOW())');
            $query->where('(' . $db->quoteName('users_licenses.license_max_downloads') . ' = 0 OR ' . $db->quoteName('users_licenses.download_hits') . ' < ' . $db->quoteName('users_licenses.license_max_downloads') . ')');
            $query->where($db->quoteName('users_licenses.user_id') . ' = ' . (int)$user->id);
            $query->where($db->quoteName('users_licenses.enabled') . ' = 1');
			$query->order($db->quoteName('licenses.level'));

			$db->setQuery($query, $start, $limit);

			$licenses = null;
			try {
			    $licenses = $db->loadObjectList();
			    foreach($licenses as $license)
			    {
			        $license->resources = $this->getLicenseResources($license);
			        $license->canRenew = $this->canLicenseBeRenewed($license->license_id);
			    }
			} catch (RuntimeException $e) {
			    PayPerDownloadPlusDebug::debug("Failed database query - getUserLicenses");
			}

			return $licenses;
		}

		return null;
	}

	function getTotalLicenses()
	{
		$user = JFactory::getUser();

		require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
		if ($user) {
		    PayPerDownloadPlusDebug::debug("Get total licenses for user id " . $user->id);
		} else {
		    PayPerDownloadPlusDebug::debug("Get total licenses for unknown user");
		}

		if($user)
		{
		    $db = JFactory::getDBO();

            $query = $db->getQuery(true);

            $query->select('COUNT(*)');
            $query->from($db->quoteName('#__payperdownloadplus_users_licenses', 'users_licenses'));
            $query->innerJoin($db->quoteName('#__payperdownloadplus_licenses', 'licenses') . ' ON (' . $db->quoteName('users_licenses.license_id') . ' = ' . $db->quoteName('licenses.license_id') . ')');
            $query->where('(' . $db->quoteName('users_licenses.expiration_date') . ' IS NULL OR ' . $db->quoteName('users_licenses.expiration_date') . ' >= NOW())');
            $query->where($db->quoteName('users_licenses.user_id') . ' = ' . (int)$user->id);
            $query->where($db->quoteName('users_licenses.enabled') . ' = 1');

			$db->setQuery($query);

			$count = 0;
			try {
			    $count = $db->loadResult();
			} catch (RuntimeException $e) {
			    PayPerDownloadPlusDebug::debug("Failed database query - getTotalLicenses");
			}

			return $count;
		}

		return 0;
	}

	function getLicenseResources($license)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    if ($license) {
	        PayPerDownloadPlusDebug::debug("Get license resources for license id " . $license->license_id);
	    } else {
	        PayPerDownloadPlusDebug::debug("Get license resources for unknown license");
	    }

		if($license)
		{
			$db = JFactory::getDBO();

			$query = $db->getQuery(true);

			$fields = $db->quoteName(array('resource_licenses.resource_license_id', 'resource_licenses.resource_name', 'resource_licenses.resource_description', 'resource_licenses.resource_type', 'resource_licenses.alternate_resource_description'));
			$fields[0] = 'DISTINCT ' . $fields[0]; // prepend distinct to the first quoted field

			$query->select($fields);
			$query->from($db->quoteName('#__payperdownloadplus_resource_licenses', 'resource_licenses'));
			$query->innerJoin($db->quoteName('#__payperdownloadplus_licenses', 'licenses') . ' ON (' . $db->quoteName('resource_licenses.license_id') . ' = ' . $db->quoteName('licenses.license_id') . ')');
			$query->where('(' . $db->quoteName('licenses.license_id') . ' = ' . (int)$license->license_id . ' OR (' . $db->quoteName('licenses.level') . ' > 0 AND ' . $db->quoteName('licenses.level') . ' < ' . (int)$license->level . ' ))');
			$query->where($db->quoteName('resource_licenses.enabled') . ' = 1');
			$query->order($db->quoteName('resource_licenses.resource_type') . ' DESC');
			$query->order($db->quoteName('resource_licenses.resource_name') . ' ASC');

			$db->setQuery($query);

			$resources = null;
			try {
			    $resources = $db->loadObjectList();
			} catch (RuntimeException $e) {
			    PayPerDownloadPlusDebug::debug("Failed database query - getLicenseResources");
			}

			return $resources;
		}

		return null;
	}

	function getRenovationOptions($licenseId)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get renewal options for license id " . $licenseId);

	    $db = JFactory::getDBO();

	    $query = $db->getQuery(true);

	    $query->select($db->quoteName('renew'));
	    $query->from($db->quoteName('#__payperdownloadplus_licenses'));
	    $query->where($db->quoteName('license_id') . ' = ' . (int)$licenseId);

	    $db->setQuery($query, 0, 1);

	    $this->renew = 0; // default
	    try {
	        $this->renew = $db->loadResult();
	    } catch (RuntimeException $e) {
	        PayPerDownloadPlusDebug::debug("Failed database query - getRenovationOptions");
	    }

	    return $this->renew;
	}

	function canLicenseBeRenewed($licenseId)
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Can license be renewed for id " . $licenseId);

		$renew = $this->getRenovationOptions($licenseId);
		if($renew == 0) // Can be renewed always
			return true;

		$user = JFactory::getUser();
		if($user)
		{
		    $db = JFactory::getDBO();

		    $query = $db->getQuery(true);

		    $query->select('COUNT(*)');
		    $query->from($db->quoteName('#__payperdownloadplus_users_licenses'));
		    $query->where($db->quoteName('user_id') . ' = ' . (int)$user->id);
		    $query->where($db->quoteName('license_id') . ' = ' . (int)$licenseId);
		    if($renew == 1) {
		        $query->where('(' . $db->quoteName('expiration_date') . ' IS NULL OR ' . $db->quoteName('expiration_date') . ' > NOW())');
		        $query->where('(' . $db->quoteName('license_max_downloads') . ' = 0 OR ' . $db->quoteName('download_hits') . ' < ' . $db->quoteName('license_max_downloads') . ')');
		    }

		    $db->setQuery($query);

		    $active = 0;
		    try {
		        $active = $db->loadResult();
		    } catch (RuntimeException $e) {
		        PayPerDownloadPlusDebug::debug("Failed database query - canLicenseBeRenewed");
		    }

		    return $active <= 0;
		}

		return true;
	}

}
?>