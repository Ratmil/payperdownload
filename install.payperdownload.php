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
	 * Constructor
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function __constructor(JAdapterInstance $adapter)
	{
	}

	/**
	 * Called before any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($route, JAdapterInstance $adapter)
	{
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
	public function postflight($route, JAdapterInstance $adapter)
	{
		return true;
	}

	/**
	 * Called on installation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install(JAdapterInstance $adapter)
	{
		$this->updatePlugins();
		return true;
	}

	/**
	 * Called on update
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update(JAdapterInstance $adapter)
	{
		$this->updatePlugins();
		return true;
	}

	/**
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function uninstall(JAdapterInstance $adapter)
	{
		$this->uninstallPlugins();
		return true;
	}
	
	function uninstallPlugins()
	{
		$this->uninstall_plugin('payperdownloadplus');
		$this->uninstall_plugin('content', 'payperdownloadplus', null, array('content_plugin.js'), array('en-GB.plg_payperdownloadplus_content.ini'));
		$this->uninstall_plugin('phocadownload', 'payperdownloadplus', null, array('phoca_plugin.js'), array('en-GB.plg_payperdownloadplus_phocadownload.ini'));
		$this->uninstall_plugin('kunena', 'payperdownloadplus', null, array('kunena.jpg'), array('en-GB.plg_payperdownloadplus_kunena.ini'));
		$this->uninstall_plugin('jdownload', 'payperdownloadplus', null, array('jdownload_plugin.js'), array('en-GB.plg_payperdownloadplus_jdownload.ini'));
		$this->uninstall_plugin('referer', 'user');
		$this->uninstall_plugin('paytoreadmore', 'content', null, null, array('en-GB.plg_content_paytoreadmore.ini'));
		$this->uninstall_plugin('paytoreadmore', 'editors-xtd', null, null, array('en-GB.plg_editors_xtd_paytoreadmore.ini'));
	}
	
	function install_plugin($component, $element, $folder = 'system', $extra_folders = null, $extra_files = null, $language_files = null)
	{
		$this->uninstall_plugin($element, $folder, $extra_folders);
		$db = JFactory::getDBO();
		$name = $folder . ' - ' . $element;
		$e_name = $db->escape($name);
		$e_element = $db->escape($element);
		$e_folder = $db->escape($folder);
		$version = new JVersion;
		if($version->RELEASE >= "1.6")
		{
			$result = true;
		
			$dest_folder = JPATH_SITE.'/'.'plugins'.'/'.$folder.'/'.$element;
			$dest_file_php = JPATH_SITE.'/'.'plugins'.'/'.$folder.'/'.$element.'/'.$element.'.php';
			$dest_file_xml = JPATH_SITE.'/'.'plugins'.'/'.$folder.'/'.$element.'/'.$element.'.xml';

			if(!JFolder::exists($dest_folder))
			{
				JFolder::create($dest_folder);
			}
			
			if(is_array($extra_folders))
			{
				foreach($extra_folders as $extra_folder)
				{
					$new_folder = JPATH_ADMINISTRATOR.'/components/'.$component.'/extensions/plugins/'.$folder.'/'.$element.'/'.$extra_folder;
					if(!JFolder::move($new_folder, $dest_folder))
					{
						echo "Error copying folder ($new_folder) to ($dest_folder) folder<br/>";
						$result = false;
					}
				}
			}
			
			$file_php = JPATH_ADMINISTRATOR.'/components/'.$component.'/extensions/plugins/'.$folder.'/'.$element.'/'.$element.'.php';
			if(!JFile::exists($file_php) || !JFile::copy($file_php, $dest_file_php))
			{
				echo "Error copying file ($file_php) to ($dest_file_php)<br/>";
				$result = false;
			}
			$file_xml = JPATH_ADMINISTRATOR.'/components/'.$component.'/extensions/plugins/'.$folder.'/'.$element.'/'.$element.'.xml';
			if(!JFile::exists($file_xml) || !JFile::copy($file_xml, $dest_file_xml))
			{
				echo "Error copying file ($file_xml) to ($dest_file_xml)<br/>";
				$result = false;
			}
			
			if($extra_files)
			{
				foreach($extra_files as $extra_file)
				{
					$source_file = JPATH_ADMINISTRATOR.'/components/'.$component.'/extensions/plugins/'.$folder.'/'.$element.'/'.$extra_file;
					$dest_file = JPATH_SITE.'/plugins/'.$folder.'/'.$element.'/'.$extra_file;
					if(!JFile::exists($source_file) || !JFile::copy($source_file, $dest_file))
					{
						echo "Error copying file ($source_file) to ($dest_file)<br/>";
						$result = false;
					}
				}
			}
			
			if($language_files)
			{
				foreach($language_files as $language_file)
				{
					$dot_pos = strpos($language_file, ".");
					if($dot_pos !== false)
					{
						$language = substr($language_file, 0, $dot_pos);
						$source_file = JPATH_ADMINISTRATOR.'/components/'.$component.'/extensions/plugins/'.$folder.'/'.$element.'/'.$language_file;
						$dest_file = JPATH_ADMINISTRATOR.'/language/'.$language.'/'.$language_file;
						if(JFile::exists($source_file) && JFolder::exists(JPATH_ADMINISTRATOR.'/language/'.$language))
						{
							JFile::copy($source_file, $dest_file);
						}
					}
				}
			}
			
			$query = "INSERT INTO #__extensions(name, type, element, folder, enabled, access) 
				VALUES('$e_name', 'plugin', '$e_element', '$e_folder', 1, 1)";
			$db->setQuery($query);
			if(!$db->query())
			{
				echo "Error inserting plugin record<br/>";
				$result = false;
			}
			if(!$result)
				$this->uninstall_plugin($element, $folder );
			return false;
		}
	}
	
	function uninstall_plugin($element, $folder = 'system', $extra_folders = null, $extra_files = null, $language_files = null)
	{
		$db = JFactory::getDBO();
		$e_element = $db->escape($element);
		$e_folder = $db->escape($folder);
		$version = new JVersion;
		if($version->RELEASE >= "1.6")
		{
			$db = JFactory::getDBO();
			$db->setQuery("DELETE FROM #__extensions WHERE element='$e_element' AND folder='$e_folder' AND type='plugin'");
			$db->query();
			$dest_folder = JPATH_SITE.'/plugins/'.$folder.'/'.$element;
			if(JFolder::exists($dest_folder))
			{
				JFolder::delete($dest_folder);
			}
			if($language_files)
			{
				foreach($language_files as $language_file)
				{
					$dot_pos = strpos($language_file, ".");
					if($dot_pos !== false)
					{
						$language = substr($language_file, 0, $dot_pos);
						$dest_file = JPATH_ADMINISTRATOR.'/language/'.$language.'/'.$language_file;
						if(JFile::exists($dest_file))
						{
							JFile::delete($dest_file);
						}
					}
				}
			}
		}
	}
	
	function updatePlugins()
	{
		$this->uninstall_plugin('payperdownloadplus');
		$this->uninstall_plugin('content', 'payperdownloadplus', null, array('content_plugin.js'), array('en-GB.plg_payperdownloadplus_content.ini'));
		$this->uninstall_plugin('phocadownload', 'payperdownloadplus', null, array('phoca_plugin.js'), array('en-GB.plg_payperdownloadplus_phocadownload.ini'));
		$this->uninstall_plugin('kunena', 'payperdownloadplus', null, array('kunena.jpg'), array('en-GB.plg_payperdownloadplus_kunena.ini'));
		$this->uninstall_plugin('jdownload', 'payperdownloadplus', null, array('jdownload_plugin.js'), array('en-GB.plg_payperdownloadplus_jdownload.ini'));
		$this->uninstall_plugin('referer', 'user');
		$this->uninstall_plugin('paytoreadmore', 'content', null, null, array('en-GB.plg_content_paytoreadmore.ini'));
		$this->uninstall_plugin('paytoreadmore', 'editors-xtd', null, array('paytoreadmore.css', 'paytoreadmore.png'), array('en-GB.plg_editor-xtd_paytoreadmore.ini'));
		$this->install_plugin('com_payperdownload', 'payperdownloadplus', 'system', null, null, array('en-GB.plg_system_payperdownloadplus.ini', 'es-ES.plg_system_payperdownloadplus.ini'));
		$this->install_plugin('com_payperdownload', 'content', 'payperdownloadplus', null, array('content_plugin.js'), array('en-GB.plg_payperdownloadplus_content.ini'));
		$this->install_plugin('com_payperdownload', 'phocadownload', 'payperdownloadplus', null, array('phoca_plugin.js'), array('en-GB.plg_payperdownloadplus_phocadownload.ini'));
		$this->install_plugin('com_payperdownload', 'kunena', 'payperdownloadplus', null, array('kunena.jpg'), array('en-GB.plg_payperdownloadplus_kunena.ini'));
		$this->install_plugin('com_payperdownload', 'jdownload', 'payperdownloadplus', null, array('jdownload_plugin.js'), array('en-GB.plg_payperdownloadplus_jdownload.ini'));
		$this->install_plugin('com_payperdownload', 'referer', 'user');
		$this->install_plugin('com_payperdownload', 'paytoreadmore', 'content', null, null, array('en-GB.plg_content_paytoreadmore.ini'));
		$this->install_plugin('com_payperdownload', 'paytoreadmore', 'editors-xtd', null, array('paytoreadmore.css', 'paytoreadmore.png'), array('en-GB.plg_editors_xtd_paytoreadmore.ini'));
	}

}
?>