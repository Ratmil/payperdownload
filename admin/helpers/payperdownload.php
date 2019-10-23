<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');

/**
 * Pay Per Download helper.
 */
class PayParDownloadHelper
{
    /**
     * @var    JObject  A cache for the available actions.
     * @since  1.6
     */
    protected static $actions;

	/**
	 * Configure the Linkbar
	 *
	 * @param   string  $vName
	 * @return void
	 */
	public static function addSubmenu($vName = 'info')
	{
	    $config = JComponentHelper::getParams('com_payperdownload');
	    $debug = $config->get('debug', false);

		JHtmlSidebar::addEntry(
			JText::_('COM_PAYPERDOWNLOAD_INFO'),
			'index.php?option=com_payperdownload&amp;view=info',
		    $vName == 'info' || $vName == ''
		);

		JHtmlSidebar::addEntry(
		  JText::_ ('COM_PAYPERDOWNLOAD_RESOURCES'),
		    'index.php?option=com_payperdownload&amp;adminpage=resources&amp;view=resources', 'icon-grid-2',
		    $vName == 'resources'
		    );

// 		JHtmlSidebar::addEntry(
// 		  JText::_ ('COM_PAYPERDOWNLOAD_LICENSES'),
// 		    'index.php?option=com_payperdownload&amp;adminpage=licenses&amp;view=licenses', 'icon-grid',
// 		    $vName == 'licenses'
// 		    );

		JHtmlSidebar::addEntry(
		    JText::_ ('COM_PAYPERDOWNLOAD_LICENSES'),
		    'index.php?option=com_payperdownload&amp;view=licenses',
		    $vName == 'licenses'
		    );

        JHtmlSidebar::addEntry(
            JText::_ ('COM_PAYPERDOWNLOAD_USERS_LICENCES'),
            'index.php?option=com_payperdownload&amp;adminpage=users&amp;view=users', 'icon-users',
            $vName == 'users'
            );

        JHtmlSidebar::addEntry(
            JText::_ ('COM_PAYPERDOWNLOAD_DOWNLOAD_LINKS'),
            'index.php?option=com_payperdownload&amp;adminpage=downloads&amp;view=downloads', 'icon-link',
            $vName == 'downloads'
            );

        JHtmlSidebar::addEntry(
            JText::_ ('COM_PAYPERDOWNLOAD_PAYMENTS'),
            'index.php?option=com_payperdownload&amp;adminpage=orders&amp;view=orders', 'icon-credit-2',
            $vName == 'orders'
            );

        JHtmlSidebar::addEntry(
            JText::_ ('COM_PAYPERDOWNLOAD_COUPONS'),
            'index.php?option=com_payperdownload&amp;adminpage=coupons&amp;view=coupons', 'icon-scissors',
            $vName == 'coupons'
            );

        JHtmlSidebar::addEntry(
            JText::_ ('COM_PAYPERDOWNLOAD_BACKUP'),
            'index.php?option=com_payperdownload&amp;adminpage=backup&amp;view=backup', 'icon-database',
            $vName == 'backup'
            );

        //if ($debug) {
            JHtmlSidebar::addEntry(
                JText::_('COM_PAYPERDOWNLOAD_DEBUG'),
                'index.php?option=com_payperdownload&amp;adminpage=debug&amp;view=debug', 'icon-wrench',
                $vName == 'debug'
                );
        //}

        JHtmlSidebar::addEntry(
            JText::_ ('COM_PAYPERDOWNLOAD_CONFIGURATION'),
            'index.php?option=com_payperdownload&amp;adminpage=config&amp;view=config', 'icon-options',
            $vName == 'config'
            );
	}

	/**
	 * Gets a list of the actions that can be performed
	 *
	 * @return    JObject
	 */
	public static function getActions()
	{
// 		$user = JFactory::getUser();
// 		$result = new JObject;

// 		$assetName = 'com_payperdownload';

// 		$actions = array(
// 			'core.admin', 'core.manage'
// 		);

// 		foreach ($actions as $action) {
// 			$result->set($action, $user->authorise($action, $assetName));
// 		}

// 		return $result;

		if (empty(self::$actions))
		{
		    $user = JFactory::getUser();
		    self::$actions = new JObject;

		    $actions = JAccess::getActions('com_payperdownload');

		    foreach ($actions as $action)
		    {
		        self::$actions->set($action->name, $user->authorise($action->name, 'com_payperdownload'));
		    }
		}

		return self::$actions;
	}

}
