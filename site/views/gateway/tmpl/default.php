<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die;

$root = JURI::root();
JPluginHelper::importPlugin("payperdownloadplus");
$dispatcher	= JDispatcher::getInstance();
$dispatcher->trigger('onRenderGatewayReturnPage', array($this->gateway));

?>