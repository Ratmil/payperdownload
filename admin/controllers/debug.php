<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

// no direct access
defined ( '_JEXEC' ) or die;

require_once(JPATH_COMPONENT.'/controllers/ppd.php');
require_once(JPATH_COMPONENT.'/data/gentable.php');
require_once(JPATH_COMPONENT.'/html/vdatabind.html.php');
require_once(JPATH_COMPONENT.'/html/vdatabindmodel.html.php');

/************************************************************
Class to manage sectors
*************************************************************/
class DebugForm extends PPDForm
{
	/**
	Class constructor
	*/
	function __construct()
	{
		parent::__construct();
		$this->context = 'com_payperdownload.debug';
		$this->formTitle = JText::_('PAYPERDOWNLOADPLUS_DEBUG');
		$this->toolbarTitle = JText::_('COM_PAYPERDOWNLOAD_DEBUG_TITLE');
		$this->toolbarIcon = 'wrench';
		$this->registerTask('wipeout');

		$config = JComponentHelper::getParams('com_payperdownload');
		if (!$config->get('debug', false)) {
		    JFactory::getApplication()->enqueueMessage(JText::_('COM_PAYPERDOWNLOAD_DEBUG_WARNING_MESSAGE'), 'warning');
		} else {
		    JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PAYPERDOWNLOAD_DEBUG_INFO_MESSAGE', $config->get('debug_days', '1')), 'info');
		}
	}

	/**
	Create the elements that define how data is to be shown and handled.
	*/
	function createDataBinds()
	{
		if($this->dataBindModel == null)
		{
		    $option = JFactory::getApplication()->input->get('option');

			$this->dataBindModel = new VisualDataBindModel();
			$this->dataBindModel->setKeyField("debug_id");
			$this->dataBindModel->setTableName("#__payperdownloadplus_debug");

			$bind = new VisualDataBind('debug_text', JText::_('PAYPERDOWNLOADPLUS_DEBUG_TEXT'));
			$bind->setColumnWidth(80);
			$this->dataBindModel->addDataBind( $bind );

			$bind = new VisualDataBind('debug_time', JText::_('PAYPERDOWNLOADPLUS_DEBUG_TIME'));
			$bind->setColumnWidth(20);
			$this->dataBindModel->addDataBind( $bind );

			$bind = new VisualDataBind('debug_id', JText::_("PAYPERDOWNLOADPLUS_ID"));
			$bind->setColumnWidth(10);
			$this->dataBindModel->addDataBind( $bind );
		}
	}

	function createToolbar($task, $option)
	{
	    JHTML::_('stylesheet', 'administrator/components/'. $option . '/css/backend.css');

		JToolBarHelper::title( $this->toolbarTitle, $this->toolbarIcon );

		JToolBarHelper::custom('wipeout', 'trash', '', JText::_("JCLEAR"), false);
	}

	function wipeout()
	{
	    $db = JFactory::getDBO();

	    $query = $db->getQuery(true);

	    $query->delete($db->quoteName('#__payperdownloadplus_debug'));

	    $db->setQuery($query);

	    try {
	        $db->execute();
	    } catch (RuntimeException $e) {
            // could not delete records
	    }

	    $this->redirectToList();
	}
}
?>