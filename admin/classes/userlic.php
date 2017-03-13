<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class PayPerDownloadUserLicenses
{
	var $user_higher_license = array();

	function getUserLicensesHtml()
	{
		$user = JFactory::getUser();
		if($user && $user->id)
		{
			$lang = JFactory::getLanguage();
			$lang->load('plg_system_payperdownloadplus', JPATH_SITE.'/administrator');
			$licenses = $this->getLicenses($user->id);
			if($licenses)
			{
				$html = "";
				$html .= "<b><span class=\"ppd_licenses_title\">";
				$html .= JText::_("PAYPERDOWNLOADPLUS_SYSTEM_PLUGIN_LICENSES");
				$html .= "</span></b>";
				$html .= "<ul class=\"ppd_licenses_title\">";
				foreach($licenses as $license)
				{
					$html .= "<li>";
					$html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . htmlspecialchars($license->license_name);
					if($license->license_max_downloads > 0)
					{
						$html .= ",&nbsp;&nbsp;" . JText::_("PAYPERDOWNLOADPLUS_SYSTEM_PLUGIN_DOWNLOADS_LEFT") . 
							":&nbsp;" .  ($license->license_max_downloads - $license->download_hits);
					}
					if($license->expiration_date)
					{
						$date = new JDate($license->expiration_date);
						$format = JText::_("PAYPERDOWNLOADPLUS_SYSTEM_PLUGIN_DATE_FORMAT");
						if($format == "PAYPERDOWNLOADPLUS_SYSTEM_PLUGIN_DATE_FORMAT")
							$format = "l, F d, Y";
						$html .=  ",&nbsp;&nbsp;" . JText::_("PAYPERDOWNLOADPLUS_SYSTEM_PLUGIN_EXPIRES") . ":&nbsp;&nbsp;" . $date->format($format);
					}
					else
					{
						$html .=  ",&nbsp;&nbsp;" . JText::_("PAYPERDOWNLOADPLUS_SYSTEM_PLUGIN_EXPIRES") . ":&nbsp;&nbsp;" . JText::_("PAYPERDOWNLOADPLUS_SYSTEM_PLUGIN_EXPIRES_NEVER");
					}
					$html .= "</li>";
				}
				$html .= "</ul>";
				return $html;
			}
			else
				return JText::_("PAYPERDOWNLOADPLUS_SYSTEM_PLUGIN_NO_LICENSES");
		}
		else
			return "";
	}
	
	function getUserHighestLicenseHtml($matches)
	{
		$user_id = 0;
		$link = $matches[1];
		if( strpos($link, "forum/profile") !== false || strpos($link, "?option=com_kunena&") !== false)
		{
			$regexp = "/.*userid.(\d+).*/";
			if(preg_match($regexp, $link, $usermatch))
			{
				$user_id = (int)$usermatch[1];
			}
			else
			{
				$user = JFactory::getUser();
				$user_id = $user->id;
			}
		}
		else if( strpos($link, "/kunena/user/") != false)
		{
			$regexp = "/\/kunena\/user\/(\d+)\-/";
			if(preg_match($regexp, $link, $usermatch))
			{
				$user_id = (int)$usermatch[1];
			}
		}
		else if( strpos($link, "?option=com_alphauserpoints&") !== false)
		{
			$regexp = "/.*userid=([^&]*)/";
			if(preg_match($regexp, $link, $usermatch))
			{
				$referrer = $usermatch[1];
				$user_id = $this->getUserFromAUPReferrer($referrer);
			}
			else
			{
				$user = JFactory::getUser();
				$user_id = $user->id;
			}
		}
		else if( strpos($link, "alphauserpoints") !== false)
		{
			$regexp = "/\/alphauserpoints\/account\/account\/(.*)/";
			if(preg_match($regexp, $link, $usermatch))
			{
				$user_name = $usermatch[1];
				if(substr($user_name, -5) == ".html")
					$user_name = substr($user_name, 0, strlen($user_name) - 5);
				$user_id = (int)$this->getUserIdFromName($user_name);
			}
			else
			{
				$user = JFactory::getUser();
				$user_id = $user->id;
			}
		}
		else if( strpos($link, "/your-profile/userprofile") !== false )
		{
			$regexp = "/\/your-profile\/userprofile\/(.*)/";
			if(preg_match($regexp, $link, $usermatch))
			{
				$user_name = $usermatch[1];
				if(substr($user_name, -5) == ".html")
					$user_name = substr($user_name, 0, strlen($user_name) - 5);
				$user_id = (int)$this->getUserIdFromName($user_name);
			}
			else
			{
				$user = JFactory::getUser();
				$user_id = $user->id;
			}
		}
		else if( strpos($link, "?option=com_comprofiler&") !== false  )
		{
			$regexp = "/.*user=([^&]*)/";
			if(preg_match($regexp, $link, $usermatch))
			{
				$user_id = (int)$usermatch[1];
			}
			else
			{
				$user = JFactory::getUser();
				$user_id = $user->id;
			}
		}
		else if( strpos($link, "/forum/") !== false ) //JoomSef link
		{
			$regexp = "/\/forum\/([^\/]*)\/user/";
			if(preg_match($regexp, $link, $usermatch))
			{
				$user_name = $usermatch[1];
				$user_id = (int)$this->getUserIdFromName($user_name);
			}
		}
		
		$license = $this->getUserHighestLicense($user_id);
		if($license)
		{
			$imageHtml = "";
			if($license->license_image)
			{
				$url = JURI::root();
				$imageSrc = str_replace("\\", "/", $license->license_image);
				$imageHtml = "<img src=\"" . htmlspecialchars($url . $imageSrc) . "\"/>&nbsp;";
			}
			return $matches[0] . "<li class=\"kkpost-userposts\">" . $imageHtml . htmlspecialchars($license->member_title) . "</li>";
		}
		else
			return $matches[0];
	}
	
	function getUserFromAUPReferrer($referrer)
	{
		$db = JFactory::getDBO();
		$referrer = $db->escape( trim( $referrer) );
		$query = "SELECT userid FROM #__alpha_userpoints WHERE referreid = '$referrer'";
		$db->setQuery( $query );
		$userid = (int)$db->loadResult();
		return $userid;
	}
	
	function getUserIdFromName($user_name)
	{
		$db = JFactory::getDBO();
		$user_name = $db->escape( trim( $user_name) );
		$query = "SELECT id FROM #__users WHERE username = '$user_name'";
		$db->setQuery( $query );
		$userid = (int)$db->loadResult();
		return $userid;
	}
	
	function getUserHighestLicense($user_id)
	{
		$user_id = (int)$user_id;
		if(isset($user_higher_license[$user_id]))
			return $user_higher_license[$user_id];
		if($user_id)
		{
			$query = "SELECT 
				#__payperdownloadplus_licenses.license_id, 
				#__payperdownloadplus_users_licenses.user_id, 
				#__payperdownloadplus_users_licenses.expiration_date,
				#__payperdownloadplus_licenses.member_title,
				#__payperdownloadplus_licenses.license_name,
				#__payperdownloadplus_licenses.level,
				#__payperdownloadplus_licenses.expiration,
				#__payperdownloadplus_licenses.license_image,
				#__payperdownloadplus_users_licenses.license_max_downloads, 
				#__payperdownloadplus_users_licenses.download_hits
				FROM #__payperdownloadplus_users_licenses 
				INNER JOIN #__payperdownloadplus_licenses 
				ON #__payperdownloadplus_users_licenses.license_id = #__payperdownloadplus_licenses.license_id
				WHERE (expiration_date >= NOW() || expiration_date IS NULL) AND 
				(#__payperdownloadplus_users_licenses.license_max_downloads = 0 OR #__payperdownloadplus_users_licenses.download_hits < #__payperdownloadplus_users_licenses.license_max_downloads)  AND
				#__payperdownloadplus_users_licenses.user_id = $user_id AND
				#__payperdownloadplus_users_licenses.enabled = 1 
				ORDER BY #__payperdownloadplus_licenses.level DESC";
			$db = JFactory::getDBO();
			$db->setQuery($query, 0, 1);
			$license = $db->loadObject();
			$user_higher_license[$user_id] = $license;
			return $license;
		}
		else
			return null;
	}
	
	function getLicenses($user_id)
	{
		$user_id = (int)$user_id;
		if($user_id)
		{
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
				ORDER BY #__payperdownloadplus_licenses.level DESC";
			$db = JFactory::getDBO();
			$db->setQuery($query, 0, 20);
			$licenses = $db->loadObjectList();
			return $licenses;
		}
		else
			return null;
	}
}

?>