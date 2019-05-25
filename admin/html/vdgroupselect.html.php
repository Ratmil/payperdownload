<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

// no direct access
defined ( '_JEXEC' ) or die;

class JoomlaGroupSelect extends VisualDataBind
{
	function __construct($dataField, $displayName)
	{
		parent::__construct($dataField, $displayName);
	}

	function renderNew()
	{
		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		$dataField = $this->dataField;
		$groups = $this->getGroups();
		$i = 0;
		$disabled = "";
		if($this->disabled)
		{
			$disabled = " disabled=\"true\" ";
			$html .= "<input type=\"hidden\" name=\"$dataField\" id=\"$dataField" . "_hidden\" value=\"" . htmlspecialchars($this->defaultValue) . "\" />";
		}
		$html .= "<select class=\"inputbox\" name=\"$dataField\" id=\"$dataField\" $disabled ".$this->renderHtmlProperties().">";
		if($this->firstItem)
			$html .= "<option value=\"\">".htmlspecialchars($this->firstItem)."</option>";
		foreach($groups as $group)
		{
			$selected = "";
			if($this->defaultValue == $group->id)
				$selected = "selected";
			$space = "";
			for($s = 0; $s < $group->depth; $s++)
				$space .= "&nbsp;&nbsp;&nbsp;&nbsp;";
			$html .= "<option value=\"".htmlspecialchars($group->id)."\" $selected>".$space.htmlspecialchars($group->title)."</option>";
		}

		$html .= "</select>";
		$html .= "</td></tr>";
		return $html;
	}

	function renderEdit(&$row)
	{
		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		$dataField = $this->dataField;
		$groups = $this->getGroups();
		$i = 0;
		$disabled = "";
		$data = $row->$dataField ;
		if($this->disabled)
		{
			$disabled = " disabled=\"true\" ";
			$html .= "<input type=\"hidden\" name=\"$dataField\" id=\"$dataField" . "_hidden\" value=\"" . htmlspecialchars($this->defaultValue) . "\" />";
		}
		$html .= "<select class=\"inputbox\" name=\"$dataField\" id=\"$dataField\" $disabled ".$this->renderHtmlProperties().">";
		if($this->firstItem)
			$html .= "<option value=\"\">".htmlspecialchars($this->firstItem)."</option>";
		foreach($groups as $group)
		{
			$selected = "";
			if($data == $group->id)
				$selected = "selected";
			$space = "";
			for($s = 0; $s < $group->depth; $s++)
				$space .= "&nbsp;&nbsp;&nbsp;&nbsp;";
			$html .= "<option value=\"".htmlspecialchars($group->id)."\" $selected>".$space.htmlspecialchars($group->title)."</option>";
		}

		$html .= "</select>";
		$html .= "</td></tr>";
		return $html;
	}

	function reorder(&$items_ordered, $items, $parent_id, $depth)
	{
		$count = count($items);
		for($i = 0; $i < $count; $i++)
		{
			$item = $items[$i];
			if($item->parent_id == $parent_id)
			{
				$item->depth = $depth;
				$items_ordered[] = $item;
				$this->reorder($items_ordered, $items, $item->id, $depth + 1);
			}
		}
	}

	function getGroups()
	{
		$query = "SELECT id, parent_id, title FROM #__usergroups";
		$db = JFactory::getDBO();
		$db->setQuery( $query );
		$groups = $db->loadObjectList();
		$groups_ordered = array();
		$this->reorder($groups_ordered, $groups, 2, 0);
		return $groups_ordered;
	}

	function renderValidateJavascript()
	{
		return "";
	}
}