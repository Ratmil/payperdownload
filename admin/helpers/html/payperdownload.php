<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Extended Utility class for the PayPerDownload component.
 */
class JHtmlPayPerDownload
{
    public static function enableStates()
    {
        $states = array(
            1 => array(
                'task'				=> 'publish',
                'text'				=> '',
                'active_title'		=> 'COM_PAYPERDOWNLOAD_TOOLBAR_ENABLE',
                'inactive_title'	=> '',
                'tip'				=> true,
                'active_class'		=> 'unpublish',
                'inactive_class'	=> 'unpublish'
            ),
            0 => array(
                'task'				=> 'unpublish',
                'text'				=> '',
                'active_title'		=> 'COM_PAYPERDOWNLOAD_TOOLBAR_DISABLE',
                'inactive_title'	=> '',
                'tip'				=> true,
                'active_class'		=> 'publish',
                'inactive_class'	=> 'publish'
            )
        );

        return $states;
    }

    public static function showHints()
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