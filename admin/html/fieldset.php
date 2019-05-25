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
	Class to handle fieldsets
*/
class Fieldset
{
	var $name = null;
	var $caption = null;
	var $dataBinds = null;
	/**
	Class constructor
	*/
	function __construct($name, $caption)
	{
		$this->dataBinds = array();
		$this->name = $name;
		$this->caption = $caption;
	}

	/**
	Adds a data bind element to the collection
	*/
	function addDataBind($dataBind)
	{
		$this->dataBinds[] = $dataBind;
	}
}

?>