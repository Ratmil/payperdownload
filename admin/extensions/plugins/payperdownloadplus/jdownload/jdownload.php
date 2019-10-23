<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die;

// import the JPlugin class
jimport('joomla.event.plugin');

class plgPayperDownloadPlusJDownload extends JPlugin
{
    protected $autoloadLanguage = true;
    static $jdownloads_version;

    static function getJDownloadsVersion()
    {
        if (!isset(self::$jdownloads_version)) {
            self::$jdownloads_version = strval(simplexml_load_file(JPATH_ADMINISTRATOR . '/components/com_jdownloads/jdownloads.xml')->version);
        }

        return self::$jdownloads_version;
    }

    static function isNewVersion()
    {
        return version_compare(self::getJDownloadsVersion(), '3.9.0', 'ge');
    }

	function onIsActive(&$plugins)
	{
		jimport('joomla.filesystem.folder');
		if(!JFolder::exists(JPATH_ROOT . '/administrator/components/com_jdownloads'))
			return false;
		$component = JComponentHelper::getComponent('com_jdownloads', true);
		if($component->enabled)
		{
		    $plugins[] = array("name" => "JDownloads", "description" => JText::_("PAYPERDOWNLOADPLUS_JDOWNLOAD_PLUGIN_JDOWNLOADSFILE"), "image" => "plugins/payperdownloadplus/jdownload/jdownload.png");
		}
	}

	function reorderCats(&$cats_ordered, $cats, $parent_id, $depth)
	{
		$count = count($cats);
		for($i = 0; $i < $count; $i++)
		{
			$cat = $cats[$i];
			if($cat->parent_id == $parent_id)
			{
				$cat->depth = $depth;
				$cats_ordered[] = $cat;
				$this->reorderCats($cats_ordered, $cats, $cat->cat_id, $depth + 1);
			}
		}
	}

