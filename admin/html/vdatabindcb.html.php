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
class VisualEditCheckboxDataBind extends VisualDataBind
{
	var $checkboxTitle;
	/**
	Class constructor
	$dataField      : The field of the table that will edited
	$displayName  : Text that will show for this element
	*/
	function __construct($dataField, $displayName, $checkboxTitle)
	{
		parent::__construct($dataField, $displayName);
		$this->checkboxTitle = $checkboxTitle;
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
		$html .= "<input class=\"text_area\" type=\"text\" name=\"$dataField\" id=\"$dataField\" size=\"$size\" maxlength=\"$maxLength\" $disabled " .$this->renderHtmlProperties()."/>";
		$html .= "&nbsp;&nbsp;&nbsp;";
		$html .= "<input type=\"checkbox\" value=\"1\" name=\"$dataField" . "_checkbox\" id=\"$dataField" . "_checkbox\"";
		$html .= " onclick=\"javascript: var cb=document.getElementById('$dataField" . "_checkbox');";
		$html .= "var edit=document.getElementById('$dataField');if(cb.checked){edit.disabled=true;edit.value='0';}else{edit.disabled=false;}\"/>";
		$html .= "&nbsp;&nbsp;&nbsp;";
		$html .= htmlspecialchars($this->checkboxTitle);
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
		$row_data = $this->getData($row);
		$data = htmlspecialchars($row_data);
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
		if($row_data == 0)
			$disabled = " disabled=\"true\" ";
		$html .= "<input class=\"text_area\" type=\"text\" name=\"$dataField\" id=\"$dataField\" size=\"$size\" maxlength=\"$maxLength\" value=\"$data\" $disabled " .$this->renderHtmlProperties()."/>";
		$html .= "&nbsp;&nbsp;&nbsp;";
		$checked = ($row_data == 0)? " checked " : "";
		$html .= "<input type=\"checkbox\" value=\"1\" name=\"$dataField" . "_checkbox\" id=\"$dataField" . "_checkbox\" $checked";
		$html .= " onclick=\"javascript: var cb=document.getElementById('$dataField" . "_checkbox');";
		$html .= "var edit=document.getElementById('$dataField');if(cb.checked){edit.disabled=true;edit.value='0';}else{edit.disabled=false;}\"/>";
		$html .= "&nbsp;&nbsp;&nbsp;";
		$html .= htmlspecialchars($this->checkboxTitle);
		$html .= "</td></tr>";
		return $html;
	}
}

?>