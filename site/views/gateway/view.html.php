<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class PayPerDownloadViewGateway extends JViewLegacy
{
	function display($tpl = null)
	{
	    $gateway = JFactory::getApplication()->input->getString('gateway');
		$this->assignRef("gateway", $gateway);
		parent::display($tpl);
	}

}

?>