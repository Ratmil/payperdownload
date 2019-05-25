<?php
/**
 * @plugin PayperdownloadPlus-Kunena
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

// no direct access
defined ( '_JEXEC' ) or die;

// import the JPlugin class
jimport('joomla.event.plugin');

global $mainframe;

class plgPayperDownloadPlusKunena extends JPlugin
{
    protected $autoloadLanguage = true;

	function onIsActive(&$plugins)
	{
		jimport('joomla.filesystem.folder');
		if(!JFolder::exists(JPATH_ROOT . '/administrator/components/com_kunena'))
			return false;
		$component = JComponentHelper::getComponent('com_kunena', true);
		if($component->enabled)
		{
			$plugins[] = array("name" => "Kunena", "description" => JText::_("PAYPERDOWNLOADPLUS_KUNENA_PLUGIN_KUNENAACCESS"), "image" => "plugins/payperdownloadplus/kunena/kunena.png");
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
		$query->select($db->quoteName('parent_id', 'parentid'));
		$query->from($db->quoteName('#__kunena_categories'));

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

	function onRenderConfig($pluginName, $resource)
	{
		if($pluginName == "Kunena")
		{
			$access = 0;
			$category_id = '';
			if($resource)
			{
				$category_id = $resource->resource_id;
				$access = $resource->resource_params;
			}
			$uri = JURI::root();
			$cats = $this->getCategories();
			?>
			<tr>
			<td align="left" class="key"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_KUNENA_PLUGIN_CATEGORY"));?></td>
			<td>
			<script type="text/javascript">
			var ppd_kunena_plugin_full_access_desc = '<?php echo JText::_("PAYPERDOWNLOADPLUS_KUNENA_PLUGIN_FULL_ACCESS_DESC", true);?>';
			var ppd_kunena_plugin_post_access_desc = '<?php echo JText::_("PAYPERDOWNLOADPLUS_KUNENA_PLUGIN_POST_ACCESS_DESC", true);?>';
			function access_change()
			{
				var kunena_access = document.getElementById('kunena_access');
				var access_desc = document.getElementById('access_desc');
				switch(parseInt(kunena_access.value))
				{
				case 1:
					access_desc.innerHTML = ppd_kunena_plugin_full_access_desc;
					break;
				case 0:
					access_desc.innerHTML = ppd_kunena_plugin_post_access_desc;
					break;
				}
			}
			</script>
			<select id="kunena_category" name="kunena_category">
			<option value="0"><?php echo JText::_("PAYPERDOWNLOADPLUS_KUNENA_PLUGIN_ALL_CATEGORIES");?></option>
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
			<td align="left" class="key"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_KUNENA_PLUGIN_ACCESS_TYPE"));?></td>
			<td>
			<select id="kunena_access" name="kunena_access" onchange="access_change();">
			<option value="1" <?php if($access == 1) echo "selected";?>><?php echo JText::_("PAYPERDOWNLOADPLUS_KUNENA_PLUGIN_FULL_ACCESS");?></option>
			<option value="0" <?php if($access == 0) echo "selected";?>><?php echo JText::_("PAYPERDOWNLOADPLUS_KUNENA_PLUGIN_POST_ACCESS");?></option>
			</select>
			<div id="access_desc">
			<?php
				if($access == 1)
					echo JText::_("PAYPERDOWNLOADPLUS_KUNENA_PLUGIN_FULL_ACCESS_DESC");
				else
					echo JText::_("PAYPERDOWNLOADPLUS_KUNENA_PLUGIN_POST_ACCESS_DESC");
			?>
			</div>
			</td>
			</tr>
			<?php
		}
	}

	function onGetSaveData(&$resourceId, $pluginName, &$resourceName, &$resourceParams, &$optionParameter, &$resourceDesc)
	{
		if($pluginName == "Kunena")
		{
		    $jinput = JFactory::getApplication()->input;

			$optionParameter = "com_kunena";
			$resourceId = $jinput->getInt('kunena_category');
			$kunena_access = $jinput->getInt('kunena_access');

			if($kunena_access)
				$access = JText::_("PAYPERDOWNLOADPLUS_KUNENA_PLUGIN_FULL_ACCESS");
			else
				$access = JText::_("PAYPERDOWNLOADPLUS_KUNENA_PLUGIN_POST_ACCESS");
			$resourceName = JText::_("PAYPERDOWNLOADPLUS_KUNENA_PLUGIN_ALL_CATEGORIES");
			if($resourceId)
			{
			    $db = JFactory::getDBO();

			    $query = $db->getQuery(true);

			    $query->select($db->quoteName('id'));
			    $query->select($db->quoteName('name', 'title'));
			    $query->from($db->quoteName('#__kunena_categories'));
			    $query->where($db->quoteName('id') . ' = ' . $resourceId);

			    $db->setQuery($query);

			    try {
			        $resource = $db->loadObject();
			        if($resource)
			            $resourceName = $resource->title;
			    } catch (RuntimeException $e) {
			        JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			    }
			}
			else
				$resourceId = -1;
			$resourceDesc = JText::sprintf("PAYPERDOWNLOADPLUS_KUNENA_PLUGIN_RESOURCE_DESC", $access, $resourceName);
			$resourceParams = $kunena_access;
		}
	}

	function getHigherLicense($license_ids)
	{
		if(count($license_ids))
		{
			$db = JFactory::getDBO();

			$query = $db->getQuery(true);

			$query->select($db->quoteName('level'));
			$query->from($db->quoteName('#__payperdownloadplus_licenses'));
			$query->where($db->quoteName('license_id') . ' IN (' . implode(',', $license_ids) . ')');
			$query->order($db->quoteName('level') . ' DESC');

			$db->setQuery($query);

			$level = 0;
			try {
			    $level = $db->loadResult();
			} catch (RuntimeException $e) {
			    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			}

			return $level;
		}
		else
			return null;
	}

	function onCheckDecreaseDownloadCount($option, $resources, $requiredLicenses, $resourcesId, &$decreaseDownloadCount)
	{
		if($option == 'com_kunena')
		{
			$decreaseDownloadCount = false;
		}
	}

	function getParents($category)
	{
		$db = JFactory::getDBO();
		$category = (int)$category;
		$parents = array();
		$parents[] = $category;
		while($category != 0)
		{
		    $query = $db->getQuery(true);

		    $query->select($db->quoteName('parent_id', 'parent'));
		    $query->from($db->quoteName('#__kunena_categories'));
		    $query->where($db->quoteName('id') . ' = ' . $category);

		    $db->setQuery($query);

		    try {
		        $category = (int)$db->loadResult();
		        $parents[] = $category;
		    } catch (RuntimeException $e) {
		        JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		        $category = 0; // to get out of the loop
		    }
		}
		return $parents;
	}

	function onValidateAccess($option, $resources, &$allowAccess, &$requiredLicenses, &$resourcesId)
	{
		if($option == 'com_kunena')
		{
		    $jinput = JFactory::getApplication()->input;

			$requiredLicenses = array();
			$resourcesId = array();
			$db = JFactory::getDBO();
			$catid = $jinput->getInt('catid', 0);
			$id = $jinput->getInt('id', 0);
			if($catid == 0)
			{
			    $query = $db->getQuery(true);

			    $query->select($db->quoteName('catid'));
			    $query->from($db->quoteName('#__kunena_messages'));
			    $query->where($db->quoteName('id') . ' = ' . $id);

			    $db->setQuery($query);

			    try {
			        $catid = $db->loadResult();
			    } catch (RuntimeException $e) {
			        JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			    }
			}
			$parents = $this->getParents($catid);
			$func = $jinput->get('func');
			$view = $jinput->get('view');
			$layout = $jinput->get('layout');
			$task = $jinput->get('task');
			foreach($resources as $resource)
			{
			    if ($resource->resource_type === 'Kunena') {
    				if($resource->resource_id != -1 && array_search($resource->resource_id, $parents) !== false)
    				{
    					if((($func == 'view' || $view == 'topic') && $resource->resource_params == 1) ||
    					 $func == 'post' || $layout == 'reply' || $task == 'post')
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

				foreach($resources as $resource)
				{
				    if ($resource->resource_type === 'Kunena') {
    					if($resource->resource_id == -1)
    					{
    						if((($func == 'view' || $view == 'topic') && $resource->resource_params == 1) ||
    							$func == 'post' || $layout == 'reply' || $task == 'post')
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


	function getReturnPage($option, &$returnPage)
	{
		if($option == "com_kunena")
		{
		    $jinput = JFactory::getApplication()->input;

		    $func = $jinput->get('func', '');
		    $id = $jinput->getInt('id', 0);
		    $catid = $jinput->getInt('catid', 0);
			if($func == 'view' || $func == 'post')
			{
				if($id)
					$returnPage = "index.php?option=com_kunena&func=view&catid=$catid&id=$id";
				else if($catid)
					$returnPage = "index.php?option=com_kunena&func=showcat&catid=$catid";
				else
					$returnPage = "index.php?option=com_kunena";
			}
			else
				$returnPage = "index.php?option=com_kunena";
		}
	}

	/*
	Returns item id for current forum topic.
	*/
	function onGetItemId($option, &$itemId)
	{
		if($option == 'com_kunena')
		{
		    $itemId = JFactory::getApplication()->input->getInt('id', 0);
		}
	}

}
?>