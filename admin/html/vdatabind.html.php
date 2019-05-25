<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

// no direct access
defined ( '_JEXEC' ) or die;

/**
This class represents an element on the admin form.
An object of this class defines how a table field will be listed and edited.
The base class will use an input control of type text to edit elements.
*/
class VisualDataBind extends JObject
{
	var $dataField;
	var $displayName;
	var $size;
	var $maxLength;
	var $minLength;
	var $allowBlank;
	var $regExp;
	var $lines;
	var $isEditLink;
	var $columnWidth;
	var $maxGridCellLength;
	var $showInGrid;
	var $showInEditForm;
	var $showInInsertForm;
	var $editLinkText;
	var $sourceTable;
	var $ignoreToSelect;
	var $ignoreToBind;
	var $htmlProperties;
	var $onRenderJavascriptRoutine;
	var $defaultValue;
	var $disabled;
	var $disabledEdit;
	var $gridToolTip;
	var $editToolTip;
	var $editRemarkToolTip;
	var $useForTextSearch;
	var $useForFilter;
	var $requiredMark;
	var $linkTask;
	var $extraValidateScript;

	/**
	Class constructor
	$dataField      : The field of the table that will edited
	$displayName  : Text that will show for this element
	*/
	function __construct($dataField, $displayName)
	{
		$this->dataField = $dataField;
		$this->displayName = $displayName;
		$this->size = 40;
		$this->maxLength = 0;
		$this->minLength = 0;
		$this->lines = 1;
		$this->regExp = null;
		$this->isEditLink = false;
		$this->columnWidth = 30;
		$this->maxGridCellLength = 0;
		$this->showInGrid = true;
		$this->editLinkText = JText::_("PAYPERDOWNLOADPLUS_EDIT_31");
		$this->ignoreToSelect = false;
		$this->ignoreToBind = false;
		$this->sourceTable = "";
		$this->allowBlank = false;
		$this->htmlProperties = null;
		$this->onRenderJavascriptRoutine = null;
		$this->defaultValue = null;
		$this->disabled = false;
		$this->disabledEdit = false;
		$this->showInEditForm = true;
		$this->showInInsertForm = true;
		$this->gridToolTip = null;
		$this->editToolTip = null;
		$this->useForTextSearch = true;
		$this->useForFilter = false;
		$this->editRemarkToolTip = "";
		$this->linkTask = "edit";
		$this->requiredWord = JText::_('PAYPERDOWNLOADPLUS_REQUIRED_32');
		$this->requiredMark = "&nbsp;&nbsp;<font color=\"#ff0000\" size=\"2\">*</font>";
	}

	/**
	Returns the table field this elements gets its data from
	*/
	function getDataField()
	{
		return $this->dataField;
	}

	/**
	Returns if this element is an aggregate, a sum or something like it.
	*/
	function isAggregate()
	{
		return false;
	}

	/**
	Returns the piece of query that will be included in the select to load all elements
	*/
	function getSelectField()
	{
		return $this->sourceTable . "." . $this->dataField;
	}

	/**
	Returns an extra table required for the query
	*/
	function getExtraSelectTable()
	{
		return "";
	}

	/**
	Returns a piece of query that will go to the where clause to filter the elements base on the text entered on the search input
	*/
	function getSearchCondition($text)
	{
		$db = JFactory::getDBO();
		return $this->sourceTable . "." . $this->dataField . " LIKE '%" . $db->escape($text) . "%'";
	}

	/**
	Returns a piece of query that will go to the where clause to filter the elements base on the value of the filter controls
	*/
	function getFilterCondition($filters)
	{
		return "";
	}

	/**
	Sets the width of the column (in percent units) for this element
	*/
	function setColumnWidth($columnWidth)
	{
		$this->columnWidth = $columnWidth;
	}

	/**
	Sets if this element will have an 'a' tag (link)
	*/
	function setEditLink($isEditLink)
	{
		$this->isEditLink = $isEditLink;
	}

	/**
	Sets the max length for the input control
	*/
	function setMaxLength($maxLength)
	{
		$this->maxLength = $maxLength;
	}

	/**
	Sets the min length for the input control
	*/
	function setMinLength($minLength)
	{
		$this->minLength = $minLength;
	}

	/**
	Sets regular expression that will be used to validate the data supplied, in javascript (client side) and PHP (server side)
	*/
	function setRegExp($regExp)
	{
		$this->regExp = $regExp;
	}

	/**
	If the value supplied is greater that one the input control will be multiline (textarea).
	*/
	function setLineCount($lines)
	{
		$this->lines = $lines;
	}

	/**
	Sets the table where data will be read from
	*/
	function setSourceTable($sourceTable)
	{
		$this->sourceTable = $sourceTable;
	}

