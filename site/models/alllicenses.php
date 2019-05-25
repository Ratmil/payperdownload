<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;

jimport( 'joomla.application.component.model' );

class PayPerDownloadModelAlllicenses extends JModelLegacy
{
	function getAllLicenses($start, $limit)
	{
	    require_once(JPATH_COMPONENT.'/models/pay.php');

	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get full licenses");

		$user = JFactory::getUser();

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('license_id', 'member_title', 'license_name', 'level', 'price', 'currency_code', 'description', 'expiration', 'max_download')));
		$query->from($db->quoteName('#__payperdownloadplus_licenses'));
		$query->where($db->quoteName('enabled') . ' = 1');

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
			case 7:
			    $field1 = "price";
			    $field2 = "license_name";
			    break;
			case 8:
			    $field1 = "license_name";
			    $field2 = "price";
			    break;
		}

		$query->order($db->quoteName($field1));
		$query->order($db->quoteName($field2));

		$db->setQuery($query, $start, $limit);

		$licenses = array();
		try {
		    $licenses = $db->loadObjectList();

		    $paymodel = new PayPerDownloadModelPay();
		    foreach($licenses as $license)
		    {
		        if($user->id)
		            $license->discount_price = $paymodel->getDiscountLicense($license, (int)$user->id);
		        else
		            $license->discount_price = $license->price;
		        $license->resources = $this->getLicenseResources($license);
		        $license->canRenew = $this->canLicenseBeRenewed($license->license_id);
		    }
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getAllLicenses");
		}

		return $licenses;
	}

	function getLicensesSort()
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get licenses sort");

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName('license_sort'));
		$query->from($db->quoteName('#__payperdownloadplus_config'));

		$db->setQuery($query, 0, 1);

		$sort = 2;
		try {
		    $sort = $db->loadResult();
		    if(!$sort)
		        $sort = 2;
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getLicensesSort");
		}

		return $sort;
	}

	function getTotalLicenses()
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get total licenses");

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select('COUNT(*)');
		$query->from($db->quoteName('#__payperdownloadplus_licenses'));
		$query->where($db->quoteName('enabled') . ' = 1');

		$db->setQuery($query);

		$count = 0;
		try {
		    $count = $db->loadResult();
		} catch (RuntimeException $e) {
		    PayPerDownloadPlusDebug::debug("Failed database query - getTotalLicenses");
		}

		return $count;
	}

	function getShowResources()
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get show resources");

	    $db = JFactory::getDBO();

	    $query = $db->getQuery(true);

	    $query->select($db->quoteName('showresources'));
	    $query->from($db->quoteName('#__payperdownloadplus_config'));

	    $db->setQuery($query, 0, 1);

	    $show_resources = false;
	    try {
	        $show_resources = $db->loadResult();
	    } catch (RuntimeException $e) {
	        PayPerDownloadPlusDebug::debug("Failed database query - getShowResources");
	    }

		return $show_resources;
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