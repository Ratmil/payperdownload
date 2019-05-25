<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die;

$app = JFactory::getApplication();

if (!JFactory::getUser()->authorise('core.manage', 'com_payperdownload'))
{
    $app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
    $app->setHeader('status', 403, true);
}

// Get an instance of the controller
$controller = JControllerLegacy::getInstance('PayPerDownload');

// Perform the Request task
$controller->execute($app->input->get('task'));

// Redirect if set by the controller
$controller->redirect();