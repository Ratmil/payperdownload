<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
 
 defined('_JEXEC') or die;
 
 /**
 * Method to build Route
 * @param array $query
 */ 
function PayperdownloadBuildRoute(&$query)
{
	$segments = array();
	if(isset($query['view']))
	{
		$segments []= $query['view'];
		unset($query['view']);
		if(isset($query['lid']))
		{
			$lid = (int)$query['lid'];
			$segments []= PayperdownloadPrepareAlias($lid, PayperdownloadGetLicenseName($lid));
			unset($query['lid']);
		}
	}
	return $segments;
}

/**
 * Method to parse Route
 * @param array $segments
 */ 
function PayperdownloadParseRoute($segments)
{
	$vars = array();
	if(count($segments) > 0)
	{
		$vars['view'] = $segments[0];
		if(count($segments) > 1)
		{
			$vars['lid'] = (int)$segments[1];
		}
	}
	return $vars;
}

function PayperdownloadGetLicenseName($id)
{
	$db = JFactory::getDBO();
	$query = "SELECT license_id, license_name FROM #__payperdownloadplus_licenses WHERE license_id = " . (int)$id;
	$db->setQuery( $query );
	$license = $db->loadObject();
	if($license)
		return $license->license_name;
	else
		return "";
}

function PayperdownloadPrepareAlias($id, $alias)
{
	$alias = preg_replace("/\\s/", "_", $alias);
	return $id . "-" . $alias;
}