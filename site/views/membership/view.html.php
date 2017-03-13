<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.view');

class PayPerDownloadViewMembership extends JViewLegacy
{
	function display($tpl = null)
	{
		$option = JRequest::getVar('option');
		JHTML::_('stylesheet', 'components/'. $option . '/css/frontend.css');
		$model = $this->getModel();
		if($model)
		{
			$limit = JRequest::getVar( 'limit', 20 );
			$start = JRequest::getVar( 'limitstart', 0 );
			jimport( 'joomla.html.pagination' );
			$members = $model->getMembers($start, $limit);
			$total = $model->getTotalMembers();
			$objPagination = new JPagination( $total, $start, $limit );
			$this->assignRef("members", $members);
			$this->assignRef("pagination", $objPagination);
			parent::display($tpl);
		}
		else
			echo "model not found";
	}
	
	
}

?>