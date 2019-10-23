<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

// no direct access
defined ( '_JEXEC' ) or die;

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
	    $adminpage = JFactory::getApplication()->input->getString('adminpage');

	    $config = JComponentHelper::getParams('com_payperdownload');
	    $debug = $config->get('debug', false);

	    JHtmlSidebar::addEntry(JText::_("COM_PAYPERDOWNLOAD_INFO"),
		    "index.php?option=com_payperdownload&view=info",
	        $adminpage == 'info' || $adminpage == "");

	    JHtmlSidebar::addEntry(JText::_("COM_PAYPERDOWNLOAD_RESOURCES"),
			"index.php?option=com_payperdownload&adminpage=resources&view=resources",
			$adminpage == "resources");
// 	    JHtmlSidebar::addEntry(JText::_("COM_PAYPERDOWNLOAD_LICENSES"),
// 			"index.php?option=com_payperdownload&adminpage=licenses&view=licenses",
// 			$adminpage == "licenses");

	    JHtmlSidebar::addEntry(JText::_("COM_PAYPERDOWNLOAD_LICENSES"),
	        "index.php?option=com_payperdownload&view=licenses",
	        $adminpage == "licenses");

	    JHtmlSidebar::addEntry(JText::_("COM_PAYPERDOWNLOAD_USERS_LICENCES"),
			"index.php?option=com_payperdownload&adminpage=users&view=users",
			$adminpage == "users");
	    JHtmlSidebar::addEntry(JText::_("COM_PAYPERDOWNLOAD_DOWNLOAD_LINKS"),
			"index.php?option=com_payperdownload&adminpage=downloads&view=downloads",
			$adminpage == "downloads");
	    JHtmlSidebar::addEntry(JText::_("COM_PAYPERDOWNLOAD_PAYMENTS"),
			"index.php?option=com_payperdownload&adminpage=orders&view=orders",
			$adminpage == "orders");
	    JHtmlSidebar::addEntry(JText::_("COM_PAYPERDOWNLOAD_COUPONS"),
			"index.php?option=com_payperdownload&adminpage=coupons&view=coupons",
			$adminpage == "coupons");
	    JHtmlSidebar::addEntry(JText::_("COM_PAYPERDOWNLOAD_BACKUP"),
			"index.php?option=com_payperdownload&adminpage=backup&view=backup",
		    $adminpage == "backup");

		//if ($debug) {
		    JHtmlSidebar::addEntry(JText::_('COM_PAYPERDOWNLOAD_DEBUG'),
		        "index.php?option=com_payperdownload&adminpage=debug&view=debug",
		        $adminpage == "debug");
		//}

		JHtmlSidebar::addEntry(JText::_("COM_PAYPERDOWNLOAD_CONFIGURATION"),
		    "index.php?option=com_payperdownload&adminpage=config&view=config",
		    $adminpage == "config");

		JToolBarHelper::preferences('com_payperdownload');
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

	/*
	 * duplicated in helpers/html/payperdownload.php for new views
	 */
	function showHints()
	{
	    $new_config = JComponentHelper::getParams('com_payperdownload');
	    $show_hints = $new_config->get('show_hints', 1);

	    if (!$show_hints) {
	        return;
	    }

		$db = JFactory::getDBO();
		$query = "SELECT config_id, paypalaccount, usepaypal, usepayplugin, paymentnotificationemail, notificationsubject, alphapoints FROM #__payperdownloadplus_config";
		$db->setQuery( $query, 0, 1 );

		try {
		    $config = $db->loadObject();
		} catch (RuntimeException $e) {
		    return;
		}

		$adminpage = JFactory::getApplication()->input->getString("view");

		$header = ''; //'<strong>' . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT")) . ': </strong>';

		$searchMoreHints = false;
		if($config == null || !preg_match("/^\s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*$/", $config->paypalaccount))
		{
		    $hint = htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_PAYPAL"));
			if($adminpage != "config")
			    $hint .= "<br/>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_CLICK_CONFIGURATION"));

			JFactory::getApplication()->enqueueMessage($header . $hint, JText::_("PAYPERDOWNLOADPLUS_HINT"));
		}
		else if(!$config->usepaypal && !$config->usepayplugin)
		{
		    $hint = htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_USE_PAYPAL"));
			if($adminpage != "config")
			    $hint .= "<br/>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_CLICK_CONFIGURATION"));

			JFactory::getApplication()->enqueueMessage($header . $hint, JText::_("PAYPERDOWNLOADPLUS_HINT"));
		}
		else if($config->alphapoints != 0)
		{
			if(!$this->isAlphaUserPointsInstalled())
			{
			    $hint = htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_AUP_SETUP"));
			    $hint .= "<br/><a href=\"http://www.alphaplug.com/\">" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_AUP_DOWNLOAD")) . "</a>";

				JFactory::getApplication()->enqueueMessage($header . $hint, JText::_("PAYPERDOWNLOADPLUS_HINT"));
			}
			else if(!$this->isAlphaRuleInstalled())
			{
			    $hint = htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_AUP_RULE_SETUP"));
			    $hint .= "<br/><a href=\"components/com_payperdownload/extensions/plugins/aup/aup_ppd.zip\">" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_AUP_RULE_DOWNLOAD")) . "</a>";

				JFactory::getApplication()->enqueueMessage($header . $hint, JText::_("PAYPERDOWNLOADPLUS_HINT"));
			}
			else if(!$this->isAlphaRuleEnable())
			{
			    $hint = htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_AUP_RULE_ENABLE"));
			    $hint .= "<br/><a href=\"index.php?option=com_alphauserpoints&task=rules\">" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_AUP_ENABLE")) . "</a>";

				JFactory::getApplication()->enqueueMessage($header . $hint, JText::_("PAYPERDOWNLOADPLUS_HINT"));
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
			    $hint = htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_RESOURCES_AND_LICENSES"));
				if($adminpage != "licenses" && $adminpage != "resources" && $adminpage != "")
				    $hint .= "<br/>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_CLICK_LICENSES_OR_RESOURCES"));

                JFactory::getApplication()->enqueueMessage($header . $hint, JText::_("PAYPERDOWNLOADPLUS_HINT"));
			}
			else if($resources == 0)
			{
			    $hint = htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_RESOURCES"));
				if($adminpage != "resources" && $adminpage != "")
				    $hint .= "<br/>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_CLICK_RESOURCES"));

				JFactory::getApplication()->enqueueMessage($header . $hint, JText::_("PAYPERDOWNLOADPLUS_HINT"));
			}
			else
			{
				$r = rand(1, 8);
				if($r == 8)
				{
					if(!preg_match("/^\s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*(;\s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*)*$/", $config->paymentnotificationemail))
					{
					    $hint = $config->paymentnotificationemail;
					    $hint .= htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_NOTIFICATION"));
					    $hint .= "<br/>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_CLICK_CONFIGURATION_NOTIFICATION"));

						JFactory::getApplication()->enqueueMessage($header . $hint, JText::_("PAYPERDOWNLOADPLUS_HINT"));
					}
					else if($config->notificationsubject == "")
					{
					    $hint = htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_NOTIFICATION_SUBJECT"));
					    $hint .= "<br/>" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_HINT_CLICK_CONFIGURATION_NOTIFICATION"));

						JFactory::getApplication()->enqueueMessage($header . $hint, JText::_("PAYPERDOWNLOADPLUS_HINT"));
					}
				}
				else
				{
					$text = "PAYPERDOWNLOADPLUS_RAND_HINT_" . $r;
					$text = JText::_($text);
					$hint = htmlspecialchars($text);

					JFactory::getApplication()->enqueueMessage($header . $hint, JText::_("PAYPERDOWNLOADPLUS_HINT"));
				}
			}
		}
	}
}
?>