	function getJDownloadCategories()
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName('id', 'cat_id'));
		$query->select($db->quoteName('title', 'cat_title'));
		$query->select($db->quoteName('parent_id'));
		$query->from($db->quoteName('#__jdownloads_categories'));

		$db->setQuery($query);

		$cats_ordered = array();

		try {
		    $cats = $db->loadObjectList();
		    $this->reorderCats($cats_ordered, $cats, 0, 0);
		} catch (RuntimeException $e) {
		    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $cats_ordered;
	}

	function getFiles($cat_id)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		if (self::isNewVersion()) {
		    $query->select($db->quoteName('id'));
		    $query->select($db->quoteName('title'));
		    $query->from($db->quoteName('#__jdownloads_files'));
		    $query->where($db->quoteName('catid') . ' = ' . (int)$cat_id);
		} else {
    		$query->select($db->quoteName('file_id', 'id'));
    		$query->select($db->quoteName('file_title', 'title'));
    		$query->from($db->quoteName('#__jdownloads_files'));
    		$query->where($db->quoteName('cat_id') . ' = ' . (int)$cat_id);
		}

		$db->setQuery($query);

		$files = array();

		try {
		    $files = $db->loadObjectList();
		} catch (RuntimeException $e) {
		    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $files;
	}

	function onRenderConfig($pluginName, $resource)
	{
		if($pluginName == "JDownloads")
		{
		    $category_id = '';
		    $files = array();
			if($resource)
			{
				$file_id = $resource->resource_id;
				$category_id = $resource->resource_params;
				if($category_id)
					$files = $this->getFiles($category_id);
			}
			$uri = JURI::root();
			$scriptPath = "administrator/components/com_payperdownload/js/";
			JHTML::script($scriptPath . 'ajax_source.js', false);

			$plugin_path = "plugins/payperdownloadplus/jdownload/";

			$scriptPath = $uri . $plugin_path;
			JHTML::script($scriptPath . 'jdownload_plugin.js', false);
			$cats = $this->getJDownloadCategories();
			?>
			<tr>
			<td align="left" class="key"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_JDOWNLOAD_PLUGIN_CATEGORY"));?></td>
			<td>
			<select id="jdownload_category" name="jdownload_category" onchange="jdownload_plugin_category_change();">
			<option value="0"><?php echo JText::_("PAYPERDOWNLOADPLUS_JDOWNLOAD_PLUGIN_ALL_CATEGORIES");?></option>
			<?php
			foreach($cats as $cat)
			{
				$space = '';
				for($i = 0; $i < $cat->depth; $i++)
					$space .= '&nbsp;&nbsp;&nbsp;&nbsp;';
				$selected = $cat->cat_id == $category_id ? "selected":"";
				echo "<option value=\"" . htmlspecialchars($cat->cat_id) . "\" $selected>" . $space . htmlspecialchars($cat->cat_title) . "</option>";
			}
			?>
			</select>
			</td>
			</tr>
			<tr>
			<td align="left" class="key"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_JDOWNLOAD_PLUGIN_DOWNLOAD"));?></td>
			<td>
			<select id="jdownload_file" name="jdownload_file">
			<option value="0"><?php echo JText::_("PAYPERDOWNLOADPLUS_JDOWNLOAD_PLUGIN_ALL_DOWNLOADS");?></option>
			<?php
			if($files)
			foreach($files as $file)
			{
				$selected = $file->id == $file_id ? "selected":"";
				echo "<option value=\"" . htmlspecialchars($file->id) . "\" $selected>" . htmlspecialchars($file->title) . "</option>";
			}
			?>
			</select>
			</td>
			</tr>
			<?php
		}
	}

	function onGetSaveData(&$resourceId, $pluginName, &$resourceName, &$resourceParams, &$optionParameter, &$resourceDesc)
	{
		if($pluginName == "JDownloads")
		{
		    $jinput = JFactory::getApplication()->input;

			$optionParameter = "com_jdownloads";
			$resourceId = $jinput->getInt('jdownload_file');
			$categoryId = $jinput->getInt('jdownload_category');

			$resourceDesc = JText::_("PAYPERDOWNLOADPLUS_JDOWNLOAD_PLUGIN_ALL_DOWNLOADS");
			if($resourceId)
			{
			    $db = JFactory::getDBO();

			    $query = $db->getQuery(true);

			    if (self::isNewVersion()) {
			        $query->select($db->quoteName('id'));
			        $query->select($db->quoteName('title'));
			        $query->from($db->quoteName('#__jdownloads_files'));
			        $query->where($db->quoteName('id') . ' = ' . $resourceId);
			    } else {
    			    $query->select($db->quoteName('file_id'));
    			    $query->select($db->quoteName('file_title', 'title'));
    			    $query->from($db->quoteName('#__jdownloads_files'));
    			    $query->where($db->quoteName('file_id') . ' = ' . $resourceId);
			    }

			    $db->setQuery($query);

			    try {
			        $resource = $db->loadObject();
			        if($resource)
			            $resourceName = $resource->title;
			    } catch (RuntimeException $e) {
			        JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			    }

				$resourceDesc = JText::_("PAYPERDOWNLOADPLUS_JDOWNLOAD_PLUGIN_DOWNLOAD");
			}
			else if($categoryId)
			{
				$resourceId = -1;

				$db = JFactory::getDBO();

				$query = $db->getQuery(true);

				$query->select($db->quoteName('id', 'cat_id'));
				$query->select($db->quoteName('title', 'cat_title'));
				$query->from($db->quoteName('#__jdownloads_categories'));
				$query->where($db->quoteName('id') . ' = ' . $categoryId);

				$db->setQuery($query);

				try {
				    $resource = $db->loadObject();
				    if($resource)
				        $resourceName = $resource->title;
				} catch (RuntimeException $e) {
				    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
				}

				$resourceDesc = JText::_("PAYPERDOWNLOADPLUS_JDOWNLOAD_PLUGIN_CATEGORY");
			}
			else
			{
				$resourceId = -1;
			}
			$resourceName = JText::_("PAYPERDOWNLOADPLUS_JDOWNLOAD_PLUGIN_ALL_DOWNLOADS");

			$resourceParams = $categoryId;
		}
	}

	function getParentCategories($id)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		if (self::isNewVersion()) {
		    $query->select($db->quoteName('catid'));
		    $query->from($db->quoteName('#__jdownloads_files'));
		    $query->where($db->quoteName('id') . ' = ' . (int)$id);
		} else {
		    $query->select($db->quoteName('cat_id'));
		    $query->from($db->quoteName('#__jdownloads_files'));
		    $query->where($db->quoteName('file_id') . ' = ' . (int)$id);
		}

		$db->setQuery($query);

		$parentCategories = array();

		try {
		    $category = $db->loadResult();

		    while($category)
		    {
		        $parentCategories[] = $category;

		        $query->clear();

		        $query->select($db->quoteName('parent_id'));
		        $query->from($db->quoteName('#__jdownloads_categories'));
		        $query->where($db->quoteName('id') . ' = ' . (int)$category);

		        $db->setQuery($query);

		        $category = $db->loadResult();
		    }

		} catch (RuntimeException $e) {
		    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $parentCategories;
	}

	function onValidateAccess($option, $resources, &$allowAccess, &$requiredLicenses, &$resourcesId)
	{
		if($option == 'com_jdownloads')
		{
		    $jinput = JFactory::getApplication()->input;

		    $task = $jinput->get('task');
		    $view = $jinput->get('view');
			if($task == 'finish' || $view == 'finish' || $task == 'download.send')
			{
				$requiredLicenses = array();
				$resourcesId = array();
				$downloads = array();
				$download = $jinput->getInt('id', 0);
				$multiple_downloads = false;
				if($download)
				{
					$downloads[] = $download;
				}
				else
				{
					$multiple_downloads = true;
					$downloads = explode(',', $jinput->getRaw('list'));
				}
				if(count($downloads) == 0)
					return;

				foreach($resources as $resource)
				{
				    if ($resource->resource_type === 'JDownloads') {
    					if(array_search($resource->resource_id, $downloads) !== false)
    					{
    						if($resource->license_id)
    						{
    							if(array_search($resource->license_id, $requiredLicenses) === false)
    								$requiredLicenses[] = $resource->license_id;
    						}
    						else
    						{
    							if(array_search($resource->resource_license_id, $resourcesId) === false)
    								$resourcesId[] = $resource->resource_license_id;
    						}
    						$allowAccess = false;
    					}
				    }
				}

				if(count($requiredLicenses) == 0 && count($resourcesId) == 0)
				{
					$allParentCategories = array();
					foreach($downloads as $d)
					{
						$parentCategories = $this->getParentCategories($d);
						$allParentCategories = array_merge($allParentCategories, $parentCategories);
					}
					foreach($resources as $resource)
					{
					    if ($resource->resource_type === 'JDownloads') {
    						if($resource->resource_id == -1 && array_search($resource->resource_params, $allParentCategories) !== false)
    						{
    							if($resource->license_id)
    							{
    								if(array_search($resource->license_id, $requiredLicenses) === false)
    									$requiredLicenses[] = $resource->license_id;
    							}
    							else
    							{
    								if(array_search($resource->resource_license_id, $resourcesId) === false)
    									$resourcesId[] = $resource->resource_license_id;
    							}
    							$allowAccess = false;
    						}
					    }
					}
				}

				if(count($requiredLicenses) == 0 && count($resourcesId) == 0)
				{
					foreach($resources as $resource)
					{
					    if ($resource->resource_type === 'JDownloads') {
    						if($resource->resource_id == -1 && $resource->resource_params == 0)
    						{
    							if($resource->license_id)
    							{
    								if(array_search($resource->license_id, $requiredLicenses) === false)
    									$requiredLicenses[] = $resource->license_id;
    							}
    							else
    							{
    								if(array_search($resource->resource_license_id, $resourcesId) === false)
    									$resourcesId[] = $resource->resource_license_id;
    							}
    							$allowAccess = false;
    						}
					    }
					}
				}

				if($multiple_downloads && !$allowAccess)
				{
					//multiple licenses for multiple downloads is not allowed
					if(count($resourcesId) > 1 || count($requiredLicenses) > 1)
					{
						$returnPage = $this->_innerGetReturnPage();
						$this->addMenuItemParameter($returnPage);
						$app = JFactory::getApplication();
						$app->redirect($returnPage, JText::_("PAYPERDOWNLOADPLUS_JDOWNLOAD_PLUGIN_NO_MULTIPLE_DOWNLOAD"));
						exit;
					}
				}
			}
		}
	}

	function addMenuItemParameter(&$url)
	{
		if(strpos($url, "Itemid=") !== false)
			return;

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__menu'));
		$query->where($db->quoteName('link') . ' = ' . $db->quote($url));

		$db->setQuery($query);

		try {
		    $itemId = $db->loadResult();
		    if(!$itemId)
		        $itemId = JFactory::getApplication()->input->getInt('Itemid');
	        if($itemId)
	            $url .= "&Itemid=" . urlencode($itemId);
		} catch (RuntimeException $e) {
		    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}
	}

	function onAjaxCall($plugin, &$output)
	{
		if($plugin == "jdownload")
		{
		    $x = JFactory::getApplication()->input->getInt('x', 0);

		    $db = JFactory::getDBO();

		    $query = $db->getQuery(true);

		    if (self::isNewVersion()) {
		        $query->select($db->quoteName('id'));
		        $query->select($db->quoteName('title'));
		        $query->from($db->quoteName('#__jdownloads_files'));
		        $query->where($db->quoteName('catid') . ' = ' . $x);
		    } else {
		        $query->select($db->quoteName('file_id'));
		        $query->select($db->quoteName('file_title', 'title'));
		        $query->from($db->quoteName('#__jdownloads_files'));
		        $query->where($db->quoteName('cat_id') . ' = ' . $x);
		    }

		    $db->setQuery($query);

		    $output = '';
		    try {
		        $files = $db->loadObjectList();
		        $output = '<<' . count($files);
		        foreach($files as $file)
		        {
		            $output .= '>' . htmlspecialchars($file->file_id) . "<" . htmlspecialchars($file->title);
		        }
		        $output .= '>>';
		    } catch (RuntimeException $e) {
		        JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		    }
		}
	}

	function _innerGetReturnPage()
	{
	    $jinput = JFactory::getApplication()->input;

		$downloads = array();
		$download = $jinput->getInt('cid', 0);
		if($download)
		{
			$downloads[] = $download;
		}
		else
		{
		    $download = $jinput->getInt('id', 0);
			if($download)
			{
				$downloads[] = $download;
			}
			else
			{
			    $downloads = explode(',', $jinput->getRaw('list'));
			}
		}
		if(count($downloads) == 0)
			return;

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		if (self::isNewVersion()) {
		    $query->select($db->quoteName('catid'));
		    $query->from($db->quoteName('#__jdownloads_files'));
		    $query->where($db->quoteName('id') . ' = ' . (int)$downloads[0]);
		} else {
		    $query->select($db->quoteName('cat_id'));
		    $query->from($db->quoteName('#__jdownloads_files'));
		    $query->where($db->quoteName('file_id') . ' = ' . (int)$downloads[0]);
		}

		$db->setQuery($query);

		$cat = '';
		try {
		    $cat = $db->loadResult();
		} catch (RuntimeException $e) {
		    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		if(!$cat)
			return;
		if(count($downloads) > 1)
			$returnPage = "index.php?option=com_jdownloads&view=viewcategory&catid=" . urlencode($cat);
		else
			$returnPage = "index.php?option=com_jdownloads&view=summary&catid=" . urlencode($cat). "&id=" . urlencode($downloads[0]);
		return $returnPage;
	}

	function getReturnPage($option, &$returnPage)
	{
		if($option == "com_jdownloads")
		{
			$returnPage = $this->_innerGetReturnPage();
		}
	}

	function onCheckDecreaseDownloadCount($option, $resources, $requiredLicenses, $resourcesId, &$decreaseDownloadCount)
	{
		if($option == 'com_jdownloads')
		{
			$decreaseDownloadCount = true;
		}
	}

	/*
	Returns item id for current article.
	*/
	function onGetItemId($option, &$itemId)
	{
		if($option == 'com_jdownloads')
		{
		    $itemId = JFactory::getApplication()->input->getInt('id', 0);
		}
	}

}
?>