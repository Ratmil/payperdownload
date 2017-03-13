<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.view');

class PayPerDownloadViewAlllicenses extends JViewLegacy
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
			$licenses = $model->getAllLicenses($start, $limit);
			$showResources = $model->getShowResources();
			$total = $model->getTotalLicenses();
			jimport( 'joomla.html.pagination' );
			$objPagination = new JPagination( $total, $start, $limit );
			$this->assignRef("pagination", $objPagination);
			$this->assignRef("licenses", $licenses);
			$this->assignRef("showResources", $showResources);
			$multipleLicenseView = 0;
			$this->assignRef("multipleLicenseView", $multipleLicenseView);
			parent::display($tpl);
		}
		else
			echo "model not found";
	}
	
}

?>