	/**
	Returns the source table
	*/
	function getSourceTable()
	{
		return $this->sourceTable;
	}

	/**
	Executed after data has been loaded
	*/
	function onAfterLoad(&$row)
	{
	}

	/**
	Executed after the property on the JTable object has been created
	*/
	function onAfterCreateProperty(&$row)
	{
	}

	/**
	Executed before storing data
	*/
	function onBeforeStore(&$row)
	{
		return true;
	}

	/**
	Executed after storing data
	*/
	function onAfterStore(&$row)
	{
		return true;
	}

	/**
	Executed before deleting data
	*/
	function onBeforeDelete($row, $id)
	{
		return true;
	}

	/**
	Executed after deleting data
	*/
	function onAfterDelete($row, $id)
	{
		return true;
	}

	/**
	Executed after binding
	*/
	function onAfterBind(&$row, $from)
	{
		return true;
	}

	/**
	Returns field use for ordering
	*/
	function getOrderField()
	{
		$field = new stdClass();
		$field->fieldName = $this->dataField;
		$field->display = $this->displayName;
		return $field;
	}

	/**
	Renders the heading on the table for the column assigned to this element
	*/
	function renderColumnHeading($filters, $rows)
	{
	?>
		<th class="nowrap center hidden-phone" width="<?php echo htmlspecialchars($this->columnWidth); ?>%">
		<?php echo JHTML::_('grid.sort',  htmlspecialchars($this->displayName), htmlspecialchars($this->dataField), @$filters['order_Dir'], @$filters['order'] ); ?>
		</th>
	<?php
	}

	/**
	Returns the data for the current row of this element
	*/
	function getData($row)
	{
		return $row->{$this->dataField};
	}

	/**
	Renders the cell of the table for this element
	*/
	function renderGridCell(&$row, $rowNumber, $columnNumber, $columnCount)
	{
		$field = $this->dataField;
		$data = $this->getData($row);
		if(is_numeric($this->maxGridCellLength) && $this->maxGridCellLength > 0 && strlen($data) > $this->maxGridCellLength)
			$data = substr($data, 0, $this->maxGridCellLength) . "...";
		if($this->isEditLink == 1)
		{
			$gridToolTip = $this->gridToolTip;
			if(!$gridToolTip)
				$gridToolTip = JText::_("PAYPERDOWNLOADPLUS_CLICK_TO_EDIT_33") . "::" . $data;
			echo "<span class=\"editlinktip hasTip\" title=\"".htmlspecialchars($gridToolTip)."\">";
			echo "<a href=\"javascript:void(0);\" onclick=\"return listItemTask('cb$rowNumber','{$this->linkTask}')\">";
			echo htmlspecialchars($data);
			echo "</a>";
			echo "</span>";
		}
		else
		{
			echo htmlspecialchars($data);
		}
	}

	/**
	Renders extra html properties in edit or insert mode
	*/
	function renderHtmlProperties()
	{
		$html = "";
		if($this->htmlProperties != null)
		{
			foreach($this->htmlProperties as $htmlProperty => $htmlPropertyValue)
			{
				$html .= htmlspecialchars($htmlProperty) . " = \"" . htmlspecialchars($htmlPropertyValue) . "\" ";
			}
		}
		return $html;
	}

	/**
	Renders the label when editing this field
	*/
	function renderFieldLabel()
	{
		//<li><label id="jform_name-lbl" for="jform_name" class="hasTip required" title="Name::Enter a name for the banner">Name<span class="star">&#160;*</span></label><input type="text" name="jform[name]" id="jform_name" value="Shop 1" class="inputbox required" size="40"/></li>

		$html = "<td width=\"200\" align=\"left\" valign=\"top\" class=\"key\">";
		if($this->editToolTip)
		{
			$html .= "<span class=\"editlinktip hasTip\"
				title=\"" .
					htmlspecialchars($this->displayName) . "::" .
					htmlspecialchars($this->editToolTip) .
					((!$this->allowBlank)?("<br/><font color=#ff0000>".htmlspecialchars($this->requiredWord)."</font>"):"").
					(($this->editRemarkToolTip)?("<br/><font color=#0000ff>".htmlspecialchars($this->editRemarkToolTip)."</font>"):"").
					"\">";
		}
		$html .= htmlspecialchars($this->displayName);
		if(!$this->allowBlank && !$this->disabled)
		{
			$html .= $this->requiredMark;
		}
		if($this->editToolTip)
		{
			$html .= "</span>";
		}
		$html .= "</td>";
		return $html;
	}

