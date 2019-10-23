<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

// no direct access
defined ( '_JEXEC' ) or die;

class ExComboVisualDataBind extends VisualDataBind
{
	var $firstItem;
	var $displayField;
	var $tableName;
	var $keyField;

	var $displayField2;
	var $tableName2;
	var $keyField2;
	var $linkField2;
	var $extraSearchField;

	/**
	Class constructor
	$dataField      : The field of the table that will edited
	$displayName  : Text that will show for this element
	$tableName    :  The name of the table where the elements to choose from will be read
	$keyField	   :   The key field on this table
         $displayField   :   The field that will be shown for the list of elements
	*/
	function __construct($dataField, $displayName, $tableName, $keyField, $displayField)
	{
		parent::__construct($dataField, $displayName);
		$this->firstItem = JText::_("PAYPERDOWNLOADPLUS_BOX_SELECT");
		$this->displayField = $displayField;
		$this->tableName = $tableName;
		$this->keyField = $keyField;
		$this->displayField2 = "";
		$this->tableName2 = "";
		$this->keyField2 = "";
		$this->linkField2 = "";
		$this->supportsAjaxCall = true;
		$this->useForFilter = false;
		$this->extraSearchField = "";
	}

	function setExtraSearchField($extraSearchField)
	{
		$this->extraSearchField = $extraSearchField;
	}

	function setSecondTable($tableName, $keyField, $displayField, $linkField2)
	{
		$this->displayField2 = $displayField;
		$this->tableName2 = $tableName;
		$this->keyField2 = $keyField;
		$this->linkField2 = $linkField2;
	}

	/**
	Returns the piece of query that will be included in the select to load all elements
	*/
	function getSelectField()
	{
		if($this->tableName2 != "")
			return $this->tableName2 . "." . $this->displayField2;
		else if($this->tableName != "")
			return $this->tableName . "." . $this->displayField;
		else
			return parent::getSelectField();
	}

	/**
	Returns a piece of query that will go to the where clause to filter the elements base on the text entered on the search input
	*/
	function getSearchCondition($text)
	{
		if($this->tableName != "")
		{
			$db = JFactory::getDBO();
			$sql = "";
			if($this->tableName && $this->displayField)
				$sql .= $this->tableName . "." . $this->displayField . " LIKE '%" . $db->escape($text) . "%'";
			if($this->tableName2 && $this->displayField2)
			{
				if(!$sql)
					$sql .= " OR ";
				$sql .= $this->tableName2 . "." . $this->displayField2 . " LIKE '%" . $db->escape($text) . "%'";
			}
			if($sql)
				return "(" . $sql . ")";
			else
				return "";
		}
		else
			return parent::getSearchCondition($text);
	}

	/**
	Returns a piece of query that will go to the where clause to filter the elements base on the value of the filter controls
	*/
	function getFilterCondition($filters)
	{
		if($filters[$this->dataField . '_search_control'])
		{
			$db = JFactory::getDBO();
			return $this->sourceTable . "." . $this->dataField . " = '" .
				$db->escape($filters[$this->dataField . '_search_control']) . "'";
		}
		return "";
	}

	/**
	Executed before storing data
	*/
	function onBeforeStore(&$row)
	{
		if($this->ignoreToBind)
			return true;
		$dataField = $this->dataField;
		if($row->$dataField == "")
			$row->$dataField = null;
		return parent::onBeforeStore($row);
	}

	/**
	Returns an extra table required for the query
	*/
	function getExtraSelectTable()
	{
		$sql = "";
		if($this->tableName != "")
			$sql = "LEFT JOIN " . $this->tableName . " ON " . $this->sourceTable . "." . $this->dataField . " = " .
				$this->tableName . "." . $this->keyField;
		if($this->tableName2 != "")
			$sql .= "LEFT JOIN " . $this->tableName2 . " ON " . $this->tableName . "." . $this->linkField2 . " = " .
				$this->tableName2 . "." . $this->keyField2;
		return $sql;
	}

	/**
	Sets the first item for the select tag. If not set it will be '--Select--'
	*/
	function setFirstItem($firstItem)
	{
		$this->firstItem = $firstItem;
	}

	/**
	Renders the heading on the table for the column assigned to this element
	*/
	function renderColumnHeading($filters, $rows)
	{
		if($this->displayField != "")
			$orderField = $this->displayField;
		else
			$orderField = $this->dataField;
	?>
		<th class="nowrap center hidden-phone" width="<?php echo htmlspecialchars($this->columnWidth); ?>%">
		<?php echo JHTML::_('grid.sort',  htmlspecialchars($this->displayName), htmlspecialchars($orderField), @$filters['order_Dir'], @$filters['order'] ); ?>
		</th>
	<?php
	}

