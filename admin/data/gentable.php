<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die('Restricted access');

/**
This class derived from JTable will be use to handle data on a table. 
Instead of statically defining attributes for each table field the attributes
are dinamically  based on the data bind model supplied as parameter to
the class constructor
*/
class GenericTable extends JTable
{
	var $dataBindModel = null;
	
	/**
	Class constructor
	*/
	function __construct(&$db, $dataBindModel)
	{
		parent::__construct( $dataBindModel->getTableName(), $dataBindModel->getKeyField(), $db );
		$this->dataBindModel = $dataBindModel;
		$this->createProperties();
	}
	
	/**
	This functions creates the JTable attributes that will used matched to table fields
	*/
	function createProperties()
	{
		$keyName = $this->dataBindModel->getKeyField();
		$this->$keyName = null;
		$dataBinds = $this->dataBindModel->getDataBinds();
		foreach($dataBinds as $dataBind)
		{
			if(!$dataBind->ignoreToBind)
			{
				$propName = $dataBind->dataField;
				$this->$propName = null;
				$dataBind->onAfterCreateProperty($this);
			}
		}
	}
	
	/**
	Calls a parent bind and calls the onAfterBind function for each element
	*/
	function bind( $from, $ignore=array() )
	{
		$result = parent::bind($from, $ignore);
		$dataBinds = $this->dataBindModel->getDataBinds();
		foreach($dataBinds as $dataBind)
			if(!$dataBind->onAfterBind( $this, $from ))
				$result = false;
		return $result;
	}
	
	/**
	Calls a parent load and calls the onAfterLoad function for each element
	*/
	function load( $oid = null, $reset = true)
	{
		$result = parent::load( $oid );
		$dataBinds = $this->dataBindModel->getDataBinds();
		foreach($dataBinds as $dataBind)
			$dataBind->onAfterLoad( $this );
		return $result;
	}
	
	/**
	Calls a parent store and calls the onBeforeLoad and onAfterStore function for each element
	*/
	function store( $updateNulls=false )
	{
		$dataBinds = $this->dataBindModel->getDataBinds();
		foreach($dataBinds as $dataBind)
			if(!$dataBind->onBeforeStore($this))
				return false;
		$result = parent::store($updateNulls);
		if($result)
			foreach($dataBinds as $dataBind)
				if(!$dataBind->onAfterStore($this))
					return false;
		return $result;
	}
	
	/**
	Calls a parent delete and calls the onBeforeDelete and onAfterDelete function for each element
	*/
	function delete($id = null)
	{
		$dataBinds = $this->dataBindModel->getDataBinds();
		foreach($dataBinds as $dataBind)
			$dataBind->onBeforeDelete($this, $id);
		$result = parent::delete($id);
		if($result)
			foreach($dataBinds as $dataBind)
				$dataBind->onAfterDelete($this, $id);
		return $result;
	}
	
	/**
	Returns the where clause that will be used on the query to show elements.
	*/
	function getWhereClause($filters, $dataBinds)
	{
		$where = "";
		if(isset($filters['where']) && $filters['where'])
			$where = $filters['where'];
		$searchWhere = "";
		$filterWhere = "";
		foreach($dataBinds as $dataBind)
		{
			if($dataBind->useForTextSearch && $filters['search'] != '')
			{
				$cond = $dataBind->getSearchCondition($filters['search']);
				if($cond)
				{
					if($searchWhere != "")
						$searchWhere .= " OR ";
					$searchWhere .= $cond;
				}
			}
			if($dataBind->useForFilter)
			{
				$cond = $dataBind->getFilterCondition($filters);
				if($cond)
				{
					if($filterWhere != "")
						$filterWhere .= " AND ";
					$filterWhere .= $cond;
				}
			}
		}
		
		if($where != "" && $searchWhere != "")
			$where = "($where) AND ($searchWhere)";
		else if($searchWhere != "")
			$where = $searchWhere;
		if($where != "" && $filterWhere != "")
			$where = "($where) AND ($filterWhere)";
		else if($filterWhere != "")
			$where = $filterWhere;
		if($where != "")
			$where = " WHERE " . $where;
		return $where;
	}
	
