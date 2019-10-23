<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

class PayPerDownloadModelInfo extends JModelLegacy
{
	/**
	 * Get the plugins that extend the extension
	 *
	 * @access public
	 * @return
	 */
	public function getExtendedPlugins()
	{
	    $db = JFactory::getDbo();

	    $query = $db->getQuery(true)
    	    ->select(
    	        $db->quoteName(
    	            array(
    	                'element',
    	                'extension_id'
    	            ),
    	            array(
    	                'name',
    	                'id'
    	            )
	            )
	        )
	        ->from('#__extensions')
	        ->where('type = ' . $db->quote('plugin'))
	        ->where('folder = ' . $db->quote('payperdownloadplus'))
	        ->where('state IN (0,1)')
	        ->order('ordering');

        $db->setQuery($query);

        try {
            return $db->loadObjectList();
        } catch (\RuntimeException $e) {
            return array();
        }
	}

}
