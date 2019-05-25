<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class PayPerDownloadViewLicenses extends JViewLegacy
{
	function display($tpl = null)
	{
	    $jinput = JFactory::getApplication()->input;

	    $option = $jinput->get('option');
		JHTML::_('stylesheet', 'components/'. $option . '/css/frontend.css');
		$model = $this->getModel();
		if($model)
		{
		    $limit = $jinput->getInt('limit', 20);
		    $start = $jinput->getInt('limitstart', 0);
			$licenses = $model->getUserLicenses($start, $limit);
			$total = $model->getTotalLicenses();
			jimport( 'joomla.html.pagination' );
			$objPagination = new JPagination( $total, $start, $limit );
			$this->assignRef("pagination", $objPagination);
			$this->assignRef("licenses", $licenses);
			parent::display($tpl);
		}
		else
			echo "model not found";
	}
}

?>