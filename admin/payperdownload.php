<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die;

if (!JFactory::getUser()->authorise('core.manage', 'com_payperdownload')) 
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

$application = JFactory::getApplication();
$input = $application->input;
$task = $input->get( 'task', '' );
$option = $input->get( 'option', '' );
$pageToShow = $input->get( 'adminpage');

if(!preg_match('/^[a-zA-Z][a-zA-Z0-9]+$/', $pageToShow) || !file_exists((JPATH_COMPONENT.'/controllers/'.$pageToShow.'.php')))
{
	$pageToShow = 'resources';
	$input->set('adminpage', 'resources');
}
//Include de php file for this call
require_once(JPATH_COMPONENT.'/controllers/'.$pageToShow.'.php');
$formName = ucfirst($pageToShow).'Form';
if (class_exists( $formName ))
{
	//Create the object and show the form
	$form = new $formName();
	$form->showForm($task, $option);
}
?>