	/**
	Returns how many elements there on the table matching the filters
	*/
	function getCount($filters)
	{
		$db = JFactory::getDBO();
		$dataBinds = $this->dataBindModel->getDataBinds();
		$query = "SELECT count(*) FROM " . $this->dataBindModel->getTableName();
		foreach($dataBinds as $dataBind)
			if($dataBind->useForTextSearch)
				$query .= " " . $dataBind->getExtraSelectTable();
		$query .= $this->getWhereClause($filters, $dataBinds);
		$db->setQuery( $query );
		$total = $db->loadResult();
		return $total;
	}
	
	/**
	Returns the group clause that will be used on the query to show elements.
	*/
	function getGroupByClause($dataBindModel)
	{
		$dataBinds = $this->dataBindModel->getDataBinds();
		$groups = array();
		$useGroupBy = false;
		foreach($dataBinds as $dataBind)
		{
			$agg = $dataBind->isAggregate();
			if($agg)
				$useGroupBy = true;
			if(!$dataBind->ignoreToSelect && $dataBind->dataField && !$agg )
			{
				$selectFields = explode(",", $dataBind->getSelectField());
				$groups = array_merge($groups, $selectFields);
			}
		}
		if($useGroupBy && count($groups) > 0)
		{
			return " GROUP BY " . implode(", " , $groups) . ", " . 
				$this->dataBindModel->getTableName() . "." . $this->dataBindModel->getKeyField();
		}
		else
			return "";
	}
	
	/**
	Returns the query that will be used to retrieve elements from database
	*/
	function getSelect($filters)
	{
		$query = null;
		$tables = $this->dataBindModel->getTableName();
		$dataBinds = $this->dataBindModel->getDataBinds();
		$query = "SELECT " . $this->dataBindModel->getTableName() . "." . $this->dataBindModel->getKeyField();
		foreach($dataBinds as $dataBind)
			if(!$dataBind->ignoreToSelect)
			{
				$query .= ", ";
				$query .= $dataBind->getSelectField();
				$tables .= " " . $dataBind->getExtraSelectTable();
			}
		$query .= " FROM " . $tables;
		$query .= $this->getWhereClause($filters, $dataBinds);
		$query .= $this->getGroupByClause($this->dataBindModel);
		if($filters['order'] != '')
			$query .= ' ORDER BY '. $filters['order'] .' '. $filters['order_Dir'];
		return $query;
	}
	
	/**
	Returns the list elements
	*/
	function getList($limitStart, $limit, $filters)
	{
		$db = JFactory::getDBO();
		$table = $this->dataBindModel->getTableName();
		$query = $this->getSelect($filters);
		$db->setQuery( $query, $limitStart, $limit );
		$rows = $db->loadObjectList();
		return $rows;
	}
	
	/**
	Derived from the parent check function to do a validation on data supplied before saving
	to database
	*/
	function check()
	{
		$dataBinds = $this->dataBindModel->getDataBinds();
		foreach($dataBinds as $dataBind)
			if(!$dataBind->check($this))
				return false;
		return true;
	}
	
	function publish($cid = null, $state = 1, $userId = 0)
	{
		if($this->dataBindModel != null)
		{
			$db = JFactory::getDBO();
			foreach($cid as $key => $value)
			{
				$cid[$key] = "'" . $db->escape($value) . "'";
			}
			if(count($cid) > 0)
			{
				$cids = implode(',', $cid);
				$query = "UPDATE " . $db->quoteName($this->dataBindModel->tableName) . " SET enabled = 1 WHERE " . 
					$db->quoteName($this->dataBindModel->keyField) . " IN ($cids)";
				$db->setQuery($query);
				$db->query();
			}
		}
	}
	
	function unpublish($cid)
	{
		if($this->dataBindModel != null)
		{
			$db = JFactory::getDBO();
			foreach($cid as $key => $value)
			{
				$cid[$key] = "'" . $db->escape($value) . "'";
			}
			if(count($cid) > 0)
			{
				$cids = implode(',', $cid);
				$query = "UPDATE " . $db->quoteName($this->dataBindModel->tableName) . " SET enabled = 0 WHERE " . 
					$db->quoteName($this->dataBindModel->keyField) . " IN ($cids)";
				$db->setQuery($query);
				$db->query();
			}
		}
	}
}

?>