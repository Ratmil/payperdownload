<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

// no direct access
defined ( '_JEXEC' ) or die;

class PriceCurrencyVisualDataBind extends VisualDataBind
{
	var $currency_field;

	function __construct($dataField, $displayName, $currency_field)
	{
		parent::__construct($dataField, $displayName);
		$this->currency_field = $currency_field;
		$this->regExp = '\s*\d+(\.\d+)?\s*';
		$this->size = 10;
	}

	/**
	Executed after the property on the JTable object has been created
	*/
	function onAfterCreateProperty(&$row)
	{
		$this->{$this->currency_field} = null;
	}

	/**
	Executed after binding
	*/
	function onAfterBind(&$row, $from)
	{
		if(isset($from[$this->currency_field]))
			$row->{$this->currency_field} = $from[$this->currency_field];
		return true;
	}

	/**
	Returns the piece of query that will be included in the select to load all elements
	*/
	function getSelectField()
	{
		return $this->sourceTable . "." . $this->dataField . ", " . $this->sourceTable . "." . $this->currency_field;
	}

	/**
	Renders the cell of the table for this element
	*/
	function renderGridCell(&$row, $rowNumber, $columnNumber, $columnCount)
	{
		$field = $this->dataField;
		$data = $this->getData($row) . " ";
		$data .= $row->{$this->currency_field};
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

	function renderCurrenciesCombo($value = "USD")
	{
		$dataField = $this->currency_field;
		$currencies = array("USD",
			"AUD", "BRL", "CAD", "CZK", "DKK",
			"EUR", "HKD", "HUF", "ILS", "JPY", "MYR", "MXN", "NOK", "PHP", "PLN",
			"GBP", "SGD", "SEK", "CHF", "TWD", "THB", "RUB");
		$html = "<select name=\"$dataField\" id=\"$dataField\">";
		foreach($currencies as $currency)
		{
			$selected = "";
			if($value == $currency)
				$selected = "selected";
			$html .= "<option value=\"" . htmlspecialchars($currency) . "\" $selected>";
			$html .= htmlspecialchars(JText::_("CURRENCY_" . $currency) . " (" . $currency . ")");
			$html .= "</option>";
		}
		$html .= "</select>";
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
		}
		$html .= "<input class=\"text_area\" type=\"text\" name=\"$dataField\" id=\"$dataField\" size=\"$size\" maxlength=\"$maxLength\" value=\"" .
			htmlspecialchars($this->defaultValue) . "\" ".$this->renderHtmlProperties()." $disabled/>";
		$html .= "&nbsp;&nbsp;&nbsp;";
		$html .= $this->renderCurrenciesCombo();
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
		}
		$html .= "<input class=\"text_area\" type=\"text\" name=\"$dataField\" id=\"$dataField\" size=\"$size\" maxlength=\"$maxLength\" value=\"$data\" $disabled " .$this->renderHtmlProperties()."/>";
		$html .= "&nbsp;&nbsp;&nbsp;";
		$html .= $this->renderCurrenciesCombo($row->{$this->currency_field});
		$html .= "</td></tr>";
		return $html;
	}
}