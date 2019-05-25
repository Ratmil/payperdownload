<?php
/**
 * @component Pay per Download Plus component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

// no direct access
defined ( '_JEXEC' ) or die;

// import the JPlugin class
jimport('joomla.event.plugin');

class plgPayperdownloadplusMenuitem extends JPlugin
{
    protected $autoloadLanguage = true;

	function onIsActive(&$plugins)
	{
		$plugins[] = array("name" => "Menuitem", "description" => JText::_("PAYPERDOWNLOADPLUS_MENUITEM_PLUGIN_JOOMLA_MENU_ITEM"), "image" => "plugins/payperdownloadplus/menuitem/menuitem.png");
	}

	function onRenderConfig($pluginName, $resource)
	{
		if($pluginName == "Menuitem")
		{
		    $item_id = 0;
			if($resource)
			{
				$item_id = $resource->resource_id;
			}
			$items = $this->getMenuitems();
			$uri = JURI::root();
			?>
			<tr>
			<td  width="100" class="key"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_MENUITEM_PLUGIN_MENU_ITEM"));?></td>
			<td>
			<select id="menuitem" name="menuitem">
			<?php
			$last_menu_type = "";
			$group_open = false;
			foreach($items as $item)
			{
				if($item->menutype != $last_menu_type)
				{
					if($group_open)
						echo "</optgroup>";
					$group_open = true;
					echo "<optgroup label=\"" . htmlspecialchars($item->menutype) . "\">";
					$last_menu_type = $item->menutype;
				}
				$space = '';
				for($i = 0; $i < $item->depth; $i++)
					$space .= '&nbsp;&nbsp;&nbsp;&nbsp;';
				$selected = $item->id == $item_id ? "selected":"";
				echo "<option value=\"" . htmlspecialchars($item->id) . "\" $selected>" .
					$space . htmlspecialchars($item->title) . "</option>";
			}
			if($group_open)
				echo "</optgroup>";
			?>
			</select>
			</td>
			</tr>
			<?php
		}
	}

	function reorderItems(&$items_ordered, $items, $parent_id, $depth)
	{
		$count = count($items);
		for($i = 0; $i < $count; $i++)
		{
			$item = $items[$i];
			if($item->parent_id == $parent_id)
			{
				$item->depth = $depth;
				$items_ordered[] = $item;
				$this->reorderItems($items_ordered, $items, $item->id, $depth + 1);
			}
		}
	}

	function getMenuitems()
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('config_id', 'payment_page_menuitem', 'thankyou_page_menuitem')));
		$query->from($db->quoteName('#__payperdownloadplus_config'));
		$query->setLimit('1');

		$db->setQuery($query);

		$menu_items = array();

		try {
		    $menu_items = $db->loadObject();
		} catch (RuntimeException $e) {
		    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		$query->clear();

		$query->select($db->quoteName(array('id', 'title', 'menutype', 'parent_id')));
		$query->from($db->quoteName('#__menu'));
		$query->where($db->quoteName('client_id') . ' = 0');
		if($menu_items)
		{
		    $query->where($db->quoteName('id') . ' <> ' . (int)$menu_items->payment_page_menuitem);
		    $query->where($db->quoteName('id') . ' <> ' . (int)$menu_items->thankyou_page_menuitem);
		}
		$query->order($db->quoteName('menutype'));

		$db->setQuery($query);

		$ordered_items = array();

		try {
		    $items = $db->loadObjectList();
		    $this->reorderItems($ordered_items, $items, 0, 0);
		} catch (RuntimeException $e) {
		    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $ordered_items;
	}

	function onGetSaveData(&$resourceId, $pluginName, &$resourceName, &$resourceParams, &$optionParameter, &$resourceDesc)
	{
		if($pluginName == "Menuitem")
		{
			$optionParameter = "";
			$resourceId = JFactory::getApplication()->input->getInt('menuitem', 0);
			$resourceDesc = JText::_("PAYPERDOWNLOADPLUS_MENUITEM_PLUGIN_JOOMLA_MENU_ITEM");
			if($resourceId)
			{
			    $db = JFactory::getDBO();

			    $query = $db->getQuery(true);

			    $query->select($db->quoteName(array('id', 'title')));
			    $query->from($db->quoteName('#__menu'));
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
			$resourceName = JText::_("PAYPERDOWNLOADPLUS_MENUITEM_PLUGIN_JOOMLA_MENU_ITEM");

			$resourceParams = "";
		}
	}

	function onValidateAccess($option, $resources, &$allowAccess, &$requiredLicenses, &$resourcesId)
	{
	    $itemid = JFactory::getApplication()->input->getInt('Itemid', 0);
	    if($itemid == 0)
	        return;

		if(!is_array($requiredLicenses))
			$requiredLicenses = array();
		if(!is_array($resourcesId))
			$resourcesId = array();

		foreach($resources as $resource)
		{
		    if ($resource->resource_type === 'Menuitem') {
    			if($resource->resource_id == $itemid)
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

	function onAjaxCall($plugin, &$output)
	{
	}

	function getReturnPage($option, &$returnPage)
	{
	    $itemid = JFactory::getApplication()->input->getInt('Itemid', 0);
		if($itemid == 0)
			return;

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName('resource_id'));
		$query->from($db->quoteName('#__payperdownloadplus_resource_licenses'));
		$query->where($db->quoteName('resource_option_parameter') . ' = ' . $db->quote(''));
		$query->where($db->quoteName('resource_id') . ' = ' . $itemid);

		$db->setQuery($query);

		try {
		    $itemid = (int)$db->loadResult();
		    if ($itemid) {
		        $query->clear();

		        $query->select($db->quoteName(array('id', 'link')));
		        $query->from($db->quoteName('#__menu'));
		        $query->where($db->quoteName('id') . ' = ' . $itemid);

		        $db->setQuery($query);

	            $menu = $db->loadObject();
	            if($menu)
	                $returnPage = $menu->link;
		    }
		} catch (RuntimeException $e) {
		    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return '';
	}

}
?>