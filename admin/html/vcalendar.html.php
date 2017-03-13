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

define("DATE_FORMAT_YMD", 1);
define("DATE_FORMAT_DMY", 2);
define("DATE_FORMAT_MDY", 3);

class CalendarVisualDataBind extends VisualDataBind
{

	var $format_str;
	
	function __construct($dataField, $displayName, $format=DATE_FORMAT_YMD)
	{
		parent::__construct($dataField, $displayName);
		$this->format = $format;
		switch($format)
		{
		case DATE_FORMAT_YMD:
			$this->format_str = "%Y-%m-%d";
			$this->regExp = "\\d{4}-\\d{1,2}-\\d{1,2}";
			break;
		case DATE_FORMAT_DMY:
			$this->format_str = "%d-%m-%Y";
			$this->regExp = "\\d{1,2}-\\d{1,2}-\\d{4}";
			break;
		case DATE_FORMAT_MDY:
			$this->format_str = "%m-%d-%Y";
			$this->regExp = "\\d{1,2}-\\d{1,2}-\\d{4}";
			break;
		}
	}
	
	function renderNew()
	{
		JHTML::_('behavior.calendar');
		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		$dataField = $this->dataField;
		$size = $this->size;
		$maxLength = "";
		if($this->maxLength > 0)
		{
			$maxLength = $this->maxLength;
		}
		if($this->disabled)
		{
			$html .=  "<input type=\"hidden\" name=\"$dataField\" id=\"$dataField" . "_hidden\" value=\"" . htmlspecialchars($this->defaultValue) . "\" />";
			$html .=  "<input type=\"text\" name=\"$dataField\" id=\"$dataField" . "\" value=\"" . htmlspecialchars($this->defaultValue) . "\" disabled=\"true\"/>";
		}
		else
			$html .= JHTML::_('calendar', "", $this->dataField, $this->dataField, $this->format_str, array('class'=>'inputbox', 'size'=>'25',  'maxlength'=>'19')); 
		$html .= "</td></tr>";
		return $html;
	}

	
	function getData($row)
	{
		return $this->getDateString($row, true);
	}
	
	function getDateString($row, $include_time)
	{
		if($row->{$this->dataField})
		{
			$sep = explode(" ", $row->{$this->dataField});
			$date_values = explode("-", $sep[0]);
			switch($this->format)
			{
			case DATE_FORMAT_YMD:
				return $date_values[0]."-".$date_values[1]."-".$date_values[2] . ($include_time ? (" " . $sep[1]) : "");
			case DATE_FORMAT_DMY:
				return $date_values[2]."-".$date_values[1]."-".$date_values[0] . ($include_time ? (" " . $sep[1]) : "");
			case DATE_FORMAT_MDY:
				return $date_values[1]."-".$date_values[2]."-".$date_values[0] . ($include_time ? (" " . $sep[1]) : "");
			}
			return $row->{$this->dataField};
		}
		else
			return "--";
	}
	
	function renderEdit(&$row)
	{
		JHTML::_('behavior.calendar');
		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		$dataField = $this->dataField;
		$data = htmlspecialchars($this->getDateString($row, false));
		$size = $this->size;
		$maxLength = "";
		if($this->maxLength > 0)
		{
			$maxLength = $this->maxLength;
		}
		if($this->disabled || $this->disabledEdit)
		{
			$html .=  "<input type=\"hidden\" name=\"$dataField\" id=\"$dataField" . "_hidden\" value=\"$data\" />";
			$html .=  "<input type=\"text\" name=\"$dataField\" id=\"$dataField" . "\" value=\"$data\" disabled=\"true\"/>";
		}
		else
			$html .= JHTML::_('calendar', $data, $this->dataField, $this->dataField, $this->format_str, 
				array('class'=>'inputbox', 'size'=>'25',  'maxlength'=>'19')); 
		$html .= "</td></tr>";
		return $html;
	}
	
	function onBeforeStore(&$row)
	{
		if($this->ignoreToBind)
			return true;
		$date_values = explode("-", $row->{$this->dataField});
		switch($this->format)
		{
		case DATE_FORMAT_YMD:
			$row->{$this->dataField} = $date_values[0]."-".$date_values[1]."-".$date_values[2];
			break;
		case DATE_FORMAT_DMY:
			$row->{$this->dataField} = $date_values[2]."-".$date_values[1]."-".$date_values[0];
			break;
		case DATE_FORMAT_MDY:
			$row->{$this->dataField} = $date_values[2]."-".$date_values[0]."-".$date_values[1];
			break;
		}
		return true;
	}
}

?>