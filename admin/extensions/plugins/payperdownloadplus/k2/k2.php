<?php
// import the JPlugin class
jimport('joomla.event.plugin');

global $mainframe;

class plgPayperDownloadplusK2 extends JPlugin
{
    protected $autoloadLanguage = true;

	function onIsActive(&$plugins)
	{
		$component = JComponentHelper::getComponent('com_k2', true);
		if($component->enabled)
		{
		    $plugins[] = array("name" => "K2", "description" => JText::_("PAYPERDOWNLOADPLUS_K2_PLUGIN_K2ITEM"), "image" => "plugins/payperdownloadplus/k2/k2.jpg");
		}
	}

	function reorderCats(&$cats_ordered, $cats, $parent_id, $depth)
	{
		$count = count($cats);
		for($i = 0; $i < $count; $i++)
		{
			$cat = $cats[$i];
			if($cat->parentid == $parent_id)
			{
				$cat->depth = $depth;
				$cats_ordered[] = $cat;
				$this->reorderCats($cats_ordered, $cats, $cat->id, $depth + 1);
			}
		}
	}

	function getCategories()
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName('id'));
		$query->select($db->quoteName('name'));
		$query->select($db->quoteName('parent', 'parentid'));
		$query->from($db->quoteName('#__k2_categories'));
		$query->where($db->quoteName('published') . ' <> 0');
		$query->where($db->quoteName('trash') . ' = 0');

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

		$query->select($db->quoteName(array('id', 'title')));
		$query->from($db->quoteName('#__k2_items'));
		$query->where($db->quoteName('catid') . ' = ' . (int)$cat_id);

		$db->setQuery($query);

		$files = null;
		try {
		    $files = $db->loadObjectList();
		} catch (RuntimeException $e) {
		    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $files;
	}

	function getAttachments($file_id)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'filename')));
		$query->from($db->quoteName('#__k2_attachments'));
		$query->where($db->quoteName('itemID') . ' = ' . (int)$file_id);

		$db->setQuery($query);

		$attachments = null;
		try {
		    $attachments = $db->loadObjectList();
		} catch (RuntimeException $e) {
		    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $attachments;
	}

	function onRenderConfig($pluginName, $resource)
	{
		if($pluginName == "K2")
		{
			$files = null;
			$paytoreadmore = "";
			$category_id = "";
			$attachments = array();
			if($resource)
			{
				$file_id = $resource->resource_id;
				if($file_id)
					$attachments = $this->getAttachments($file_id);
				list($category_id, $attachment_id, $paytoreadmore) = explode('_', $resource->resource_params);
				if($category_id)
					$files = $this->getFiles($category_id);
			}
			$uri = JURI::root();
			$scriptPath = "administrator/components/com_payperdownload/js/";
			JHTML::script($scriptPath . 'ajax_source.js');
			$plugin_path = "plugins/payperdownloadplus/k2/";
			$scriptPath = $uri . $plugin_path;
			JHTML::script($scriptPath . 'k2_plugin.js');
			$cats = $this->getCategories();
			?>
			<tr>
			<td  width="100" align="left" class="key"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_K2_PLUGIN_CATEGORY"));?></td>
			<td>
			<select id="k2_category" name="k2_category" onchange="k2_plugin_category_change();">
			<option value="0"><?php echo JText::_("PAYPERDOWNLOADPLUS_K2_PLUGIN_ALL_CATEGORIES");?></option>
			<?php
			foreach($cats as $cat)
			{
				$space = '';
				for($i = 0; $i < $cat->depth; $i++)
					$space .= '&nbsp;&nbsp;&nbsp;&nbsp;';
				$selected = $cat->id == $category_id ? "selected":"";
				echo "<option value=\"" . htmlspecialchars($cat->id) . "\" $selected>" . $space . htmlspecialchars($cat->name) . "</option>";
			}
			?>
			</select>
			</td>
			</tr>
			<tr>
			<td  width="100" align="left" class="key">
				<?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_K2_PLUGIN_ALLOW_READING_HEADER"));?>
			</td>
			<?php
			$checked = $paytoreadmore == "1" ? "checked" : "";
			?>
			<td><input type="checkbox" name="paytoreadmore" <?php echo $checked;?> value="1"/></td>
			</tr>
			<tr>
			<td  width="100" align="left" class="key"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_K2_PLUGIN_DOWNLOAD"));?></td>
			<td>
			<select id="k2_file" name="k2_file" onchange="k2_plugin_file_change();">
			<option value="0"><?php echo JText::_("PAYPERDOWNLOADPLUS_K2_PLUGIN_ALL_DOWNLOADS");?></option>
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
			<tr>
			<td  width="100" align="left" class="key"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_K2_PLUGIN_ATTACHMENT"));?></td>
			<td>
			<select id="k2_attachement" name="k2_attachement">
			<option value="0"><?php echo JText::_("PAYPERDOWNLOADPLUS_K2_PLUGIN_ALL_ATTACHMENTS");?></option>
			<?php
			if($attachments)
			foreach($attachments as $attachment)
			{
				$selected = $attachment->id == $attachment_id ? "selected":"";
				echo "<option value=\"" . htmlspecialchars($attachment->id) . "\" $selected>" . htmlspecialchars($attachment->filename) . "</option>";
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
		if($pluginName == "K2")
		{
		    $jinput = JFactory::getApplication()->input;

			$optionParameter = "com_k2";
			$resourceId = $jinput->getInt('k2_file');
			$categoryId = $jinput->getInt('k2_category');
			$attachmentId = $jinput->getInt('k2_attachement');
			$paytoreadmore = $jinput->getInt('paytoreadmore', 0);

			$resourceDesc = JText::_("PAYPERDOWNLOADPLUS_K2_PLUGIN_ALL_DOWNLOADS");
			if($attachmentId)
			{
			    $db = JFactory::getDBO();

			    $query = $db->getQuery(true);

			    $query->select($db->quoteName('id'));
			    $query->select($db->quoteName('filename', 'title'));
			    $query->from($db->quoteName('#__k2_attachments'));
			    $query->where($db->quoteName('id') . ' = ' . $attachmentId);

			    $db->setQuery($query);

			    try {
			        $resource = $db->loadObject();
			        if($resource)
			            $resourceName = $resource->title;
			    } catch (RuntimeException $e) {
			        JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			    }

				$resourceDesc = JText::_("PAYPERDOWNLOADPLUS_K2_PLUGIN_ATTACHMENT");
			}
			else if($resourceId)
			{
				$attachmentId = -1;

				$db = JFactory::getDBO();

				$query = $db->getQuery(true);

				$query->select($db->quoteName('id'));
				$query->select($db->quoteName('title'));
				$query->from($db->quoteName('#__k2_items'));
				$query->where($db->quoteName('id') . ' = ' . $resourceId);

				$db->setQuery($query);

				try {
				    $resource = $db->loadObject();
				    if($resource)
				        $resourceName = $resource->title;
				} catch (RuntimeException $e) {
				    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
				}

				$resourceDesc = JText::_("PAYPERDOWNLOADPLUS_K2_PLUGIN_DOWNLOAD");
			}
			else if($categoryId)
			{
				$resourceId = -1;
				$attachmentId = -1;

				$db = JFactory::getDBO();

				$query = $db->getQuery(true);

				$query->select($db->quoteName('id'));
				$query->select($db->quoteName('name', 'title'));
				$query->from($db->quoteName('#__k2_categories'));
				$query->where($db->quoteName('id') . ' = ' . $categoryId);

				$db->setQuery($query);

				try {
				    $resource = $db->loadObject();
				    if($resource)
				        $resourceName = $resource->title;
				} catch (RuntimeException $e) {
				    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
				}

				$resourceDesc = JText::_("PAYPERDOWNLOADPLUS_K2_PLUGIN_CATEGORY");
			}
			else
			{
				$resourceId = -1;
			}
			$resourceName = JText::_("PAYPERDOWNLOADPLUS_K2_PLUGIN_ALL_DOWNLOADS");

			$resourceParams = $categoryId . "_" . $attachmentId . "_" . $paytoreadmore;
		}
	}

	function onValidateAccess($option, $resources, &$allowAccess, &$requiredLicenses, &$resourcesId)
	{
		if($option == 'com_k2')
		{
		    $jinput = JFactory::getApplication()->input;

		    $view = $jinput->get('view', 'item');
		    $task = $jinput->get('task', '');
			if($view == 'item' && $task != 'download' && $task != 'save')
			{
				$requiredLicenses = array();
				$resourcesId = array();
				$paytoreadmore = 0;
				$item = $jinput->getInt('id', 0);

				foreach($resources as $resource)
				{
				    if ($resource->resource_type === 'K2') {
    					list($categoryId, $attachmentId, $paytoreadmore) = explode('_', $resource->resource_params);
    					if(isset($paytoreadmore) && (int)$paytoreadmore == 1)
    						continue;
    					if($resource->resource_id == $item && $attachmentId == -1)
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

				if(count($requiredLicenses) == 0)
				{
					$db = JFactory::getDBO();

					$query = $db->getQuery(true);

					$query->select($db->quoteName('catid'));
					$query->from($db->quoteName('#__k2_items'));
					$query->where($db->quoteName('id') . ' = ' . (int)$item);

					$db->setQuery($query);

					try {
					    $cats = $db->loadResultArray();
					    if(count($cats) == 0)
					        return;
					} catch (RuntimeException $e) {
					    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
					}

					foreach($resources as $resource)
					{
					    if ($resource->resource_type === 'K2') {
    						list($categoryId, $attachmentId) = explode('_', $resource->resource_params);
    						if($resource->resource_id == -1 && array_search($categoryId, $cats) !== false)
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
			else
			if($view == 'item' && $task == 'download')
			{
				$requiredLicenses = array();
				$resourcesId = array();
				$attachment = $jinput->getInt('id', 0);

				foreach($resources as $resource)
				{
				    if ($resource->resource_type === 'K2') {
    					list($categoryId, $attachmentId) = explode('_', $resource->resource_params);
    					if($attachmentId == $attachment)
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

				$db = JFactory::getDBO();

				$query = $db->getQuery(true);

				$query->select($db->quoteName('itemID'));
				$query->from($db->quoteName('#__k2_attachments'));
				$query->where($db->quoteName('id') . ' = ' . $attachment);

				$db->setQuery($query);

				$item = 0;
				try {
				    $item = (int)$db->loadResult();
				} catch (RuntimeException $e) {
				    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
				}

				if(count($requiredLicenses) == 0)
				{
					foreach($resources as $resource)
					{
					    if ($resource->resource_type === 'K2') {
    						list($categoryId, $attachmentId) = explode('_', $resource->resource_params);
    						if($resource->resource_id == $item && $attachmentId == -1)
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

				if(count($requiredLicenses) == 0)
				{
				    $query = $db->getQuery(true);

				    $query->select($db->quoteName('catid'));
				    $query->from($db->quoteName('#__k2_items'));
				    $query->where($db->quoteName('id') . ' = ' . $item);

				    $db->setQuery($query);

				    $cat = 0;
				    try {
				        $cat = (int)$db->loadResult();
				    } catch (RuntimeException $e) {
				        JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
				    }

					foreach($resources as $resource)
					{
					    if ($resource->resource_type === 'K2') {
    						list($categoryId, $attachmentId) = explode('_', $resource->resource_params);
    						if($categoryId == $cat && $resource->resource_id == -1 && $attachmentId == -1)
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
	}

	function onAjaxCall($plugin, &$output)
	{
		if($plugin == "k2")
		{
		    $jinput = JFactory::getApplication()->input;

		    $x = $jinput->getInt('x', 0);
		    $t = $jinput->getRaw('t', 'f');
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			if($t=='a')
			{
			    $query->select($db->quoteName(array('id', 'filename')));
			    $query->from($db->quoteName('#__k2_attachments'));
			    $query->where($db->quoteName('itemID') . ' = ' . $x);

			    $db->setQuery($query);

			    $output = '';
			    try {
			        $files = $db->loadObjectList();
			        $output = '<<' . count($files);
			        foreach($files as $file)
			        {
			            $output .= '>' . htmlspecialchars($file->id) . "<" . htmlspecialchars($file->filename);
			        }
			        $output .= '>>';
			    } catch (RuntimeException $e) {
			        JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			    }
			}
			else if($t=='f')
			{
			    $query->select($db->quoteName(array('id', 'title')));
			    $query->from($db->quoteName('#__k2_items'));
			    $query->where($db->quoteName('catid') . ' = ' . $x);

			    $db->setQuery($query);

			    $output = '';
			    try {
			        $files = $db->loadObjectList();
			        $output .= '<<' . count($files);
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

	function getReturnPage($option, &$returnPage)
	{
		if($option == "com_k2")
		{
		    $jinput = JFactory::getApplication()->input;

		    $task = $jinput->get('task', '');
			$item = $jinput->getInt('id', 0);
			if($task == 'download')
			{
				$db = JFactory::getDBO();

				$query = $db->getQuery(true);

				$query->select($db->quoteName('itemID'));
				$query->from($db->quoteName('#__k2_attachments'));
				$query->where($db->quoteName('id') . ' = ' . $item);

				$db->setQuery($query);

				try {
				    $item = (int)$db->loadResult();
				} catch (RuntimeException $e) {
				    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
				}
			}
			$returnPage = "index.php?option=com_k2&view=item&layout=item&id=" . urlencode($item);
		}
	}

	/*
	Returns item id for k2 item
	*/
	function onGetItemId($option, &$itemId)
	{
		if($option == 'com_k2')
		{
		    $itemId = JFactory::getApplication()->input->getInt('id', 0);
		}
	}
}
?>