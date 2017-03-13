<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or
die( 'Direct Access to this location is not allowed.' );

class MenuItemVisualDataBind extends VisualDataBind
{
	
	/**
	Class constructor
	$dataField      : The field of the table that will edited
	$displayName  : Text that will show for this element
	$tableName    :  The name of the table where the elements to choose from will be read
	$keyField	   :   The key field on this table
         $displayField   :   The field that will be shown for the list of elements	
	*/
	function __construct($dataField, $displayName)
	{
		parent::__construct($dataField, $displayName);
		$this->useForFilter = false;
		$this->useForTextSearch = false;
		$this->firstItem = JText::_("PAYPERDOWNLOADPLUS_NONE");
	}
	
	/**
	Renders filter controls for this element in list mode
	*/
	function renderFilter($filters)
	{
		return "";
	}
	
	/**
	Renders controls for this element when inserting a new record on the table
	*/
	function renderNew()
	{
		$items = $this->getMenuitems();
		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		$dataField = $this->dataField;
		$disabled = "";
		if($this->disabled)
		{
			$disabled = " disabled=\"true\" ";
			$html .= "<input type=\"hidden\" name=\"$dataField\" id=\"$dataField" . "_hidden\" value=\"" . htmlspecialchars($this->defaultValue) . "\" />";
		}
		$last_menu_type = "";
		$group_open = false;
		$html .= "<select class=\"inputbox\" name=\"$dataField\" id=\"$dataField\" $disabled ".$this->renderHtmlProperties().">";  
		$html .= "<option value=\"0\">".htmlspecialchars($this->firstItem)."</option>";
		foreach($items as $item)
		{	
			if($item->menutype != $last_menu_type)
			{
				if($group_open)
					$html .= "</optgroup>";
				$group_open = true;
				$html .= "<optgroup label=\"" . htmlspecialchars($item->menutype) . "\">";
				$last_menu_type = $item->menutype;
			}
			$selected = "";
			if($this->defaultValue == $item->id)
				$selected = "selected";
			$space = '';
			for($i = 0; $i < $item->depth; $i++)
				$space .= '&nbsp;&nbsp;&nbsp;&nbsp;';
			$html .= "<option value=\"".htmlspecialchars($item->id)."\" $selected>".$space.htmlspecialchars($item->title)."</option>";
		}
		if($group_open)
			echo "</optgroup>";
		$html .= "</select>";
		$html .= "</td></tr>";
		return $html;
	}
	
	/**
	Renders controls for this element when editing a record on the table
	*/
	function renderEdit(&$row)
	{
		$items = $this->getMenuitems();
		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		$dataField = $this->dataField;
		$data = $row->$dataField ;
		$disabled = "";
		if($this->disabled)
		{
			$disabled = " disabled=\"true\" ";
			$html .= "<input type=\"hidden\" name=\"$dataField\" id=\"$dataField" . "_hidden\" value=\"" . htmlspecialchars($this->defaultValue) . "\" />";
		}
		$last_menu_type = "";
		$group_open = false;
		$html .= "<select class=\"inputbox\" name=\"$dataField\" id=\"$dataField\" $disabled ".$this->renderHtmlProperties().">";  
		$html .= "<option value=\"0\">".htmlspecialchars($this->firstItem)."</option>";
		foreach($items as $item)
		{	
			if($item->menutype != $last_menu_type)
			{
				if($group_open)
					$html .= "</optgroup>";
				$group_open = true;
				$html .= "<optgroup label=\"" . htmlspecialchars($item->menutype) . "\">";
				$last_menu_type = $item->menutype;
			}
			$selected = "";
			if($data == $item->id)
				$selected = "selected";
			$space = '';
			for($i = 0; $i < $item->depth; $i++)
				$space .= '&nbsp;&nbsp;&nbsp;&nbsp;';
			$html .= "<option value=\"".htmlspecialchars($item->id)."\" $selected>".$space.htmlspecialchars($item->title)."</option>";
		}
		if($group_open)
			$html .= "</optgroup>";
		$html .= "</select>";
		$html .= "</td></tr>";
		return $html;
	}
	
	/**
	Renders javascript code to validate this control before submitting
	*/
	function renderValidateJavascript()
	{
		$javascript = "";
		return $javascript;
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
		$version = new JVersion;
		if($version->RELEASE == "1.5")
			$query = "SELECT id, name as title, menutype, parent as parent_id FROM #__menu ORDER BY menutype";
		else
			$query = "SELECT id, title, menutype, parent_id FROM #__menu WHERE client_id = 0 ORDER BY menutype";
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$items = $db->loadObjectList();
		$ordered_items = array();
		$this->reorderItems($ordered_items, $items, 0, 0);
		return $ordered_items;
	}
}
?>