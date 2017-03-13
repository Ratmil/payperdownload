<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

define('PAYPERDOWNLOADPLUS_VERSION', '1.6');

jimport( 'joomla.plugin.plugin' );

/**
 * Joomla! PayperdownloadPlus plugin
 *
 * @package		Joomla
 * @subpackage	System
 */
 

 
class  plgSystemPayperDownloadPlus extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}
	
	/*
	Check affiliate referrer from request parameters.
	*/
	function checkReferer()
	{
		$affid = JRequest::getInt('ppd_affid', 0);
		if($affid)
		{
			$session = JFactory::getSession();
			$session->set("ppd_affid", $affid);
		}
	}
	
	/*
	onAfterRoute event.
	In this event it is checked if there is any protected resource and if user has
	access to it.
	If user has no access it is redirected to a payment form.
	*/
	function onAfterRoute()
	{
		$this->doMaintenance();
		$mainframe = JFactory::getApplication();
		if($mainframe->isAdmin())
			return;
		$this->checkReferer();
		$this->validateResourceAccessFromRequestParameters();
		$option = JRequest::getVar('option');
		$db = JFactory::getDBO();
		$escaped_option = $db->escape($option);
		$query = "SELECT resource_license_id, resource_id, resource_type, license_id, resource_params, shared 
			FROM #__payperdownloadplus_resource_licenses
			WHERE (resource_option_parameter = '$escaped_option' OR 
				resource_option_parameter = '') AND #__payperdownloadplus_resource_licenses.enabled = 1";
		$db->setQuery( $query );
		$resources = $db->loadObjectList();
		if(count($resources) > 0)
		{
			$shared = true;
			JPluginHelper::importPlugin("payperdownloadplus");
			$dispatcher	=JDispatcher::getInstance();
			$allowAccess = true;
			$dispatcher->trigger('onValidateAccess', array ($option, $resources, &$allowAccess, &$requiredLicenses, &$resourcesId));
			$requiresPayment = false;
			$user = JFactory::getUser();
			if(!$allowAccess)
			{
				$decreaseDownloadCount = false;
				$dispatcher->trigger('onCheckDecreaseDownloadCount', array ($option, $resources, $requiredLicenses, $resourcesId, &$decreaseDownloadCount));
				$checkSession = true;
				$dispatcher->trigger('onCheckSession', array ($option, $resources, $requiredLicenses, $resourcesId, &$checkSession));
				$deleteResourceFromSession = false;
				$dispatcher->trigger('onGetDeleteResourceFromSession', array ($option, $resources, $requiredLicenses, $resourcesId, &$deleteResourceFromSession));
				$requiresPayment = true;
				if(count($resourcesId) > 0)
					$shared = $this->isResourceShared((int)$resourcesId[0]);
				$item = $this->getItemForResource($option);
				if($user && $user->id)
				{
					if(
					  $this->isPrivilegedUser($user) ||
					  (count($requiredLicenses) > 0 && $this->isThereValidLicense($requiredLicenses, $decreaseDownloadCount, $item, $checkSession, $deleteResourceFromSession)) || 
					  (count($resourcesId) > 0 && $this->isTherePaidResource($resourcesId, $item, $decreaseDownloadCount, $shared, $checkSession, $deleteResourceFromSession)))
					{
						$requiresPayment = false;
					}
				}
				else
				{
					if((count($resourcesId) > 0 && $this->isTherePaidResource($resourcesId, $item, $decreaseDownloadCount, $shared, $checkSession, $deleteResourceFromSession)))
					{
						$requiresPayment = false;
					}
				}
			}
			if($requiresPayment)
			{
				$return = '';
				$dispatcher->trigger('getReturnPage', array($option, &$return));
				if($return)
				{
					if(strpos($return, "index.php") === 0)
					{
						$return = JURI::base() . $return;
					}
				}
				else
				{
					$protocol = $_SERVER['SERVER_PROTOCOL'];
					if(strtolower(substr($protocol, 0, 5)) == 'https')
						$return = "https://";
					else
						$return = "http://";
					$port = $_SERVER['SERVER_PORT'];
					if($port == '80')
						$port = '';
					else
						$port = ':' . $port;
					$return .= $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
				}
				$return = base64_encode($return);
				$licenses = implode( ',', $requiredLicenses );
				if($this->redirectToNoAccessPage())
				{
					$link = "index.php?option=com_payperdownload&view=noaccess";
				}
				else
				{
					$link = "index.php?option=com_payperdownload&view=pay&m=1&h=1&lid=" . $licenses;
					if(count($resourcesId) > 0)
					{
						$link .= "&res=" . (int)$resourcesId[0];
						if(!$shared && $item)
							$link .= "&item=" . urlencode($item);
					}
					$link .= "&returnurl=" . urlencode($return);
					$menuitems = $this->getMenuItems();
					if($menuitems && $menuitems->payment_page_menuitem)
					{
						$link .= "&Itemid=" . (int)$menuitems->payment_page_menuitem;
					}
				}
				$mainframe->redirect(JRoute::_($link, false));
				exit;
			}
		}
	}
	
	/*
	onAfterRender event
	It is called after page is created. This event checks if current page is a Kunena forum
	page and adds user's membership after user's name.
	*/
	function onAfterRender()
	{
		$this->showLicensesPrices();
		$this->showUserLicenses();
	}
	
	/*
	If the specified resource is shared returns the specific item that is about to be download.
	This item is id of download for Phocadownload or id of article for a content article, etc.
	*/
	function getItemForResource($option)
	{
		JPluginHelper::importPlugin("payperdownloadplus");
		$dispatcher	= JDispatcher::getInstance();
		$itemId = 0;
		$dispatcher->trigger('onGetItemId', array ($option, &$itemId));
		return $itemId;
	}
	
	/*
	Returns wheather user should be redirected to the "No access" page if he or her has
	no access to the resource
	*/
	function redirectToNoAccessPage()
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT usenoaccesspage FROM #__payperdownloadplus_config", 0, 1);
		return $db->loadResult();
	}
	
	/*
	Return menu items assigned to payment page and thank you page
	*/
	function getMenuItems()
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT config_id, payment_page_menuitem, thankyou_page_menuitem FROM #__payperdownloadplus_config", 0, 1);
		return $db->loadObject();
	}
	
	/*
	Returns licenses with their level given licenses ids.
	*/
	function getLicensesWithLevel($licenses)
	{
		if(count($licenses) > 0)
		{
			$lics = implode(", ", $licenses);
			$query = "SELECT license_id, level FROM #__payperdownloadplus_licenses WHERE 
				license_id IN ($lics)";
			$db = JFactory::getDBO();
			$db->setQuery($query);
			return $db->loadObjectList();
		}
		else
			return array();
	}
	
	/*
	Checks if current user owns one of the specified required licenses.
	Parameter decreaseDownloadCount specifies if the available download count is decreased.
	*/
	function isThereValidLicense($licenses, $decreaseDownloadCount, $item, $checkSession = true, $deleteResourceFromSession = false)
	{
		if(!is_array($licenses))
		{
			$licenses_ar = array();
			$licenses_ar[] = $licenses;
			$licenses = $licenses_ar;
		}
		$licenses = $this->getLicensesWithLevel($licenses);
		foreach($licenses as $license)
		{
			if($this->isLicenseValid($license, $decreaseDownloadCount, $item, $checkSession, $deleteResourceFromSession))
				return true;
		}
		return false;
	}
	
	/*
	Adds a menu item parameter (Itemid) to the specified url
	*/
	function addMenuItemParameter(&$url)
	{
		if(strpos($url, "Itemid=") !== false)
			return;
		$db = JFactory::getDBO();
		$query = "SELECT id FROM #__menu WHERE link = '" . $db->escape($url) . "'";
		$db->setQuery($query);
		$itemId = $db->loadResult();
		if(!$itemId)
			$itemId = JRequest::getVar('Itemid');
		if($itemId)
			$url .= "&Itemid=" . urlencode($itemId);
	}
	
	/*
	Returns component configuration
	*/
	function getConfiguration()
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT * FROM #__payperdownloadplus_config", 0, 1);
		return $db->loadObject();
	}
	
	/*
	Checks if some of the requested resources has been activated though url parameters
	**/
	function isTherePaidResource($resources, $item, $decreaseDownloadCount, 
		$shared = true, $checkSession = true,
		$deleteResourceFromSession = false)
	{
		foreach($resources as $resource)
		{
			$this->checkResourceForUser($resource, $item);
			if($this->isResourceAccessInSession(
				$resource, $item, $decreaseDownloadCount, 
				$checkSession, $shared, $deleteResourceFromSession))
				return true;
		}
		return false;
	}
	
	function checkResourceForUser($resource_id, $item_id)
	{
		$user_id = (int)JFactory::getUser()->id;
		if($user_id)
		{
			$resource_id = (int)$resource_id;
			$db = JFactory::getDBO();
			$query = "SELECT * FROM #__payperdownloadplus_download_links 
				WHERE resource_id = $resource_id AND user_id = $user_id AND
				(expiration_date IS NULL OR expiration_date >= NOW()) AND payed <> 0";
			if($item_id != null && $item_id != 0)
				$query .= " AND (item_id = '" . $db->escape($item_id) . "' OR item_id IS NULL)";
			else
				$query .= " AND item_id IS NULL";
			$db->setQuery($query);
			$downloadlink = $db->loadObject();
			if($downloadlink)
			{
				$this->addResourceAccessToSession($resource_id, $item_id, $downloadlink->download_id);
			}
		}
	}
	
	/*
	Checks if the specified resource has been activated though url parameters
	*/
	function isResourceAccessInSession(
		$req_resource, $item, 
		$decreaseDownloadCount, $checkSession = true, $shared = true,
		$deleteResourceFromSession = false)
	{
		$session = JFactory::getSession();
		$resources = $session->get("ActiveResourceAccess", null);
		if($resources)
		{
			foreach($resources as $key => $resource)
			{
				if($resource && $req_resource == $resource->resource_id &&
				  ($shared || $resource->item == $item) && 
				  $this->isDownloadCountValid($resource, $decreaseDownloadCount, $item, $checkSession))
				{
					if($deleteResourceFromSession)
					{
						$resources[$key] = null;
						$session->set("ActiveResourceAccess", $resources);
					}
					return true;
				}
			}
		}
		return false;
	}
	
	/*
	Returns if the specified resource is shared
	*/
	function isResourceShared($resource_id)
	{
		$db = JFactory::getDBO();
		$query = "SELECT shared FROM #__payperdownloadplus_resource_licenses 
			WHERE resource_license_id = " . (int)$resource_id;
		$db->setQuery( $query );
		$shared = $db->loadResult();
		return $shared;
	}
	
	/*
	Increase download count for download link
	*/
	function increaseDownloadCount($download_id, $item, $checkSession, $downloadCountValid)
	{
		$increaseCount = true;
		$result = false;
		if($checkSession)
		{
			$needle = $download_id . "-" . $item;
			$session = JFactory::getSession();
			$resources_hits = $session->get('ppd_resources_hits', array());
			if(array_search($needle, $resources_hits) === false)
			{
				$resources_hits []= $needle;
				$session->set('ppd_resources_hits', $resources_hits);
			}
			else
			{
				$increaseCount = false;
				$result = true;
			}
		}
		if($increaseCount && $downloadCountValid)
		{
			$db = JFactory::getDBO();
			$query = "UPDATE #__payperdownloadplus_download_links SET download_hits = download_hits + 1 WHERE download_id = " . (int)$download_id;
			$db->setQuery( $query );
			$db->query();
		}
		return $result;
	}
	
	/*
	Checks if user has not exceeded its allowed download count limit.
	*/
	function isDownloadCountValid($resource, $decreaseDownloadCount, $item, $checkSession = true)
	{
		$db = JFactory::getDBO();
		$resource_id = (int)$resource->resource_id;
		$query = "SELECT download_id,
			(link_max_downloads = 0 || download_hits < link_max_downloads) AS downloadcount_valid
			FROM #__payperdownloadplus_download_links 
			WHERE (link_max_downloads = 0 || download_hits <= link_max_downloads) AND download_id = " . (int)$resource->download_link;
		$db->setQuery( $query );
		$download = $db->loadObject();
		if($decreaseDownloadCount && $download != null)
		{
			if($this->increaseDownloadCount($resource->download_link, $item, $checkSession,
					$download->downloadcount_valid))
				$download->downloadcount_valid = true;		
		}
		return $download != null && $download->downloadcount_valid;
	}
	
	/*
	Adds resource to a session variable.
	This resource has been activated though url parameters.
	*/
	function addResourceAccessToSession($resource_id, $item, $download_link)
	{
		$session = JFactory::getSession();
		$resources = $session->get("ActiveResourceAccess", null);
		if(!$resources)
		{
			$resources = array();
		}
		foreach($resources as $resource)
		{
			if($resource->resource_id == $resource_id && 
				$resource->item == $item && 
				$resource->download_link == $download_link)
				return;
		}
		$resource = new stdClass();
		$resource->resource_id = $resource_id;
		$resource->item = $item;
		$resource->download_link = $download_link;
		$resources []= $resource;
		$session->set("ActiveResourceAccess", $resources);
	}
	
	/*
	Checks request parameters and searches for a download link activation
	*/
	function validateResourceAccessFromRequestParameters()
	{
		$access = JRequest::getVar('ppdaccess', '');
		if($access == '')
			return;
		// get item id if available in request
		$item_id = null;
		if(preg_match("/\\S+\\-\\S+\\-\\S+\\-\\S+/", $access))
			list($resource_id, $hash, $rand, $item_id) = explode("-", $access);
		else if(preg_match("/\\S+\\-\\S+\\-\\S+/", $access))
			list($resource_id, $hash, $rand) = explode("-", $access);
		else if(preg_match("/\\S+\\:\\S+\\:\\S+\\:\\S+/", $access))
			list($resource_id, $hash, $rand, $item_id) = explode(":", $access);
		else if(preg_match("/\\S+\\:\\S+\\:\\S+/", $access))
			list($resource_id, $hash, $rand) = explode(":", $access);
		
		$db = JFactory::getDBO();
		$resource_id = (int)$resource_id;
		$esc_rand = $db->escape($rand);
		$query = "SELECT * FROM #__payperdownloadplus_download_links 
			WHERE resource_id = $resource_id AND random_value = '$esc_rand' AND
			(expiration_date IS NULL OR expiration_date >= NOW()) AND payed <> 0";
		if($item_id != null)
			$query .= " AND (item_id = '" . $db->escape($item_id) . "' OR item_id IS NULL)";
		else
			$query .= " AND item_id IS NULL";
		$db->setQuery($query);
		$downloadlink = $db->loadObject();
		if($downloadlink)
		{
			$result = $hash == sha1($downloadlink->secret_word . $rand);
			if($result)
				$this->addResourceAccessToSession($resource_id, $item_id, $downloadlink->download_id);
		}
	}
	
	/*
	Checks wheather the current user owns the specified license. 
	If he or she does and decreaseDownloadCount parameter is true then 
	the allowed download count for this license is decreased.
	*/
	function isLicenseValid($license, $decreaseDownloadCount, $item, $checkSession = true, $deleteResourceFromSession = false)
	{
		$user = JFactory::getUser();
		if(!$user || $user->id == 0)
		{
			return false;
		}	
		$db = JFactory::getDBO();
		$license_id = (int)$license->license_id;
		$user_id = (int)$user->id;
		$level = (int)$license->level;
		$sqlHiherLevelCondition = "";
		if($level > 0) //If license level is zero then don't search for higher licenses
			$sqlHiherLevelCondition = "#__payperdownloadplus_licenses.level > $level OR";
		$query = "SELECT user_license_id,
			(#__payperdownloadplus_users_licenses.license_max_downloads = 0 OR #__payperdownloadplus_users_licenses.download_hits < #__payperdownloadplus_users_licenses.license_max_downloads) 
				AS download_count_valid
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
				(#__payperdownloadplus_users_licenses.license_max_downloads = 0 OR #__payperdownloadplus_users_licenses.download_hits <= #__payperdownloadplus_users_licenses.license_max_downloads)";
		$db->setQuery( $query );
		$user_licenses = $db->loadObjectList();
		$user_license = null;
		if($user_licenses && count($user_licenses) > 0)
		{
			foreach($user_licenses as $ul)
			{
				if($user_license == null || $ul->download_count_valid)
					$user_license = $ul;
			}
		}
		if($user_license)
		{
			if($decreaseDownloadCount)
			{
				if($checkSession)
				{
					$session = JFactory::getSession();
					$licensesHits = $session->get('licenses_hits', array());
					foreach($licensesHits as $key => $licenseHit)
					{
						if($licenseHit && $licenseHit->user_license_id == $user_license->user_license_id && 
							$licenseHit->item == $item)
						{
							$decreaseDownloadCount = false;
							$user_license->download_count_valid = true;
							if($deleteResourceFromSession)
							{
								$licensesHits[$key] = null;
								$session->set('licenses_hits', $licensesHits);
							}
							break;
						}
					}
					if($decreaseDownloadCount && $user_license->download_count_valid)
					{
						$licenseHit = new stdClass();
						$licenseHit->item = $item;
						$licenseHit->user_license_id = $user_license->user_license_id;
						$licensesHits []= $licenseHit;
						$session->set('licenses_hits', $licensesHits);
					}
				}
				if($decreaseDownloadCount && $user_license->download_count_valid)
				{
					$user_license_id = (int)$user_license->user_license_id;
					$query = "UPDATE #__payperdownloadplus_users_licenses SET download_hits = download_hits + 1 WHERE user_license_id = $user_license_id AND license_max_downloads > 0";
					$db->setQuery( $query );
					$db->query();
				}
			}
			return $user_license->download_count_valid;
		}
		else
			return false;
	}
	
	function showLicensesPrices()
	{
		JLoader::register('PayPerDownloadPrices', 
			JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/prices.php");
		$app = JFactory::getApplication();
		if($app->isAdmin())
			return;
		$body = JResponse::getBody();
		$pattern = "/\\{PPD_RESOURCE_PRICES\\:(\\d+(,\\d+)*)\\}/";
		if(preg_match($pattern, $body))
		{
			$body = preg_replace_callback($pattern, 
				array("PayPerDownloadPrices", "replaceResourcePrice"), $body);
			JResponse::setBody($body);
		}
	}
	
	/*
	Shows users owned valid licenses on Kunena forum
	*/
	function showUserLicenses()
	{
		JLoader::register('PayPerDownloadUserLicenses', 
			JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/userlic.php");
		$app = JFactory::getApplication();
		if($app->isAdmin())
			return;
		$setBody = false;
		$body = JResponse::getBody();
		$userlic = null;
		if(strpos($body, "{PPD_USER_LICENSES}") !== false)
		{
			$userlic = new PayPerDownloadUserLicenses();
			$html = $userlic->getUserLicensesHtml();
			$body = str_replace("{PPD_USER_LICENSES}", $html, $body);
			$setBody = true;
		}
		$option = JRequest::getVar('option');
		if($option == 'com_kunena')
		{
			$func = JRequest::getVar('func');
			$view = JRequest::getVar('view');
			if($func == 'view' || $view == 'topic')
			{
				if($this->isShowLicenseOnKunenaSet())
				{
					if(!$userlic)
						$userlic = new PayPerDownloadUserLicenses();
					$regExp = "/<li class=\"kpost-username\">\s*<a class=\"[^\"]*\"\s+href=\"([^\"]*)\"[^>]*>[^<]*<\/a>\s*<\/li>/";
					$body = preg_replace_callback($regExp, array($userlic, "getUserHighestLicenseHtml"), $body);
					$setBody = true;
				}
			}
		}
		if($setBody)
			JResponse::setBody($body);
	}
	
	/*
	Checks if showing user licenses on Kunena is enabled.
	*/
	function isShowLicenseOnKunenaSet()
	{
		$db = JFactory::getDBO();
		$query = "SELECT show_license_on_kunena FROM #__payperdownloadplus_config";
		$db->setQuery( $query, 0, 1 );
		return $db->loadResult();
	}
	
	/*
	Returns wheather current user belongs to one of the privileged user groups that don't
	need to pay to access protected resources.
	*/
	function isPrivilegedUser($user)
	{
		$db = JFactory::getDBO();
		$query = "SELECT privilege_groups FROM #__payperdownloadplus_config";
		$db->setQuery( $query, 0, 1 );
		$privileged_groups = $db->loadResult();
		$groups = explode(',', $privileged_groups);
		$version = new JVersion();
		if($version->RELEASE == "1.5")
			return array_search($user->gid, $groups) !== false;
		else
		{
			foreach($user->groups as $group)
			{
				if(array_search($group, $groups) !== false)
					return true;
			}
			return false;
		}
	}
	
	function doMaintenance()
	{
		$version = new JVersion();
		if($version->RELEASE < "1.6")
			return;
		$db = JFactory::getDBO();
		$query = "SELECT COUNT(*) FROM #__payperdownloadplus_last_time_check 
			WHERE DATE_ADD(last_time_check, INTERVAL 1 HOUR) > NOW()";
		$db->setQuery( $query );
		$count = $db->loadResult();
		if($count == 0)
		{
			$db->setQuery("START TRANSACTION");
			$db->query();
			$query = "SELECT COUNT(*) FROM #__payperdownloadplus_last_time_check";
			$db->setQuery( $query );
			$count = $db->loadResult();
			if($count == 0)
				$query = "INSERT INTO #__payperdownloadplus_last_time_check(last_time_check) VALUES(NOW())";
			else
				$query = "UPDATE #__payperdownloadplus_last_time_check SET last_time_check = NOW()";
			$db->setQuery( $query );
			$db->query();
			$this->unassignUserGroupsForExpiredLicenses();
			$db->setQuery("COMMIT");
			$db->query();
		}
		
	}
	
	function unassignUserGroupsForExpiredLicenses()
	{
		$db = JFactory::getDBO();
		$query = "SELECT user_license_id, assigned_user_group, user_id FROM #__payperdownloadplus_users_licenses 
			WHERE expiration_date < NOW() AND expiration_date IS NOT NULL AND assigned_user_group IS NOT NULL";
		$db->setQuery($query, 0, 5);
		$expired = $db->loadObjectList();
		$ids = array();
		foreach($expired as $expired_user_license)
		{
			$this->unassignUserGroupForLicense($expired_user_license);
			$ids []= $expired_user_license->user_license_id;
		}
		if(count($ids))
		{
			$ids = implode(",", $ids);
			$query = "UPDATE #__payperdownloadplus_users_licenses SET assigned_user_group = NULL 
				WHERE user_license_id IN ($ids)";
			$db->setQuery( $query );
			$db->query();
		}
	}
	
	function unassignUserGroupForLicense($user_license)
	{
		if($user_license->assigned_user_group)
		{
			$version = new JVersion();
			if($version->RELEASE >= "1.6")
			{
				$user = JFactory::getUser($user_license->user_id);
				$gid = array_search($user_license->assigned_user_group, $user->groups);
				if($gid !== false)
				{
					unset($user->groups[$gid]);
					$user->save();
				}
			}
		}
	}
}