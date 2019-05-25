<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class com_payperdownloadInstallerScript
{
	/**
	 * Called before any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
    public function preflight($type, $parent)
	{
	    JFactory::getApplication()->enqueueMessage('Pay Per Download', 'message');

		return true;
	}

	/**
	 * Called after any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, $parent)
	{
	    // remove files

	    $files = array();
	    $files[] = '/components/com_payperdownload/helpers/version.php';
	    $files[] = '/administrator/components/com_payperdownload/install.payperdownload.php';
	    $files[] = '/administrator/components/com_payperdownload/html/about.html.php';
	    $files[] = '/administrator/components/com_payperdownload/extensions/plugins/payperdownloadplus/phocadownload/phocadownload.zip';
	    $files[] = '/administrator/components/com_payperdownload/extensions/plugins/editors-xtd/paytoreadmore/paytoreadmore.png';
	    $files[] = '/administrator/components/com_payperdownload/extensions/plugins/editors-xtd/paytoreadmore/paytoreadmore.css';
	    $files[] = '/administrator/components/com_payperdownload/extensions/plugins/editors-xtd/paytoreadmore/en-GB.plg_editors_xtd_paytoreadmore.ini';
	    $files[] = '/administrator/components/com_payperdownload/css/frontend.css';
	    $files[] = '/administrator/components/com_payperdownload/css/stat.css';
	    $files[] = '/administrator/components/com_payperdownload/controllers/about.php';
	    $files[] = '/administrator/components/com_payperdownload/images/icon-48-phocadownload.png';
	    $files[] = '/administrator/components/com_payperdownload/images/jd.png';
	    $files[] = '/administrator/components/com_payperdownload/extensions/plugins/payperdownloadplus/kunena/kunena.jpg';

	    $folders = array();

	    foreach ($files as $file) {
	        if (JFile::exists(JPATH_ROOT.$file) && !JFile::delete(JPATH_ROOT.$file)) {
	            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PAYPERDOWNLOAD_ERROR_DELETINGFILEFOLDER', $file), 'warning');
	        }
	    }

	    foreach ($folders as $folder) {
	        if (JFolder::exists(JPATH_ROOT.$folder) && !JFolder::delete(JPATH_ROOT.$folder)) {
	            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PAYPERDOWNLOAD_ERROR_DELETINGFILEFOLDER', $folder), 'warning');
	        }
	    }

		// enable plugins

	    //if ($type == 'install') {
	        $this->enableExtension('plugin', 'paytoreadmore', 'content');
	        $this->enableExtension('plugin', 'paytoreadmore', 'editors-xtd');
	        $this->enableExtension('plugin', 'content', 'payperdownloadplus');
	        $this->enableExtension('plugin', 'menuitem', 'payperdownloadplus');
	        $this->enableExtension('plugin', 'payperdownloadplus', 'system');
	    //}

	    return true;
	}

	/**
	 * Called on installation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install($parent)
	{
	    $manifest = $parent->get("manifest");
	    $parent = $parent->getParent();
	    $source = $parent->getPath("source");

	    // Install plugins
	    foreach($manifest->plugins->plugin as $plugin) {
	        $attributes = $plugin->attributes();

	        $plugin_name = JText::_($attributes['name']);

	        $plg = $source.'/'.$attributes['folder'].'/'.$attributes['plugin'];

	        $installer = new JInstaller();
	        if ($installer->install($plg)) {
	            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PAYPERDOWNLOAD_INSTALLED_SUCCESSFULLY', 'plg_'.$attributes['group'].'_'.$attributes['plugin']), 'message');
	        } else {
	            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PAYPERDOWNLOAD_INSTALLED_UNSUCCESSFULLY', 'plg_'.$attributes['group'].'_'.$attributes['plugin']), 'error');
	        }
	    }
	}

	/**
	 * Called on update
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update($parent)
	{
	    $manifest = $parent->get("manifest");
	    $parent = $parent->getParent();
	    $source = $parent->getPath("source");

	    // Install or update plugins
	    foreach($manifest->plugins->plugin as $plugin) {
	        $attributes = $plugin->attributes();

	        $plugin_name = JText::_($attributes['name']);

	        $plg = $source.'/'.$attributes['folder'].'/'.$attributes['plugin'];

	        $installer = new JInstaller();
	        if (JFolder::exists(JPATH_SITE.'/plugins/'.$attributes['group'].'/'.$attributes['plugin'])) {
    	        if ($installer->update($plg)) {
    	            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PAYPERDOWNLOAD_UPDATED_SUCCESSFULLY', 'plg_'.$attributes['group'].'_'.$attributes['plugin']), 'message');
    	        } else {
    	            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PAYPERDOWNLOAD_UPDATED_UNSUCCESSFULLY', 'plg_'.$attributes['group'].'_'.$attributes['plugin']), 'error');
    	        }
	        } else {
	            if ($installer->install($plg)) {
	                JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PAYPERDOWNLOAD_INSTALLED_SUCCESSFULLY', 'plg_'.$attributes['group'].'_'.$attributes['plugin']), 'message');
	            } else {
	                JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PAYPERDOWNLOAD_INSTALLED_UNSUCCESSFULLY', 'plg_'.$attributes['group'].'_'.$attributes['plugin']), 'error');
	            }
	        }
	    }
	}

	/**
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function uninstall($parent)
	{
	    //$manifest = $parent->get("manifest");
	    //$parent = $parent->getParent();

	    $db = JFactory::getDBO();

	    // Uninstall plugins
// 	    foreach($manifest->plugins->plugin as $plugin) {
// 	        $attributes = $plugin->attributes();

// 	        $plugin_name = JText::_($attributes['name']);

// 	        $query = 'SELECT extension_id FROM #__extensions WHERE type=\'plugin\' AND folder=\''.$attributes['group'].'\' AND element=\''.$attributes['plugin'].'\'';
// 	        $db->setQuery($query);
// 	        $pluginid = $db->loadResult();
// 	        if ($pluginid) {
// 	            $installer = new JInstaller();
// 	            if ($installer->uninstall('plugin', $pluginid)) {
// 	                JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PAYPERDOWNLOAD_UNINSTALLED', 'plg_'.$attributes['group'].'_'.$attributes['plugin']), 'message');
// 	            } else {
// 	                JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PAYPERDOWNLOAD_ERRORUNINSTALLING', 'plg_'.$attributes['group'].'_'.$attributes['plugin']), 'error');
// 	            }
// 	        } else {
// 	            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PAYPERDOWNLOAD_COULDNOTFIND', 'plg_'.$attributes['group'].'_'.$attributes['plugin']), 'error');
// 	        }
// 	    }

	    //JFactory::getApplication()->enqueueMessage('Pay Per Download uninstall', 'message');

	    $groupedplugins = array('system' => array('payperdownloadplus'));
	    $groupedplugins['content'] = array('paytoreadmore');
	    $groupedplugins['editors-xtd'] = array('paytoreadmore');
	    $groupedplugins['payperdownloadplus'] = array('content', 'phocadownload', 'k2', 'kunena', 'jdownload', 'menuitem');
	    $groupedplugins['user'] = array('referer');

	    // Uninstall plugins
	    foreach($groupedplugins as $group => $plugins) {
	        foreach($plugins as $plugin) {
	            //$query = 'SELECT extension_id FROM #__extensions WHERE type=\'plugin\' AND folder=\''.$group.'\' AND element=\''.$plugin.'\'';

	            $query = $db->getQuery(true);

	            $query->select($db->quoteName('extension_id'));
	            $query->from('#__extensions');
	            $query->where($db->quoteName('element') . ' = ' . $db->quote($plugin));
	            $query->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
	            $query->where($db->quoteName('folder') . ' = ' . $db->quote($group));

	            $db->setQuery($query);

	            try {
	                $pluginid = $db->loadResult();
	                if ($pluginid) {
	                    $installer = new JInstaller();
	                    if ($installer->uninstall('plugin', $pluginid)) {
	                        JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PAYPERDOWNLOAD_UNINSTALLED', 'plg_'.$group.'_'.$plugin), 'message');
	                    } else {
	                        JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PAYPERDOWNLOAD_ERRORUNINSTALLING', 'plg_'.$group.'_'.$plugin), 'error');
	                    }
	                } else {
	                    JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PAYPERDOWNLOAD_COULDNOTFIND', 'plg_'.$group.'_'.$plugin), 'error');
	                }
	            } catch (RuntimeException $e) {
	                JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PAYPERDOWNLOAD_ERRORUNINSTALLING', 'plg_'.$group.'_'.$plugin), 'error');
	            }
	        }
	    }
	}

	private function enableExtension($type, $element, $folder = '', $enable = true)
	{
	    $db = JFactory::getDBO();

	    $query = $db->getQuery(true);

	    $query->update($db->quoteName('#__extensions'));
	    if ($enable) {
	        $query->set($db->quoteName('enabled').' = 1');
	    } else {
	        $query->set($db->quoteName('enabled').' = 0');
	    }
	    $query->where($db->quoteName('type').' = '.$db->quote($type));
	    $query->where($db->quoteName('element').' = '.$db->quote($element));
	    if ($folder) {
	        $query->where($db->quoteName('folder').' = '.$db->quote($folder));
	    }

	    $db->setQuery($query);

	    try {
	        $db->execute();
	    } catch (RuntimeException $e) {
	        JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
	        return false;
	    }

	    return true;
	}
}
?>