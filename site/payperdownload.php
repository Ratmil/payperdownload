<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die;

$controller = JControllerLegacy::getInstance('Payperdownload');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

?>