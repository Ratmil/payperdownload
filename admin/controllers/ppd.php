<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or
die( 'Direct Access to this location is not allowed.' );
/**
 * @author		Ratmil 
 * http://www.ratmilwebsolutions.com
*/

require_once(JPATH_COMPONENT.'/controllers/base.php');

/************************************************************
Base class to handle common task like add, edit, save, delete, etc
*************************************************************/
class PPDForm extends BaseForm
{
	/**
	Class constructor
	*/
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	Renders submenu.
	*/
	function renderSubmenu()
	{
		$version = new JVersion;
		//if($version->RELEASE >= "1.6")
		{
			$adminpage = JRequest::getVar('adminpage');
			JSubMenuHelper::addEntry(JText::_("COM_PAYPERDOWNLOAD_RESOURCES"), 
				"index.php?option=com_payperdownload&adminpage=resources",
				$adminpage == "resources" || $adminpage == "");
			JSubMenuHelper::addEntry(JText::_("COM_PAYPERDOWNLOAD_LICENSES"), 
				"index.php?option=com_payperdownload&adminpage=licenses",
				$adminpage == "licenses");
			JSubMenuHelper::addEntry(JText::_("COM_PAYPERDOWNLOAD_USERS_LICENCES"), 
				"index.php?option=com_payperdownload&adminpage=users",
				$adminpage == "users");
			JSubMenuHelper::addEntry(JText::_("COM_PAYPERDOWNLOAD_DOWNLOAD_LINKS"), 
				"index.php?option=com_payperdownload&adminpage=downloads",
				$adminpage == "downloads");
			JSubMenuHelper::addEntry(JText::_("COM_PAYPERDOWNLOAD_PAYMENTS"), 
				"index.php?option=com_payperdownload&adminpage=orders",
				$adminpage == "orders");
			JSubMenuHelper::addEntry(JText::_("COM_PAYPERDOWNLOAD_COUPONS"), 
				"index.php?option=com_payperdownload&adminpage=coupons",
				$adminpage == "coupons");
			JSubMenuHelper::addEntry(JText::_("COM_PAYPERDOWNLOAD_CONFIGURATION"), 
				"index.php?option=com_payperdownload&adminpage=config",
				$adminpage == "config");
			JSubMenuHelper::addEntry(JText::_("COM_PAYPERDOWNLOAD_BACKUP"), 
				"index.php?option=com_payperdownload&adminpage=backup",
				$adminpage == "backup");
			JSubMenuHelper::addEntry(JText::_("COM_PAYPERDOWNLOAD_ABOUT"), 
				"index.php?option=com_payperdownload&adminpage=about",
				$adminpage == "about");
		}
	}
	
	//Render the admin form and handles the supplied task.
	function showForm($task, $option)
	{
		if($task != "ajaxcall")
		{
			$this->showHints();
			parent::showForm($task, $option);
		}
	}
	
	function isAlphaUserPointsInstalled()
	{
		jimport('joomla.filesystem.folder');
		return JFolder::exists(JPATH_ROOT . '/administrator/components/com_alphauserpoints');
	}
	
	function isAlphaRuleEnable()
	{
		$db = JFactory::getDBO();
		$query = "SELECT published FROM #__alpha_userpoints_rules WHERE plugin_function = 'plgaup_payperdownload_buy'";
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	function isAlphaRuleInstalled()
	{
		$db = JFactory::getDBO();
		$query = "SELECT COUNT(*) FROM #__alpha_userpoints_rules WHERE plugin_function = 'plgaup_payperdownload_buy'";
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	function showHints()
	{	
		$adminpage = JRequest::getVar("adminpage");
		$db = JFactory::getDBO();
		$query = "SELECT config_id, show_hints, paypalaccount, usepaypal, usepayplugin, paymentnotificationemail, notificationsubject, alphapoints FROM #__payperdownloadplus_config";
		$db->setQuery( $query, 0, 1 );
		$config = $db->loadObject();
		if($config != null && !$config->show_hints)
			return;
		$searchMoreHints = false;
		if($config == null || !preg_match("/^\s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*$/", $config->paypalaccount))
		{
			echo "<strong>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT")) . "</strong>";
			echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_PAYPAL"));
			if($adminpage != "config")
				echo "<br/>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_CLICK_CONFIGURATION"));
		}
		else if(!$config->usepaypal && !$config->usepayplugin)
		{
			echo "<strong>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT")) . "</strong>";
			echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_USE_PAYPAL"));
			if($adminpage != "config")
				echo "<br/>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_CLICK_CONFIGURATION"));
		}
		else if($config->alphapoints != 0)
		{
			if(!$this->isAlphaUserPointsInstalled())
			{
				echo "<strong>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT")) . "</strong>";
				echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_AUP_SETUP"));
				echo "<br/><a href=\"http://www.alphaplug.com/\">" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_AUP_DOWNLOAD")) . "</a>";
			}
			else if(!$this->isAlphaRuleInstalled())
			{
				echo "<strong>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT")) . "</strong>";
				echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_AUP_RULE_SETUP"));
				echo "<br/><a href=\"components/com_payperdownload/extensions/plugins/aup/aup_ppd.zip\">" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_AUP_RULE_DOWNLOAD")) . "</a>";
			}
			else if(!$this->isAlphaRuleEnable())
			{
				echo "<strong>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT")) . "</strong>";
				echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_AUP_RULE_ENABLE"));
				echo "<br/><a href=\"index.php?option=com_alphauserpoints&task=rules\">" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_AUP_ENABLE")) . "</a>";
			}
			else
			{
				$searchMoreHints = true;
			}
			
		}
		else
			$searchMoreHints = true;
		if($searchMoreHints)
		{
			$query = "SELECT COUNT(*) FROM #__payperdownloadplus_resource_licenses";
			$db->setQuery( $query );
			$resources = (int)$db->loadResult();
			$query = "SELECT COUNT(*) FROM #__payperdownloadplus_licenses";
			$db->setQuery( $query );
			$licenses = (int)$db->loadResult();
			if($licenses == 0 && $resources == 0)
			{
				echo "<strong>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT")) . "</strong>";
				echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_RESOURCES_AND_LICENSES"));
				if($adminpage != "licenses" && $adminpage != "resources" && $adminpage != "")
					echo "<br/>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_CLICK_LICENSES_OR_RESOURCES"));
			}
			else if($resources == 0)
			{
				echo "<strong>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT")) . "</strong>";
				echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_RESOURCES"));
				if($adminpage != "resources" && $adminpage != "")
					echo "<br/>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_CLICK_RESOURCES"));
			}
			else 
			{
				$r = rand(1, 8);
				if($r == 8)
				{
					if(!preg_match("/^\s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*(;\s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*)*$/", $config->paymentnotificationemail))
					{
						echo $config->paymentnotificationemail;
						echo "<strong>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT")) . "</strong>";
						echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_NOTIFICATION"));
						echo "<br/>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_CLICK_CONFIGURATION_NOTIFICATION"));
					}
					else if($config->notificationsubject == "")
					{
						echo "<strong>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT")) . "</strong>";
						echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_NOTIFICATION_SUBJECT"));
						echo "<br/>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_CLICK_CONFIGURATION_NOTIFICATION"));
					}
				}
				else
				{
					
					$text = "PAYPERDOWNLOADPLUS_RAND_HINT_" . $r;
					$text = JText::_($text);
					echo "<strong>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT")) . "</strong>";
					echo htmlspecialchars($text);
				}
			}
		}
	}
}
?>