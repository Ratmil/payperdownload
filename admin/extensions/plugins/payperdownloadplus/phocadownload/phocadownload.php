<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

// no direct access
defined ( '_JEXEC' ) or die;

// import the JPlugin class
jimport('joomla.event.plugin');

class plgPayperDownloadPlusPhocadownload extends JPlugin
{
    protected $autoloadLanguage = true;

	function onIsActive(&$plugins)
	{
		jimport('joomla.filesystem.folder');
		if(!JFolder::exists(JPATH_ROOT . '/administrator/components/com_phocadownload'))
			return false;
		$component = JComponentHelper::getComponent('com_phocadownload', true);
		if($component->enabled)
		{
		    $plugins[] = array("name" => "Phoca Download", "description" => JText::_("PAYPERDOWNLOADPLUS_PHOCADOWNLOAD_PLUGIN_PHOCADOWNLOADFILE"), "image" => "plugins/payperdownloadplus/phocadownload/phocadownload.png");
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
				if(!isset($cat->end_branch) || !$cat->end_branch)
					$this->reorderCats($cats_ordered, $cats, $cat->id, $depth + 1);
			}
		}
	}

	function getCategories()
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'title', 'parent_id')));
		$query->from($db->quoteName('#__phocadownload_categories'));
		$query->where($db->quoteName('published') . ' = 1');

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

	function getDownloads($category)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'title')));
		$query->from($db->quoteName('#__phocadownload'));
		$query->where($db->quoteName('catid') . ' = ' . (int)$category);

		$db->setQuery($query);

		$downloads = array();

		try {
		    $downloads = $db->loadObjectList();
		} catch (RuntimeException $e) {
		    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $downloads;
	}

	function onRenderConfig($pluginName, $resource)
	{
		if($pluginName == "Phoca Download")
		{
		    $content_id = '';
		    $category_id = '';
		    $files = array();
			if($resource)
			{
				$content_id = $resource->resource_id;
				$category_id = $resource->resource_params;
				if($category_id)
					$files = $this->getDownloads($category_id);
			}
			$uri = JURI::root();
			$scriptPath = "administrator/components/com_payperdownload/js/";
			JHTML::script($scriptPath . 'ajax_source.js', false);

			$plugin_path = "plugins/payperdownloadplus/phocadownload/";

			$scriptPath = $uri . $plugin_path;
			JHTML::script($scriptPath . 'phoca_plugin.js', false);
			$cats = $this->getCategories();
			?>
			<tr>
			<td align="left" class="key"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_PHOCADOWNLOAD_PLUGIN_CATEGORY"));?></td>
			<td>
			<select id="phocadownload_category" name="phocadownload_category" onchange="phocadownload_plugin_category_change();">
			<option value="0"><?php echo JText::_("PAYPERDOWNLOADPLUS_PHOCADOWNLOAD_PLUGIN_ALL_CATEGORIES");?></option>
			<?php
			foreach($cats as $cat)
			{
				$space = '';
				for($i = 0; $i < $cat->depth; $i++)
					$space .= '&nbsp;&nbsp;&nbsp;&nbsp;';
				$selected = $cat->id == $category_id ? "selected":"";
				echo "<option value=\"" . htmlspecialchars($cat->id) . "\" $selected>" . $space . htmlspecialchars($cat->title) . "</option>";
			}
			?>
			</select>
			</td>
			</tr>
			<tr>
			<td  width="100" align="left" class="key"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_PHOCADOWNLOAD_PLUGIN_DOWNLOAD"));?></td>
			<td>
			<select id="phocadownload_file" name="phocadownload_file">
			<option value="0"><?php echo JText::_("PAYPERDOWNLOADPLUS_PHOCADOWNLOAD_PLUGIN_ALL_DOWNLOADS");?></option>
			<?php
			if($files)
				foreach($files as $file)
				{
					$selected = $file->id == $content_id ? "selected":"";
					echo "<option value=\"" . htmlspecialchars($file->id) . "\" $selected>" . htmlspecialchars($file->title) . "</option>";
				}
			?>
			</select>
			&nbsp;
			<input type="text" id="search_text" />
			&nbsp;
			<input type="button" id="search_button" value="<?php echo JText::_("PAYPERDOWNLOADPLUS_PHOCADOWNLOAD_PLUGIN_SEARCH")?>" onclick="phocadownload_plugin_search();" />
			<script type="text/javascript">
			var cancel_text = '<?php echo JText::_("PAYPERDOWNLOADPLUS_PHOCADOWNLOAD_PLUGIN_CANCEL", true)?>';
			</script>
			<div id="search_result" style="position:absolute;visibility:hidden;z-index:1000;border-width:1px;border-style:solid;background-color:#ffffff;"></div>
			</td>
			</tr>
			<?php
		}
	}

	function onGetSaveData(&$resourceId,
		$pluginName, &$resourceName, &$resourceParams, &$optionParameter,
		&$resourceDesc)
	{
		if($pluginName == "Phoca Download")
		{
		    $jinput = JFactory::getApplication()->input;

			$optionParameter = "com_phocadownload";
			$resourceId = $jinput->getInt('phocadownload_file');
			$categoryId = $jinput->getInt('phocadownload_category');

			$resourceDesc = JText::_("PAYPERDOWNLOADPLUS_PHOCADOWNLOAD_PLUGIN_ALL_DOWNLOADS");
			if($resourceId)
			{
			    $db = JFactory::getDBO();

			    $query = $db->getQuery(true);

			    $query->select($db->quoteName(array('id', 'title')));
			    $query->from($db->quoteName('#__phocadownload'));
			    $query->where($db->quoteName('id') . ' = ' . $resourceId);

			    $db->setQuery($query);

			    try {
			        $resource = $db->loadObject();
			        if($resource)
			            $resourceName = $resource->title;
			    } catch (RuntimeException $e) {
			        JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			    }

				$resourceDesc = JText::_("PAYPERDOWNLOADPLUS_PHOCADOWNLOAD_PLUGIN_CONTENT_ARTICLE");
			}
			else if($categoryId)
			{
				$resourceId = -1;

				$db = JFactory::getDBO();

				$query = $db->getQuery(true);

				$query->select($db->quoteName(array('id', 'title')));
				$query->from($db->quoteName('#__phocadownload_categories'));
				$query->where($db->quoteName('id') . ' = ' . $categoryId);

				$db->setQuery($query);

				try {
				    $resource = $db->loadObject();
				    if($resource)
				        $resourceName = $resource->title;
				} catch (RuntimeException $e) {
				    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
				}

				$resourceDesc = JText::_("PAYPERDOWNLOADPLUS_PHOCADOWNLOAD_PLUGIN_CONTENT_CATEGORY");
			}
			else
				$resourceId = -1;
			$resourceName = JText::_("PAYPERDOWNLOADPLUS_PHOCADOWNLOAD_PLUGIN_ALL_DOWNLOADS");

			$resourceParams = $categoryId;
		}
	}

	function getParentCategories($id)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName('catid'));
		$query->from($db->quoteName('#__phocadownload'));
		$query->where($db->quoteName('id') . ' = ' . (int)$id);

		$db->setQuery($query);

		$parentCategories = array();

		try {
		    $category = $db->loadResult();

		    while($category)
		    {
		        $parentCategories[] = $category;

		        $query->clear();

		        $query->select($db->quoteName('parent_id'));
		        $query->from($db->quoteName('#__phocadownload_categories'));
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
		if($option == 'com_phocadownload')
		{
		    $jinput = JFactory::getApplication()->input;

			$requiredLicenses = array();
			$download = $jinput->getInt('download', 0);
			$resourcesId = array();
			if($download == 0)
				return;
			$view = $jinput->get('view');
			if($view != 'file' && $view != 'category')
				return;
			//Check download licenses
			foreach($resources as $resource)
			{
			    if ($resource->resource_type === 'Phoca Download') {
    				if($resource->resource_id == $download)
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

			$download_categories = $this->getParentCategories($download);
			//Check category licenses
			foreach($resources as $resource)
			{
			    if ($resource->resource_type === 'Phoca Download') {
    				if($resource->resource_id < 0)
    				{
    					$categoryId = $resource->resource_params;
    					if(array_search($categoryId, $download_categories) !== false)
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

			//Check all downloads license
			foreach($resources as $resource)
			{
			    if ($resource->resource_type === 'Phoca Download') {
    				if($resource->resource_id < 0)
    				{
    					$categoryId = $resource->resource_params;
    					if($categoryId == 0)
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
		}
	}

	function onAjaxCall($plugin, &$output)
	{
		if($plugin == "phocadownload")
		{
		    $jinput = JFactory::getApplication()->input;

		    $t = $jinput->getRaw('t');
		    $x = $jinput->getRaw('x');

		    $db = JFactory::getDBO();
		    $query = $db->getQuery(true);

			if($t == 's')
			{
			    $query->select($db->quoteName(array('id', 'title', 'catid')));
			    $query->from($db->quoteName('#__phocadownload'));
			    $query->where($db->quoteName('title') . ' LIKE ' . $db->quote('%' . $x . '%'));
			    $query->setLimit('10');

			    $db->setQuery($query);

			    $output = '';
			    try {
			        $files = $db->loadObjectList();
			        $output = '<<' . count($files);
			        foreach($files as $file)
			        {
			            $output .= '>' . htmlspecialchars($file->id) . "<" . htmlspecialchars($file->title) . "<" . htmlspecialchars($file->catid);
			        }
			        $output .= '>>';
			    } catch (RuntimeException $e) {
			        JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			    }
			}
			else if($t == 'a')
			{
			    $query->select($db->quoteName(array('id', 'title')));
			    $query->from($db->quoteName('#__phocadownload'));
			    $query->where($db->quoteName('catid') . ' = ' . (int)$x);

			    $db->setQuery($query);

			    $output = '';
			    try {
			        $files = $db->loadObjectList();
			        $output = '<<' . count($files);
			        foreach($files as $file)
			        {
			            $output .= '>' . htmlspecialchars($file->id) . "<" . htmlspecialchars($file->title);
			        }
			        $output .= '>>';
			    } catch (RuntimeException $e) {
			        JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			    }
			}
		}
	}

	/*function getReturnPage($option, &$returnPage)
	{
		if($option == "com_phocadownload")
		{
			$download = JFactory::getApplication()->input->getInt('download', 0);
			if(!$download)
			{
				return;
			}
			$db = JFactory::getDBO();
			$db->setQuery("SELECT catid FROM #__phocadownload WHERE id = " . $download);
			$download_category = (int)$db->loadResult();
			$phoceRouterHelperFile =
				JPATH_SITE . "/components/com_phocadownload/helpers/route.php";
			if(file_exists($phoceRouterHelperFile))
			{
				$alias = "";
				$db->setQuery("SELECT id, alias FROM #__phocadownload_categories WHERE id = " . (int)$download_category);
				$cat = $db->loadObject();
				if($cat)
					$alias = $cat->alias;
				require_once($phoceRouterHelperFile);
				if(class_exists("PhocaDownloadHelperRoute"))
				{
					$uri = JRoute::_(PhocaDownloadHelperRoute::getCategoryRoute($download_category, $alias));

					// should always work according to PHP.net
					// http://www.php.net/manual/en/reserved.variables.server.php
					// 1 - Set to a non-empty value if the script was queried through the HTTPS protocol.
					// 2 - Note that when using ISAPI with IIS, the value will be "off" if the request was not made through the HTTPS protocol
					$is_protocol_https = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== "off") ? true : false;
					if ($is_protocol_https)
						$prefix = "https://";
					else
						$prefix = "http://";
					$port = $_SERVER['SERVER_PORT'];
					if($port == '80')
						$port = '';
					else
						$port = ':' . $port;
					$returnPage = $prefix . $_SERVER['SERVER_NAME'] . $port . $uri;
					return;
				}
			}
			$returnPage = "index.php?option=com_phocadownload&view=category&id=" .
					urlencode($download_category);
		}
	}*/

	function onCheckDecreaseDownloadCount($option, $resources, $requiredLicenses, $resourcesId, &$decreaseDownloadCount)
	{
		if($option == 'com_phocadownload')
		{
			$decreaseDownloadCount = true;
		}
	}

	/*
	Returns item id for current download. In the case of Phocadownload it is the id of
		the download
	*/
	function onGetItemId($option, &$itemId)
	{
		if($option == 'com_phocadownload')
		{
		    $itemId = JFactory::getApplication()->input->getInt('download', 0);
		}
	}

}
?>