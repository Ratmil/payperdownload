<?php
/**
 * @plugin PayperdownloadPlus-Kunena
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined( '_JEXEC' ) or
die( 'Direct Access to this location is not allowed.' );

// import the JPlugin class
jimport('joomla.event.plugin');

global $mainframe;

class plgPayperDownloadPlusKunena extends JPlugin
{
	public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject);
		// load the language file
		$lang = JFactory::getLanguage();
		$lang->load('plg_payperdownloadplus_kunena', JPATH_SITE.'/administrator');
	}

	function onIsActive(&$plugins)
	{
		jimport('joomla.filesystem.folder');
		if(!JFolder::exists(JPATH_ROOT . '/administrator/components/com_kunena'))
			return false;
		$component = JComponentHelper::getComponent('com_kunena', true);
		if($component->enabled)
		{
			jimport('joomla.filesystem.file');
			$image = "";
			$version = new JVersion;
			if($version->RELEASE == "1.5")
			{
				if(JFile::exists(JPATH_ROOT . '/plugins/payperdownloadplus/kunena.jpg'))
					$image = "plugins/payperdownloadplus/kunena.jpg";
			}
			else
			{
				if(JFile::exists(JPATH_ROOT . '/plugins/payperdownloadplus/kunena/kunena.jpg'))
					$image = "plugins/payperdownloadplus/kunena/kunena.jpg";
			}
				
			$plugins[] = array("name" => "Kunena", "description" => JText::_("Kunena access"), 
				"image" => $image);
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
		$db->setQuery('SELECT id, name, parent_id AS parentid FROM #__kunena_categories');
		$cats = $db->loadObjectList();
		$cats_ordered = array();
		$this->reorderCats($cats_ordered, $cats, 0, 0);
		return $cats_ordered;
	}
	
	function onRenderConfig($pluginName, $resource)
	{
		if($pluginName == "Kunena")
		{
			$access = 0;
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
	
	function onGetSaveData(&$resourceId, 
		$pluginName, &$resourceName, &$resourceParams, &$optionParameter,
		&$resourceDesc)
	{
		if($pluginName == "Kunena")
		{
			$optionParameter = "com_kunena";
			$resourceId = JRequest::getInt('kunena_category');
			$kunena_access = JRequest::getInt('kunena_access');
			$db = JFactory::getDBO();
			$query = "";
			if($kunena_access)
				$access = JText::_("PAYPERDOWNLOADPLUS_KUNENA_PLUGIN_FULL_ACCESS");
			else
				$access = JText::_("PAYPERDOWNLOADPLUS_KUNENA_PLUGIN_POST_ACCESS");
			$resourceName = JText::_("PAYPERDOWNLOADPLUS_KUNENA_PLUGIN_ALL_CATEGORIES");
			if($resourceId)
			{
				$query = 'SELECT id, name as title FROM #__kunena_categories WHERE id = ' . $resourceId;
				$db->setQuery($query);
				$resource = $db->loadObject();
				if($resource)
					$resourceName = $resource->title;
			}
			else
				$resourceId = -1;
			$resourceDesc = JText::sprintf("PAYPERDOWNLOADPLUS_KUNENA_PLUGIN_RESOURCE_DESC", 
				$access, $resourceName);
			$resourceParams = $kunena_access;
		}
	}
	
	function getHigherLicense($license_ids)
	{
		if(count($license_ids))
		{
			$db = JFactory::getDBO();
			$nl = implode(',', $license_ids);
			$query = "SELECT level FROM #__payperdownloadplus_licenses WHERE license_id IN ($nl) 
				ORDER BY level DESC ";
			$db->setQuery($query);
			return $db->loadResult();
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
			$query = "SELECT parent_id AS parent FROM #__kunena_categories WHERE id = " . $category;
			$db->setQuery( $query );
			$category = (int)$db->loadResult();
			$parents[] = $category;
		}
		return $parents;
	}
	
	function onValidateAccess($option, $resources, &$allowAccess, &$requiredLicenses, &$resourcesId)
	{
		if($option == 'com_kunena')
		{
			$requiredLicenses = array();
			$resourcesId = array();
			$db = JFactory::getDBO();
			$catid = JRequest::getInt('catid', 0);
			$id = JRequest::getInt('id', 0);
			if($catid == 0)
			{
				$db->setQuery("SELECT catid FROM #__kunena_messages WHERE id = " . $id);
				$catid = $db->loadResult();
			}
			$parents = $this->getParents($catid);
			$func = JRequest::getVar('func');
			$view = JRequest::getVar('view');
			$layout = JRequest::getVar('layout');
			$task = JRequest::getVar('task');
			foreach($resources as $resource)
			{
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
			
			if(count($requiredLicenses) == 0)
			{
				
				foreach($resources as $resource)
				{
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
	
	
	function getReturnPage($option, &$returnPage)
	{
		if($option == "com_kunena")
		{
			$func = JRequest::getVar('func', '');
			$id = JRequest::getInt('id', 0);
			$catid = JRequest::getInt('catid', 0);
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
			$itemId = JRequest::getInt('id', 0);
		}
	}
	
}
?>