	/**
	Renders controls for this element when inserting a new record on the table
	*/
	function renderNew()
	{
		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		$dataField = $this->dataField;
		$size = $this->size;
		$maxLength = "";
		if($this->maxLength > 0)
		{
			$maxLength = $this->maxLength;
		}
		$disabled = "";
		if($this->disabled)
		{
			$disabled = " disabled=\"true\" ";
			$html .=  "<input type=\"hidden\" name=\"$dataField\" id=\"$dataField" . "_hidden\" value=\"" . htmlspecialchars($this->defaultValue) . "\" />";
		}
		if($this->lines <= 1)
			$html .= "<input class=\"text_area\" type=\"text\" name=\"$dataField\" id=\"$dataField\" size=\"$size\" maxlength=\"$maxLength\" value=\"" .
				htmlspecialchars($this->defaultValue) . "\" ".$this->renderHtmlProperties()." $disabled/>";
		else
			$html .= "<textarea name=\"$dataField\" id=\"$dataField\" rows=\"".$this->lines."\" cols=\"$size\" ".$this->renderHtmlProperties()." $disabled>" .
				htmlspecialchars($this->defaultValue). "</textarea>";
		$html .= "</td></tr>";
		return $html;
	}

	/**
	Renders controls for this element when editing a record on the table
	*/
	function renderEdit(&$row)
	{
		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		$dataField = $this->dataField;
		$data = htmlspecialchars($this->getData($row));
		$size = $this->size;
		$maxLength = "";
		if($this->maxLength > 0)
		{
			$maxLength = $this->maxLength;
		}
		$disabled = "";
		if($this->disabled || $this->disabledEdit)
		{
			$disabled = " disabled=\"true\" ";
			$html .=  "<input type=\"hidden\" name=\"$dataField\" id=\"$dataField" . "_hidden\" value=\"$data\" />";
		}
		if($this->lines <= 1)
			$html .= "<input class=\"text_area\" type=\"text\" name=\"$dataField\" id=\"$dataField\" size=\"$size\" maxlength=\"$maxLength\" value=\"$data\" $disabled " .$this->renderHtmlProperties()."/>";
		else
			$html .= "<textarea name=\"$dataField\" id=\"$dataField\" rows=\"".$this->lines."\" cols=\"$size\" $disabled ".$this->renderHtmlProperties().">$data</textarea>";
		$html .= "</td></tr>";
		return $html;
	}

	/**
	Renders filter controls for this element in list mode
	*/
	function renderFilter($filters)
	{
		return "";
	}

	/**
	Renders javascript code to reset the filter controls
	*/
	function renderResetFilter($filters)
	{
		return "";
	}

	/**
	Returns a name for a filter control
	*/
	function getFilterName()
	{
		return $this->dataField . '_search_control';
	}

