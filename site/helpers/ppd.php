<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
// no direct access
defined('_JEXEC') or die;

class PPDAccess
{

	function isThereAvailableResource($resources, $article_id, $decreaseDownloadCount = true, $user_id = 0)
	{
		if($user_id == 0)
		{
			$user = JFactory::getUser();
			$user_id = $user->id;
		}
		foreach($resources as $resource)
		{
			if($resource->license_id)
			{
				if($user_id && $this->isLicenseValid($resource->license_id, $user_id, $article_id, $decreaseDownloadCount))
				{
					return true;
				}
			}
			else
			{
				if($this->isResourceAccessInSession($resource->resource_license_id, $article_id, $decreaseDownloadCount))
				{
					return true;
				}
			}
		}
		return false; //If there is no valid license and not valid download link then user has no access
	}

	function isResourceAccessInSession($req_resource, $article_id, $decreaseDownloadCount = true)
	{
		$session = JFactory::getSession();
		$resources = $session->get("ActiveResourceAccess", null);
		if($resources)
		{
			foreach($resources as $resource)
			{
				if($req_resource == $resource->resource_id &&
				  (is_null($resource->item) || $resource->item == $article_id)	&&
					$this->isDownloadCountValid($resource, $article_id, $decreaseDownloadCount))
				{
					return true;
				}
			}
		}
		return false;
	}

	function increaseDownloadCount($download_id, $article_id, $checkSession = true)
	{
		$increaseCount = true;
		if($checkSession)
		{
			$needle = $download_id . "-" . $article_id;
			$session = JFactory::getSession();
			$resources_hits = $session->get('ppd_resources_hits', array());
			if(array_search($needle, $resources_hits) === false)
			{
				$resources_hits []= $needle;
				$session->set('ppd_resources_hits', $resources_hits);
			}
			else
				$increaseCount = false;
		}
		if($increaseCount)
		{
			$db = JFactory::getDBO();
			$query = "UPDATE #__payperdownloadplus_download_links SET download_hits = download_hits + 1 WHERE download_id = " . (int)$download_id;
			$db->setQuery( $query );
			$db->query();
		}
	}

	function isDownloadCountValid($resource, $article_id, $decreaseDownloadCount = true)
	{
		if($decreaseDownloadCount)
			$this->increaseDownloadCount($resource->download_link, $article_id);
		$resource_id = (int)$resource->resource_id;
		$db = JFactory::getDBO();
		$query = "SELECT download_id FROM #__payperdownloadplus_download_links
			WHERE (link_max_downloads = 0 || download_hits <= link_max_downloads) AND download_id = " . (int)$resource->download_link;
		$db->setQuery( $query );
		$download_id = (int)$db->loadResult();
		return $download_id > 0;
	}


	/*
	* If license is valid for the specified user
	*/
	function isLicenseValid($license_id, $user_id, $article_id, $decreaseDownloadCount = true)
	{
		$db = JFactory::getDBO();
		$license_id = (int)$license_id;
		$db->setQuery( "SELECT level FROM #__payperdownloadplus_licenses WHERE license_id = " . $license_id);
		$level = (int) $db->loadResult();
		$user_id = (int)$user_id;
		$sqlHiherLevelCondition = "";
		if($level > 0) //If license level is zero then don't search for higher licenses
			$sqlHiherLevelCondition = "#__payperdownloadplus_licenses.level > $level OR";
		$query = "SELECT user_license_id
			FROM #__payperdownloadplus_users_licenses
			INNER JOIN #__payperdownloadplus_licenses
			ON #__payperdownloadplus_users_licenses.license_id = #__payperdownloadplus_licenses.license_id
			WHERE #__payperdownloadplus_users_licenses.user_id = $user_id AND
				(#__payperdownloadplus_users_licenses.expiration_date >= NOW() OR
				#__payperdownloadplus_users_licenses.expiration_date IS NULL) AND
				($sqlHiherLevelCondition
				#__payperdownloadplus_users_licenses.license_id = $license_id
				) AND
				#__payperdownloadplus_users_licenses.enabled = 1 AND
				(#__payperdownloadplus_users_licenses.license_max_downloads = 0 OR #__payperdownloadplus_users_licenses.download_hits < #__payperdownloadplus_users_licenses.license_max_downloads)
			LIMIT 1";
		$db->setQuery( $query );
		$user_license_id = (int)$db->loadResult();
		if($user_license_id)
		{
			if($decreaseDownloadCount)
			{
				$session = JFactory::getSession();
				$licensesHits = $session->get('licenses_hits', array());
				foreach($licensesHits as $licenseHit)
				{
					if($licenseHit->user_license_id == $user_license_id &&
						$licenseHit->item == $article_id)
					{
						$decreaseDownloadCount = false;
						break;
					}
				}
				if($decreaseDownloadCount)
				{
					$licenseHit = new stdClass();
					$licenseHit->item = $article_id;
					$licenseHit->user_license_id = $user_license_id;
					$licensesHits []= $licenseHit;
					$session->set('licenses_hits', $licensesHits);
					$query = "UPDATE #__payperdownloadplus_users_licenses SET download_hits = download_hits + 1 WHERE user_license_id = $user_license_id AND license_max_downloads > 0";
					$db->setQuery( $query );
					$db->query();
				}
			}
			return true;
		}
		else
			return false;
	}

	/*
	* If user is a member of one of privileged user groups who don't need to pay for any resource
	*/
	function isPrivilegedUser($user)
	{
		if(!$user->id)
			return false;
		$db = JFactory::getDBO();
		$query = "SELECT privilege_groups FROM #__payperdownloadplus_config";
		$db->setQuery( $query, 0, 1 );
		$privileged_groups = $db->loadResult();
		$groups = explode(',', $privileged_groups);

		foreach($user->groups as $group)
		{
			if(array_search($group, $groups) !== false)
				return true;
		}
		return false;
	}

	function getMenuItems()
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT config_id, payment_page_menuitem, thankyou_page_menuitem FROM #__payperdownloadplus_config", 0, 1);
		return $db->loadObject();
	}
}