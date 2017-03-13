<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.view');

class PayPerDownloadViewPaypalSim extends JViewLegacy
{
	function display($tpl = null)
	{
		
		$return = JRequest::getVar('return');
		$this->assign("return", $return);
		parent::display($tpl);
	}
	
}

?>