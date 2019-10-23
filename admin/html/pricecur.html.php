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
		    JHtml::_('bootstrap.tooltip');
			$gridToolTip = $this->gridToolTip;
			if(!$gridToolTip)
				$gridToolTip = JText::_("PAYPERDOWNLOADPLUS_CLICKTOEDIT");
			echo "<span class=\"editlinktip hasTooltip\" title=\"".htmlspecialchars($gridToolTip)."\">";
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

	function renderCurrenciesCombo($value = '')
	{
	    if (empty($value)) { // when new
	        $config = JComponentHelper::getParams('com_payperdownload');
	        $value = $config->get('default_currency', 'USD');
	    }

	    $db = JFactory::getDBO();

	    $query = $db->getQuery(true);

	    //$query->select('DISTINCT iso');
	    $fields = $db->quoteName(array('iso', 'currency'));
	    $fields[0] = 'DISTINCT ' . $fields[0]; // prepend distinct to the first quoted field
	    $query->select($fields);
	    $query->from($db->quoteName('#__payperdownloadplus_currencies'));
	    $query->where($db->quoteName('iso') . ' <> ' . $db->quote('-'));
	    $query->order($db->quoteName('iso') . ' ASC'); // because when translated, currency order has no meaning

	    $db->setQuery($query);

	    $results = null;
	    try {
	        //$currencies = $db->loadColumn();
	        $results = $db->loadAssocList('iso');

	        $currencies = array();
	        foreach ($results as $result) {
	            $currencies[] = $result['iso'];
	        }
	    } catch (RuntimeException $e) {
	        $currencies = array("USD",
	            "AUD", "BRL", "CAD", "CZK", "DKK",
	            "EUR", "HKD", "HUF", "ILS", "JPY", "MYR", "MXN", "NOK", "PHP", "PLN",
	            "GBP", "SGD", "SEK", "CHF", "TWD", "THB", "RUB");
	    }

		$dataField = $this->currency_field;

		$html = "<select name=\"$dataField\" id=\"$dataField\">";
		foreach($currencies as $currency)
		{
			$selected = "";
			if($value == $currency)
				$selected = "selected";
			$html .= "<option value=\"" . htmlspecialchars($currency) . "\" $selected>";

			$translated_currency = JText::_("CURRENCY_" . $currency);
			if (substr($translated_currency, 0, 3) === 'CUR') { // there is no currency starting with CUR
			    if (is_null($results)) {
			        $translated_currency = $currency;
			    } else {
			        $translated_currency = ucwords($results[$currency]['currency']);
			    }
			}

			$html .= htmlspecialchars($translated_currency . " (" . $currency . ")");
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