	/**
	Returns field use for ordering
	*/
	function getOrderField()
	{
		if($this->displayField != "")
			$orderField = $this->displayField;
		else
			$orderField = $this->dataField;
		$field = new stdClass();
		$field->fieldName = $orderField;
		$field->display = $this->displayName;
		return $field;
	}

	/**
	Returns the data for the current row of this element
	*/
	function getGridData(&$row, $value)
	{
		$field = $this->displayField;
		$data = $row->$field;
		return $data;
	}

	/**
	Returns the data for the current row of this element
	*/
	function getData($row)
	{
		if(isset($this->dataField) && isset($row->{$this->dataField}))
			return $this->getGridData($row, $row->{$this->dataField});
		else
			return $this->getGridData($row, null);
	}

	/**
	Renders filter controls for this element in list mode
	*/
	function renderFilter($filters)
	{
		return "";
	}

	function getDefaultDisplay($key_value)
	{
		$db = JFactory::getDBO();
		$key_value = $db->escape($key_value);
		if(!$this->tableName2)
			$query = "SELECT {$this->keyField}, {$this->displayField} as display FROM {$this->tableName} WHERE {$this->keyField} = '$key_value'";
		else
			$query = "SELECT {$this->tableName}.{$this->keyField}, CONCAT({$this->tableName}.{$this->displayField}, ' ', {$this->tableName2}.{$this->displayField2}) as display
				FROM {$this->tableName}
				LEFT JOIN {$this->tableName2}
				ON {$this->tableName}.{$this->linkField2} = {$this->tableName2}.{$this->keyField2}
				WHERE {$this->tableName}.{$this->keyField} = '$key_value'";
		$db->setQuery($query);
		$object = $db->loadObject();
		if(isset($object) && $object != null)
			return $object->display;
		else
			return "";
	}

	/**
	Renders controls for this element when inserting a new record on the table
	*/
	function renderNew()
	{
	    $option = JFactory::getApplication()->input->get("option");

		echo "<script type=\"text/javascript\">";
		echo "var cancel_text='" . JText::_("PAYPERDOWNLOADPLUS_CANCEL", true) . "';";
		echo "</script>";

		$scriptPath = "administrator/components/$option/js/";
		JHTML::script($scriptPath . "ajax_source.js", false);
		JHTML::script($scriptPath . "excombo.js", false);

		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		$dataField = $this->dataField;
		$disabled = "";
		if($this->disabled)
		{
			$disabled = " disabled=\"true\" ";
		}
		$defaultDisplay = "";
		if($this->defaultValue)
			$defaultDisplay = $this->getDefaultDisplay($this->defaultValue);
		$html .= "<input type=\"hidden\" name=\"$dataField\" id=\"$dataField\" value=\"" . htmlspecialchars($this->defaultValue) . "\" />";
		$html .= "<div id=\"$dataField" . "_show\">" . htmlspecialchars($defaultDisplay) . "</div>";
		if(!$this->disabled)
		{
			$html .= "<input type=\"text\" name=\"$dataField" . "_search\" id=\"$dataField" . "_search\"
				value=\"\" " .
					$disabled . " />";
			$html .= "<input type=\"button\" name=\"$dataField" . "_btn\" id=\"$dataField" . "_btn\" value=\"" . JText::_("PAYPERDOWNLOADPLUS_SEARCH") . "\" " .
				"onclick=\"excombo_search('" . addslashes($dataField) . "');\"" .
				$disabled . "/>";
			$html .= "<div id=\"$dataField" . "_values\" style=\"position:absolute;visibility:hidden;z-index:1000;border-width:1px;border-style:solid;background-color:#ffffff;\"></div>";
		}
		$html .= "</td></tr>";
		return $html;
	}