	/**
	Validates the data supplied before storing in the database
	*/
	function check(&$row)
	{
		$data = $row->{$this->dataField};
		if(!$this->allowBlank && $data == "")
		{
			$row->setError(JText::_("PAYPERDOWNLOADPLUS_FIELD_CANT_BE_LEFT_EMPTY__34") . $this->displayName);
			return false;
		}
		if($this->minLength > 0 && strlen($data) < $this->minLength)
		{
			$row->setError(JText::_("PAYPERDOWNLOADPLUS_FIELD_LENGTH_IS_NO_VALID__35") . $this->displayName);
			return false;
		}
		if($this->allowBlank && $data == "")
		{
			return true;
		}
		if($this->regExp != null && preg_match("/^{$this->regExp}$/", $data) == 0)
		{
			$row->setError(JText::_("PAYPERDOWNLOADPLUS_THE_VALUE_FOR_THIS_FIELD_IS_NOT_CORRECT__36") . $this->displayName);
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
		$displayName = addslashes($this->displayName);
		$javascript .= "var var_$dataField = document.getElementById('$dataField');\n";
		if($this->minLength > 0 || $this->regExp != null || !$this->allowBlank)
		{
			$errorText2 = JText::_("PAYPERDOWNLOADPLUS_FIELD_CANT_BE_LEFT_EMPTY__37", true) . $displayName;
			$javascript .= "var errmsg2$dataField = '$errorText2';\n";
			if(!$this->allowBlank)
			{
				$javascript .= "if(var_$dataField.value.length == 0)\n";
				$javascript .= "{\n";
				$javascript .= "alert(errmsg2$dataField);\n";
				$javascript .= "return false;\n";
				$javascript .= "}\n";
			}
			if($this->minLength > 0)
			{
				$errorText1 = JText::_("PAYPERDOWNLOADPLUS_FIELD_LENGTH_IS_NO_VALID__38", true) . $displayName;
				$javascript .= "var errmsg1$dataField = '$errorText1';\n";
				$javascript .= "if(var_$dataField.value.length < " . $this->minLength. ")\n";
				$javascript .= "{\n";
				if($this->minLength > 1)
					$javascript .= "alert(errmsg1$dataField);\n";
				else
					$javascript .= "alert(errmsg2$dataField);\n";
				$javascript .= "return false;\n";
				$javascript .= "}\n";
			}
			$allowBlankCond = "";
			if($this->allowBlank)
			{
				$allowBlankCond = "var_$dataField.value.length > 0 &&";
			}
			if($this->regExp != null)
			{
				$errorText3 = JText::_("PAYPERDOWNLOADPLUS_THE_VALUE_FOR_THIS_FIELD_IS_NOT_CORRECT__39", true) . $displayName;
				$javascript .= "var errmsg3$dataField = '$errorText3';\n";
				$javascript .= "var regExp$dataField = /^".$this->regExp."$/;\n";
				$javascript .= "if($allowBlankCond !regExp$dataField.test(var_$dataField.value))\n";
				$javascript .= "{\n";
				$javascript .= "alert(errmsg3$dataField);\n";
				$javascript .= "return false;\n";
				$javascript .= "}\n";
			}
		}
		if($this->extraValidateScript)
			$javascript .= $this->extraValidateScript;
		return $javascript;
	}

	/**
	Sets the tooltip to show in list mode
	*/
	function setGridToolTip($gridToolTip)
	{
		$this->gridToolTip = $gridToolTip;
	}

	/**
	Sets the tooltip to show in edit or insert mode
	*/
	function setEditToolTip($editToolTip)
	{
		$this->editToolTip = $editToolTip;
	}

	/**
	Handles an ajax call
	*/
	function ajaxCall()
	{
		return "";
	}


	function renderEditHeading()
	{
	}

	function renderAddNewHeading()
	{
	}
}

/**
This class is used to edit data using a select html element.
The data for this field will be chosen from a list of elements
*/
class ComboVisualDataBind extends VisualDataBind
{
	var $itemsQuery;
	var $firstItem;
	var $items;
	var $displayField;
	var $tableName;
	var $keyField;

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
		$this->firstItem = JText::_("PAYPERDOWNLOADPLUS_SELECT_40");
		$this->displayField = $displayField;
		$this->tableName = $tableName;
		$this->keyField = $keyField;
		$this->useForFilter = true;
		if($this->tableName != "")
			$this->itemsQuery = "SELECT $keyField as value, $displayField as display FROM $tableName ORDER BY $displayField";
		else
			$this->itemsQuery = "";
		$this->items = array();
	}

	/**
	Returns the piece of query that will be included in the select to load all elements
	*/
	function getSelectField()
	{
		if($this->tableName != "")
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
			return $this->tableName . "." . $this->displayField . " LIKE '%" . $db->escape($text) . "%'";
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
		if($this->tableName != "")
			return "LEFT JOIN " . $this->tableName . " ON " . $this->sourceTable . "." . $this->dataField . " = " .
				$this->tableName . "." . $this->keyField;
		else
			return "";
	}

	/**
	Sets the first item for the select tag. If not set it will be '--Select--'
	*/
	function setFirstItem($firstItem)
	{
		$this->firstItem = $firstItem;
	}

	/**
	Sets a query for the elements to show, the parameters tablename, keyfield and displayname are ignored.
	The query must returns fields with names 'value' and 'data'
	*/
	function setItemsQuery($itemsQuery)
	{
		$this->itemsQuery = $itemsQuery;
	}

