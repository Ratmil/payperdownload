<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

// no direct access
defined ( '_JEXEC' ) or die;

require_once(JPATH_COMPONENT.'/html/fieldset.php');

/**
This class is a container of the elements of the table that will be managed.
*/
class VisualDataBindModel
{
	var $dataBinds = null;
	var $keyField = null;
	var $tableName = null;
	var $fieldSets = null;
	var $currentFieldSet = null;

	/**
	Class constructor
	*/
	function __construct()
	{
		$this->dataBinds = array();
	}

	/**
	Sets the key field of the table
	*/
	function setKeyField($keyField)
	{
		$this->keyField = $keyField;
	}

	/**
	Adds a data bind element to the collection
	*/
	function addDataBind($dataBind)
	{
		$dataBind->setSourceTable($this->tableName);
		$this->dataBinds[] = $dataBind;
		if($this->currentFieldSet)
			$this->currentFieldSet->addDataBind($dataBind);
	}

	/**
	Adds a field set
	*/
	function newFieldSet($fieldSetName, $caption)
	{
		if($this->fieldSets == null)
			$this->fieldSets = array();
		$newFieldSet = new Fieldset($fieldSetName, $caption);
		$this->fieldSets []= $newFieldSet;
		$this->currentFieldSet = $newFieldSet;
	}

	/**
	Sets the table name
	*/
	function setTableName($tableName)
	{
		$this->tableName = $tableName;
	}

	/**
	Returns the key field of the table
	*/
	function getKeyField()
	{
		return $this->keyField;
	}

	/**
	Returns the table name
	*/
	function getTableName()
	{
		return $this->tableName;
	}

	/**
	Returns the collection of elements
	*/
	function getDataBinds()
	{
		return $this->dataBinds;
	}

	/**
	Return collection of fieldsets
	*/
	function getFieldSets()
	{
		if($this->fieldSets == null)
		{
			$fieldSets = array();
			$emptyFieldSet = new Fieldset("", "");
			$emptyFieldSet->dataBinds = $this->dataBinds;
			$fieldSets []= $emptyFieldSet;
			return $fieldSets;
		}
		return $this->fieldSets;
	}
}

?>