	/**
	Renders controls for this element when editing a record on the table
	*/
	function renderEdit(&$row)
	{
	    $option = JFactory::getApplication()->input->get("option");

		echo "<script type=\"text/javascript\">";
		echo "var cancel_text='" . JText::_("PAYPERDOWNLOADPLUS_CANCEL", true) . "';";
		echo "</script>";

		$scriptPath = "administrator/components/$option/js/";
		JHTML::script($scriptPath . "ajax_source.js", false);
		JHTML::script($scriptPath . "excombo.js", false);

		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		$dataField = $this->dataField;
		$data = $row->$dataField ;
		$disabled = "";
		if($this->disabled || $this->disabledEdit)
		{
			$disabled = " disabled=\"true\" ";
		}
		$dataDisplay = "";
		if($data)
			$dataDisplay = $this->getDefaultDisplay($data);
		$html .= "<input type=\"hidden\" name=\"$dataField\" id=\"$dataField\" value=\"" . htmlspecialchars($data) . "\" />";
		$html .= "<div id=\"$dataField" . "_show\">" . htmlspecialchars($dataDisplay) . "</div>";
		if(!$this->disabled && !$this->disabledEdit)
		{
			$html .= "<input type=\"text\" name=\"$dataField" . "_search\" id=\"$dataField" . "_search\"
				value=\"\" " .
					$disabled . " />";
			$html .= "<input type=\"button\" name=\"$dataField" . "_btn\" id=\"$dataField" . "_btn\" value=\"" .
				htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_SEARCH")) . "\" " .
				"onclick=\"excombo_search('" . addslashes($dataField) . "');\"" .
				$disabled . "/>";
			$html .= "<div id=\"$dataField" . "_values\" style=\"position:absolute;visibility:hidden;z-index:1000;border-width:1px;border-style:solid;background-color:#ffffff;\"></div>";
		}
		$html .= "</td></tr>";
		return $html;
	}

	/**
	Validates the data supplied before storing in the database
	*/
	function check(&$row)
	{
		$data = $row->{$this->dataField};
		if(!$this->allowBlank && $data == "")
		{
			$row->setError(JText::_("PAYPERDOWNLOADPLUS_YOU_MUST_SELECT_THIS_FIELD") . $this->displayName);
			return false;
		}
		return true;
	}

	/**
	Renders javascript code to validate this control before submitting
	*/
	function renderValidateJavascript()
	{
		$javascript = "";
		$dataField = $this->dataField;
		$javascript .= "var $dataField = document.getElementById('$dataField');\n";
		if(!$this->allowBlank)
		{
			$displayName = addslashes($this->displayName);
			$errorText = JText::_("PAYPERDOWNLOADPLUS_YOU_MUST_SELECT_THIS_FIELD", true) . $displayName;
			$javascript .= "var errmsg$dataField = '$errorText';\n";
			$javascript .= "if($dataField.value == '')\n";
			$javascript .= "{\n";
			$javascript .= "alert(errmsg$dataField);\n";
			$javascript .= "return false;\n";
			$javascript .= "}\n";
		}
		if($this->extraValidateScript)
			$javascript .= $this->extraValidateScript;
		return $javascript;
	}

	function getSourceItems($filter = "")
	{
		$db = JFactory::getDBO();
		$filter = $db->escape($filter);

		if(!$this->tableName2)
		{
			$query = "SELECT {$this->keyField} as value, {$this->displayField} as display
				FROM {$this->tableName} WHERE {$this->displayField} LIKE '%" . $filter . "%'";
		}
		else
		{
			$query = "SELECT {$this->tableName}.{$this->keyField} as value,
				CONCAT({$this->tableName}.{$this->displayField}, ' ', {$this->tableName2}.{$this->displayField2}) as display
				FROM {$this->tableName}
				LEFT JOIN {$this->tableName2}
				ON {$this->tableName}.{$this->linkField2} = {$this->tableName2}.{$this->keyField2}
				WHERE {$this->tableName}.{$this->displayField} LIKE '%" . $filter . "%'
					OR {$this->tableName2}.{$this->displayField2} LIKE '%" . $filter . "%'";
		}
		if($this->extraSearchField)
		{
			$query .= " OR " . $this->tableName . "." . $this->extraSearchField . " LIKE '%" . $filter . "%'";
		}
		$query .= " LIMIT 10";
		$db->setQuery($query);
		$items = $db->loadObjectList();
		return $items;
	}

	function renderAjaxGetItems($filter)
	{
		$items = $this->getSourceItems($filter);
		echo "<<1>" . htmlspecialchars($this->dataField);
		foreach($items as $item)
		{
			echo ">" . htmlspecialchars($item->value) . "<" . htmlspecialchars($item->display);
		}
		echo ">>";
	}

	function ajaxCall()
	{
	    $jinput = JFactory::getApplication()->input;

	    $dataField = $jinput->getRaw("v");
		if($dataField == $this->dataField)
		{
		    $this->renderAjaxGetItems($jinput->getRaw("x"));
		}
	}
}

?>