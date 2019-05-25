<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class PayPerDownloadViewNoAccess extends JViewLegacy
{
	function display($tpl = null)
	{
	    $option = JFactory::getApplication()->input->get('option');
		JHTML::_('stylesheet', 'components/'. $option . '/css/frontend.css');
		$model = $this->getModel();
		if($model)
		{
			$content = $model->getNoAccessPage();
			$this->assignRef("content", $content);
			parent::display($tpl);
		}
		else
			echo "model not found";
	}
}

?>