	/**
	Adds an item to the list of elements to show. They don't necessarily need to be loaded from a table.
	*/
	function addItem($itemValue, $itemDisplay)
	{
		$item = new stdClass();
		$item->value = $itemValue;
		$item->display = $itemDisplay;
		$this->items[] = $item;
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
	Returns the list of items to show
	*/
	function getItems()
	{
		if($this->itemsQuery != "")
		{
			$db = JFactory::getDBO();
			$db->setQuery($this->itemsQuery);
			$rows = $db->loadObjectList();
			return $rows;
		}
		else
			return $this->items;
	}

	/**
	Returns the data for the current row of this element
	*/
	function getGridData(&$row, $value)
	{
		if($this->itemsQuery != "")
		{
			$field = $this->displayField;
			$data = $row->$field;
		}
		else
		{
			foreach($this->items as $item)
			{
				if($item->value == $value)
					$data = $item->display;
			}
		}
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
		$dataField = $this->dataField . '_search_control';
		$data = $filters[$dataField];
		$items = $this->getItems();
		$html = "<select class=\"inputbox\" name=\"$dataField\" id=\"$dataField\" onchange=\"this.form.submit();\">";
		$html .= "<option value=\"\">--&nbsp;&nbsp;".htmlspecialchars($this->displayName)."&nbsp;&nbsp;--</option>";
		foreach($items as $item)
		{
			$selected = "";
			if($data == $item->value)
				$selected = "selected";
			$html .= "<option value=\"".htmlspecialchars($item->value)."\" $selected>".htmlspecialchars($item->display)."</option>";
		}
		$html .= "</select>";
		return $html;
	}

	/**
	Renders javascript code to reset the filter controls
	*/
	function renderResetFilter($filters)
	{
		return "document.getElementById('" . $this->dataField . '_search_control' . "').value = '';";
	}

	/**
	Renders controls for this element when inserting a new record on the table
	*/
	function renderNew()
	{
		$items = $this->getItems();
		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		$dataField = $this->dataField;
		$disabled = "";
		if($this->disabled)
		{
			$disabled = " disabled=\"true\" ";
			$html .= "<input type=\"hidden\" name=\"$dataField\" id=\"$dataField" . "_hidden\" value=\"" . htmlspecialchars($this->defaultValue) . "\" />";
		}
		$html .= "<select class=\"inputbox\" name=\"$dataField\" id=\"$dataField\" $disabled ".$this->renderHtmlProperties().">";
		$html .= "<option value=\"\">".htmlspecialchars($this->firstItem)."</option>";
		foreach($items as $item)
		{
			$selected = "";
			if($this->defaultValue == $item->value)
				$selected = "selected";
			$html .= "<option value=\"".htmlspecialchars($item->value)."\" $selected>".htmlspecialchars($item->display)."</option>";
		}
		$html .= "</select>";
		$html .= "</td></tr>";
		return $html;
	}

	/**
	Renders controls for this element when editing a record on the table
	*/
	function renderEdit(&$row)
	{
		$items = $this->getItems();
		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		$dataField = $this->dataField;
		$data = $row->$dataField ;
		$disabled = "";
		if($this->disabled || $this->disabledEdit)
		{
			$disabled = " disabled=\"true\" ";
			$html .=  "<input type=\"hidden\" name=\"$dataField\" id=\"$dataField" . "_hidden\" value=\"" . htmlspecialchars($data) . "\" />";
		}
		$html .= "<select class=\"inputbox\" name=\"$dataField\" id=\"$dataField\" $disabled ".$this->renderHtmlProperties().">";
		$html .= "<option value=\"\">".htmlspecialchars($this->firstItem)."</option>";
		foreach($items as $item)
		{
			$selected = "";
			if($data == $item->value)
				$selected = "selected";
			$html .= "<option value=\"".htmlspecialchars($item->value)."\" $selected>".htmlspecialchars($item->display)."</option>";
		}
		$html .= "</select>";
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
			$row->setError(JText::_("PAYPERDOWNLOADPLUS_YOU_MUST_SELECT_THIS_FIELD__41") . $this->displayName);
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
			$errorText = JText::_("PAYPERDOWNLOADPLUS_YOU_MUST_SELECT_THIS_FIELD__42", true) . $displayName;
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
}

/**
This class is used to edit data using a radio button
*/
class RadioVisualDataBind extends VisualDataBind
{
	var $yes_image;
	var $no_image;
	var $yes_task;
	var $no_task;

	/**
	Class constructor
	$dataField      : The field of the table that will edited
	$displayName  : Text that will show for this element
	*/
	function __construct($dataField, $displayName)
	{
		parent::__construct($dataField, $displayName);
		$this->useForTextSearch = false;
		$this->allowBlank = true;
		$this->yes_image="";
		$this->no_image="";
		$this->yes_task="";
		$this->no_task="";
	}

	/**
	Returns the table field this elements gets its data from
	*/
	function getData($row)
	{
		return ($row->{$this->dataField}== 0) ? JText::_("PAYPERDOWNLOADPLUS_NO_43") : JText::_("PAYPERDOWNLOADPLUS_YES_44");
	}

	/**
	Renders the cell of the table for this element
	*/
	function renderGridCell(&$row, $rowNumber, $columnNumber, $columnCount)
	{
		if($this->yes_image || $this->no_image)
		{
			if($row->{$this->dataField})
			{
				if($this->yes_image)
				{
					if($this->yes_task)
						echo "<a href=\"javascript:void(0);\"
							onclick=\"return listItemTask('cb$rowNumber','{$this->yes_task}')\">";
					echo JHTML::image( $this->yes_image, "" );
					if($this->yes_task)
						echo "</a>";
				}
			}
			else
			{
				if($this->no_image)
				{
					if($this->no_task)
						echo "<a href=\"javascript:void(0);\"
							onclick=\"return listItemTask('cb$rowNumber','{$this->no_task}')\">";
					echo JHTML::image( $this->no_image, "" );
					if($this->no_task)
						echo "</a>";
				}
			}
		}
		else
		{
			parent::renderGridCell($row, $rowNumber, $columnNumber, $columnCount);
		}
	}

	/**
	Renders controls for this element when inserting a new record on the table
	*/
	function renderNew()
	{
		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		if($this->disabled)
		{
			$data = ($this->defaultValue == 0) ? JText::_("PAYPERDOWNLOADPLUS_NO_45") : JText::_("PAYPERDOWNLOADPLUS_YES_46");
			$html .= htmlspecialchars($data);
		}
		else
		{
			$html .= "<div style=\"padding: 5px;\">";
			$html .= "<fieldset id=\"{$this->dataField}\" class=\"radio btn-group\">";
			$html .= "<input name=\"{$this->dataField}\" id=\"{$this->dataField}0\" value=\"1\" type=\"radio\"";
			$html .= "/>";
			$html .= "<label class=\"btn\" for=\"{$this->dataField}0\">" . JText::_("JYES") . "</label>";
			$html .= "<input name=\"{$this->dataField}\" id=\"{$this->dataField}1\" value=\"0\" type=\"radio\"";
			$html .= "/>";
			$html .= "<label class=\"btn\" for=\"{$this->dataField}1\">" . JText::_("JNO") . "</label>";
			$html .= "</fieldset>";
			$html .= "</div>";
		}
		$html .= "</td></tr>";
		return $html;
	}

	/**
	Renders controls for this element when editing a new record on the table
	*/
	function renderEdit(&$row)
	{
		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		if($this->disabled || $this->disabledEdit)
		{
			$data = ($row->{$this->dataField} == 0) ? JText::_("PAYPERDOWNLOADPLUS_NO_47") : JText::_("PAYPERDOWNLOADPLUS_YES_48");
			$html .= htmlspecialchars($data);
		}
		else
		{
			$html .= "<div style=\"padding: 5px;\">";
			$html .= "<fieldset id=\"{$this->dataField}\" class=\"radio btn-group\">";
			$html .= "<input name=\"{$this->dataField}\" id=\"{$this->dataField}0\" value=\"1\" type=\"radio\"";
			if($row->{$this->dataField})
				$html .= " checked=\"checked\" ";
			$html .= "/>";
			$html .= "<label class=\"btn\" for=\"{$this->dataField}0\">" . JText::_("JYES") . "</label>";
			$html .= "<input name=\"{$this->dataField}\" id=\"{$this->dataField}1\" value=\"0\" type=\"radio\"";
			if(!$row->{$this->dataField})
				$html .= " checked=\"checked\" ";
			$html .= "/>";
			$html .= "<label class=\"btn\" for=\"{$this->dataField}1\">" . JText::_("JNO") . "</label>";
			$html .= "</fieldset>";
			$html .= "</div>";
		}
		$html .= "</td></tr>";
		return $html;
	}

	/**
	Renders javascript code to validate this control before submitting
	*/
	function renderValidateJavascript()
	{
		return "";
	}
}

/**
This class is for elements that won't used to edit. Theese elements will
only show a link on a table cell that will execute a certaing task.
*/
class LinkVisualDataBind extends VisualDataBind
{
	var $task;

	/**
	Class constructor
	$dataField      : The field of the table that will edited
	$displayName  : Text that will show for this element
	*/
	function __construct($dataField, $displayName)
	{
		parent::__construct($dataField, $displayName);
		$this->useForTextSearch = false;
		$this->allowBlank = true;
		$this->task="edit";
		$this->showInEditForm = false;
		$this->showInInsertForm = false;
		$this->ignoreToSelect = true;
		$this->ignoreToBind = true;
		$this->sourceTable = "";
	}

	/**
	Renders the heading on the table for the column assigned to this element
	*/
	function renderColumnHeading($filters, $rows)
	{
	?>
		<th class="nowrap center hidden-phone" width="<?php echo htmlspecialchars($this->columnWidth); ?>%">
		<?php echo htmlspecialchars($this->displayName);?>
		</th>
	<?php
	}

	/**
	Returns field use for ordering
	*/
	function getOrderField()
	{
		return null;
	}

	/**
	Renders the cell of the table for this element
	*/
	function renderGridCell(&$row, $rowNumber, $columnNumber, $columnCount)
	{
		echo "<a href=\"javascript:void(0);\" onclick=\"return listItemTask('cb$rowNumber','{$this->task}')\">";
		echo htmlspecialchars($this->displayName);
		echo "</a>";
	}

	/**
	Renders javascript code to validate this control before submitting
	*/
	function renderValidateJavascript()
	{
		return "";
	}
}

/**
This class is for elements that will could be used for sorting
*/
class OrderVisualDataBind extends VisualDataBind
{
	/**
	Class constructor
	$dataField      : The field of the table that will edited
	$displayName  : Text that will show for this element
	*/
	function __construct($dataField, $displayName)
	{
		parent::__construct($dataField, $displayName);
		$this->showInGrid = true;
		$this->useForTextSearch = false;
		$this->showInEditForm = false;
		$this->showInInsertForm = false;
	}

	/**
	Renders the heading on the table for the column assigned to this element
	*/
	function renderColumnHeading($filters, $rows)
	{
	?>
		<th class="nowrap center hidden-phone" width="<?php echo htmlspecialchars($this->columnWidth); ?>%">
		<?php echo JHTML::_('grid.sort',  htmlspecialchars($this->displayName), htmlspecialchars($this->dataField), @$filters['order_Dir'], @$filters['order'] ); ?>
		<?php echo JHTML::_('grid.order',  $rows ); ?>
		</th>
	<?php
	}

	/**
	Renders the cell of the table for this element
	*/
	function renderGridCell(&$row, $rowNumber, $columnNumber, $columnCount)
	{
	$root = JURI::root();
	?>
	<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" class="text_area" style="text-align: center" />
	<?php
	if($rowNumber > 0)
	{
	?>
	<span>
	<a href="#reorder" onclick="return listItemTask('cb<?php echo $rowNumber?>', 'orderup');" alt="<?php echo JText::_("PAYPERDOWNLOADPLUS_MOVER_ARRIBA_49");?>">
	<img src="<?php echo $root;?>administrator/images/uparrow.png" width="16" height="16" border="0" alt="<?php echo JText::_("PAYPERDOWNLOADPLUS_MOVER_ARRIBA_50");?>"/>
	</a>
	</span>
	<?php
	}
	?>
	<span>
	<a href="#reorder" onclick="return listItemTask('cb<?php echo $rowNumber?>', 'orderdown');" alt="<?php echo JText::_("PAYPERDOWNLOADPLUS_MOVER_ABAJO_51");?>">
	<img src="<?php echo $root;?>administrator/images/downarrow.png" width="16" height="16" border="0" alt="<?php echo JText::_("PAYPERDOWNLOADPLUS_MOVER_ABAJO_52");?>"/>
	</a></span>

	<?php
	}
}

/**
This class is for elements that won't be edited or shown. An input element of type hidden will be used
*/
class HiddenVisualDataBind extends VisualDataBind
{
	/**
	Class constructor
	$dataField      : The field of the table that will edited
	$displayName  : Text that will show for this element
	*/
	function __construct($dataField, $displayName)
	{
		parent::__construct($dataField, $displayName);
		$this->showInGrid = false;
		$this->useForTextSearch = false;
	}

	/**
	Renders the hidden input control with the default value
	*/
	function renderNew()
	{
		$html = "";
		$html .= "<input type=\"hidden\" name=\"" . htmlspecialchars($this->dataField) . "\" id=\"" . htmlspecialchars($this->dataField) . "\" ";
		$html .= "value=\"" . htmlspecialchars($this->defaultValue) . "\" />";
		return $html;
	}

	/**
	Renders the hidden input control with the current value
	*/
	function renderEdit(&$row)
	{
		$dataField = $this->dataField;
		$data = htmlspecialchars($row->$dataField) ;
		$html = "";
		$html .= "<input type=\"hidden\" name=\"" . htmlspecialchars($this->dataField) . "\" id=\"" . htmlspecialchars($this->dataField) . "\" ";
		$html .= "value=\"$data\" />";
		return $html;
	}

	/**
	Renders javascript code to validate this control before submitting
	*/
	function renderValidateJavascript()
	{
		return "";
	}
}

class AggregateVisualDataBind extends VisualDataBind
{
	var $tableName;
	var $keyField;
	var $foreignKeyField;
	var $aggregateFunction;

	function __construct($dataField, $displayName, $tableName, $keyField, $foreignKeyField, $aggregateFunction)
	{
		parent::__construct($dataField, $displayName);
		$this->showInGrid = true;
		$this->showInEditForm = false;
		$this->showInInsertForm = false;
		$this->useForTextSearch = false;
		$this->tableName = $tableName;
		$this->keyField = $keyField;
		$this->foreignKeyField = $foreignKeyField;
		$this->aggregateFunction = $aggregateFunction;
	}

	function isAggregate()
	{
		return true;
	}

	function getData($row)
	{
		$field = $this->aggregateFunction . "_" . $this->dataField;
		return $row->$field;
	}

	function getSelectField()
	{
		if($this->tableName && $this->aggregateFunction)
			return $this->aggregateFunction . "(" . $this->tableName . "." . $this->dataField . ") AS " .
				$this->aggregateFunction . "_" . $this->dataField;
		else
			return parent::getSelectField();
	}

	function getExtraSelectTable()
	{
		if($this->tableName != "")
			return "LEFT JOIN " . $this->tableName . " ON " . $this->sourceTable . "." . $this->keyField . " = " .
				$this->tableName . "." . $this->foreignKeyField;
		else
			return "";
	}

	/**
	Returns field use for ordering
	*/
	function getOrderField()
	{
		$field = new stdClass();
		$field->fieldName = $this->aggregateFunction . "_" . $this->dataField;
		$field->display = $this->displayName;
		return $field;
	}

	function renderColumnHeading($filters, $rows)
	{
	?>
		<th class="nowrap center hidden-phone" width="<?php echo htmlspecialchars($this->columnWidth); ?>%">
		<?php echo JHTML::_('grid.sort',  htmlspecialchars($this->displayName), $this->aggregateFunction . "_" . $this->dataField, @$filters['order_Dir'], @$filters['order'] ); ?>
		</th>
	<?php
	}
}

class WYSIWYGEditotVisualDataBind extends VisualDataBind
{
	var $extraDescription;

	function __construct($dataField, $displayName)
	{
		parent::__construct($dataField, $displayName);
		$this->extraDescription = "";
	}

	function setExtraDescription($extraDescription)
	{
		$this->extraDescription = $extraDescription;
	}

	function cleanHtml($text)
	{
		$text = preg_replace("/<\/?[a-zA-Z0-9]+[^>]*>/", "", $text);
		$text = preg_replace("/&[a-zA-Z]{1,6};/", "", $text);
		return $text;
	}

	function renderGridCell(&$row, $rowNumber, $columnNumber, $columnCount)
	{
		$field = $this->dataField;
		$data = $this->cleanHtml($this->getData($row));
		if(is_numeric($this->maxGridCellLength) && $this->maxGridCellLength > 0 && strlen($data) > $this->maxGridCellLength)
			$data = substr($data, 0, $this->maxGridCellLength) . "...";
		if($this->isEditLink == 1)
		{
			$gridToolTip = $this->gridToolTip;
			if(!$gridToolTip)
				$gridToolTip = JText::_("PAYPERDOWNLOADPLUS_HAGA_CLICK_PARA_EDITAR_53") . "::" . $data;
			echo "<span class=\"editlinktip hasTip\" title=\"".htmlspecialchars($gridToolTip)."\">";
			echo "<a href=\"javascript:void(0);\" onclick=\"return listItemTask('cb$rowNumber','{$this->linkTask}')\">";
			echo htmlspecialchars($data);
			echo "</a>";
			echo "</span>";
		}
		else
		{
			echo htmlspecialchars($data);
		}
	}

	function renderNew()
	{
		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		$dataField = $this->dataField;
		$size = $this->size;
		$maxLength = "";
		if($this->maxLength > 0)
		{
			$maxLength = $this->maxLength;
		}
		$editor = JFactory::getConfig()->get('editor');
		$editor = JEditor::getInstance($editor);
		$html .= "<div style=\"float: left;\">";
		$html .= $editor->display($dataField, '', '100%', '200', '20', '20');
		$html .= "</div>";
		$html .= "<div style=\"float: left; margin: 10px; width: 300px;\">" . htmlspecialchars($this->extraDescription) . "</div>";
		$html .= "</td></tr>";
		return $html;
	}

	function renderEdit(&$row)
	{
		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		$dataField = $this->dataField;
		$data = $this->getData($row);
		$size = $this->size;
		$maxLength = "";
		if($this->maxLength > 0)
		{
			$maxLength = $this->maxLength;
		}
		$editor = JFactory::getConfig()->get('editor');
		$editor = JEditor::getInstance($editor);
		$html .= "<div style=\"float: left;\">";
		$html .= $editor->display($dataField, $row->$dataField, '100%', '200', '20', '20');
		$html .= "</div>";
		$html .= "<div style=\"float: left; margin: 10px; width: 300px;\">" . htmlspecialchars($this->extraDescription) . "</div>";
		$html .= "</td></tr>";
		return $html;
	}

	function onBeforeStore( &$row )
	{
		if($this->ignoreToBind)
			return true;
		//$row->{$this->dataField} = JRequest::getVar( $this->dataField, '', 'post','string', JREQUEST_ALLOWRAW );
			$row->{$this->dataField} = JFactory::getApplication()->input->getRaw($this->dataField, '');
		return true;
	}

	function renderValidateJavascript()
	{
		return "";
	}
}

?>