<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.view');

class PayPerDownloadViewGateway extends JViewLegacy
{
	function display($tpl = null)
	{
		$gateway = JRequest::getVar('gateway');
		$this->assignRef("gateway", $gateway);
		parent::display($tpl);
	}
	
}

?>