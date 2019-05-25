<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

// no direct access
defined ( '_JEXEC' ) or die;

class MultipleJoomlaGroupsSelect extends VisualDataBind
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
		$html .= "<input type=\"hidden\" name=\"$dataField" . "_count\" value=\"" . count($groups) . "\"/><br/>";
		$html .= "<table>";
		foreach($groups as $group)
		{
			$html .= "<tr><td>";

			$html .= "<input type=\"checkbox\" name=\"$dataField" . "_" . $i ."\" value=\"1\"/>";
			$html .= "</td><td>";
			for($s = 0; $s < $group->depth; $s++)
				$html .= "&nbsp;&nbsp;&nbsp;&nbsp;";
			$html .= htmlspecialchars($group->title);
			$html .= "<input type=\"hidden\" name=\"$dataField" . "_id_" . $i ."\" value=\"" . $group->id . "\"/><br/>";
			$i++;
			$html .= "</td></tr>";
		}

		$html .= "</table>";
		return $html;
	}

	function renderEdit(&$row)
	{
		JHTML::_('behavior.calendar');
		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		$dataField = $this->dataField;
		$data = $row->$dataField;
		$ids = explode(",", $data);
		$groups = $this->getGroups();
		$i = 0;
		$html .= "<input type=\"hidden\" name=\"$dataField" . "_count\" value=\"" . count($groups) . "\"/><br/>";
		$html .= "<table>";
		foreach($groups as $group)
		{
			$html .= "<tr><td valign=\"bottom\">";
			$checked = "";
			if(array_search($group->id, $ids) !== false)
				$checked = " checked ";
			$html .= "<input type=\"checkbox\" name=\"$dataField" . "_" . $i ."\" $checked/>";
			$html .= "</td><td>";
			for($s = 0; $s < $group->depth; $s++)
				$html .= "&nbsp;&nbsp;&nbsp;&nbsp;";
			$html .= htmlspecialchars($group->title);
			$html .= "<input type=\"hidden\" name=\"$dataField" . "_id_" . $i ."\" value=\"" . $group->id . "\"/><br/>";
			$html .= "</td></tr>";
			$i++;
		}
		$html .= "</table>";
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
		$this->reorder($groups_ordered, $groups, 0, 0);
		return $groups_ordered;
	}

	function onBeforeStore(&$row)
	{
	    $jinput = JFactory::getApplication()->input;

		if($this->ignoreToBind)
			return true;
		$result = array();
		$count = $jinput->getInt($this->dataField . "_count");
		for($i = 0 ; $i < $count; $i++)
		{
		    if ($jinput->getString($this->dataField . "_" . $i))
			{
			    $result[] = $jinput->getInt($this->dataField . "_id_" . $i);
			}
		}
		$row->{$this->dataField} = implode(",", $result);
		return true;
	}

	function renderValidateJavascript()
	{
		return "";
	}
}