<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class PayPerDownloadViewPaypalSim extends JViewLegacy
{
	function display($tpl = null)
	{

	    $return = JFactory::getApplication()->input->getString('return', '');
		$this->assign("return", $return);
		parent::display($tpl);
	}

}

?>