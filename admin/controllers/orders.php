<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined( '_JEXEC' ) or
die( 'Direct Access to this location is not allowed.' );

/**
 * @author		Ratmil 
 * http://www.ratmilwebsolutions.com
*/

require_once(JPATH_COMPONENT.'/controllers/ppd.php');
require_once(JPATH_COMPONENT.'/data/gentable.php');
require_once(JPATH_COMPONENT.'/html/vdatabind.html.php');
require_once(JPATH_COMPONENT.'/html/vexcombo.html.php');
require_once(JPATH_COMPONENT.'/html/vdatabindmodel.html.php');
require_once(JPATH_COMPONENT.'/html/payments.html.php');

/************************************************************
Class to manage payments
*************************************************************/
class OrdersForm extends PPDForm
{
	/**
	Class constructor
	*/
	function __construct()
	{
		parent::__construct();
		$this->context = 'com_payperdownload.payments';
		$this->formTitle = $this->toolbarTitle = JText::_('PAYPERDOWNLOADPLUS_PAYMENTS_99');
		$this->editItemTitle = JText::_("PAYPERDOWNLOADPLUS_VIEW_PAYMENT_100");
		$this->registerTask('statistics');
		$this->registerTask('back');
		$this->registerTask('del');
	}
	
	function getHtmlObject()
	{
		return new PaymentsHtmlForm();
	}
	
	function display($task, $option)
	{
		?>
		<script type="text/javascript">
		function validatetask(pressbutton)
		{
			var delmsg = '<?php echo JText::_("PAYPERDOWNLOADPLUS_OLD_WILL_BE_DELETED", true); ?>';
			if(pressbutton == 'del')
			{
				if(!confirm(delmsg))
					return false;
			}
			return true;
		}
		</script>
		<?php
		parent::display($task, $option);
	}
	
	/**
	Create the elements that define how data is to be shown and handled. 
	*/
	function createDataBinds()
	{
		if($this->dataBindModel == null)
		{
			$option = JRequest::getVar('option');
		
			$this->dataBindModel = new VisualDataBindModel();
			$this->dataBindModel->setKeyField("payment_id");
			$this->dataBindModel->setTableName("#__payperdownloadplus_payments");
			
			$bind = new VisualDataBind('txn_id', JText::_('PAYPERDOWNLOADPLUS_TRANSACTION_101'));
			$bind->allowBlank = true;
			$bind->setColumnWidth(10);
			$bind->setEditLink(true);
			$bind->disabled = true;
			$bind->gridToolTip = JText::_("PAYPERDOWNLOADPLUS_CLICK_TO_VIEW_102");
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new ExComboVisualDataBind('user_id', JText::_('PAYPERDOWNLOADPLUS_USER_103'), 
				'#__users', 'id', 'name');
			$bind->setColumnWidth(10);
			$bind->allowBlank = true;
			$bind->useForFilter = false;
			$bind->disabled = true;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_JOOMLA_USER_104"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('user_email', JText::_('PAYPERDOWNLOADPLUS_PAYER_EMAIL'));
			$bind->allowBlank = true;
			$bind->setColumnWidth(10);
			$bind->disabled = true;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_PAYER_EMAIL_DESC"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('receiver_email', JText::_('PAYPERDOWNLOADPLUS_PAYMENT_RECEIVER_EMAIL'));
			$bind->allowBlank = true;
			$bind->setColumnWidth(10);
			$bind->disabled = true;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_PAYMENT_RECEIVER_EMAIL_DESC"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new ComboVisualDataBind('license_id', JText::_('PAYPERDOWNLOADPLUS_LICENSE_105'), 
				'#__payperdownloadplus_licenses', 'license_id', 'license_name');
			$bind->setColumnWidth(10);
			$bind->allowBlank = true;
			$bind->disabled = true;
			$bind->showInGrid = false;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_LICENSE_BUYED_BY_THE_USER_106"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new ComboVisualDataBind('resource_id', JText::_('PAYPERDOWNLOADPLUS_PAID_RESOURCE'), 
				'#__payperdownloadplus_resource_licenses', 'resource_license_id', 'resource_name');
			$bind->setColumnWidth(10);
			$bind->allowBlank = true;
			$bind->disabled = true;
			$bind->showInGrid = false;
			$bind->useForFilter = false;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_PAID_RESOURCE_DESC"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new ComboVisualDataBind('affiliate_user_id', JText::_('PAYPERDOWNLOADPLUS_PAYMENT_AFFILIATED_USER'), 
				'#__payperdownloadplus_affiliates_users', 'affiliate_user_id', 'website');
			$bind->setColumnWidth(10);
			$bind->allowBlank = true;
			$bind->disabled = true;
			$bind->showInGrid = false;
			$bind->useForFilter = false;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_PAYMENT_AFFILIATED_USER_DESC"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('amount', JText::_('PAYPERDOWNLOADPLUS_AMOUNT_107'));
			$bind->allowBlank = true;
			$bind->setColumnWidth(5);
			$bind->disabled = true;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('fee', JText::_('PAYPERDOWNLOADPLUS_FEE_108'));
			$bind->allowBlank = true;
			$bind->setColumnWidth(5);
			$bind->showInGrid = true;
			$bind->disabled = true;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('currency', JText::_('PAYPERDOWNLOADPLUS_CURRENCY_109'));
			$bind->allowBlank = true;
			$bind->setColumnWidth(5);
			$bind->showInGrid = true;
			$bind->disabled = true;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('status', JText::_('PAYPERDOWNLOADPLUS_PAYMENT_STATUS_110'));
			$bind->allowBlank = true;
			$bind->setColumnWidth(10);
			$this->requiredMark = '';
			$bind->showInGrid = true;
			$bind->disabled = true;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('payment_date', JText::_('PAYPERDOWNLOADPLUS_PAYMENT_DATE_111'));
			$bind->allowBlank = true;
			$bind->setColumnWidth(10);
			$bind->disabled = true;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new RadioVisualDataBind('payed', JText::_('PAYPERDOWNLOADPLUS_PAYED_112'));
			$bind->allowBlank = true;
			$bind->setColumnWidth(5);
			$bind->disabled = true;
			$bind->yes_image = "administrator/components/$option/images/published.png";
			$bind->no_image = "administrator/components/$option/images/unpublished.png";
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('response', JText::_('PAYPERDOWNLOADPLUS_RESPONSE_113'));
			$bind->allowBlank = true;
			$bind->showInGrid = false;
			$bind->lines = 5;
			$bind->disabled = true;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('validate_response', JText::_('PAYPERDOWNLOADPLUS_VALIDATE_RESPONSE_114'));
			$bind->allowBlank = true;
			$bind->showInGrid = false;
			$bind->lines = 5;
			$bind->disabled = true;
			$this->dataBindModel->addDataBind( $bind );
		}
	}
	
	/*** Creates toolbar***/
	function createToolbar($task, $option)
	{
		JHTML::_('stylesheet', 'administrator/components/'. $option . '/css/backend.css');
		JToolBarHelper::title( $this->toolbarTitle, 'payments.png' );
		switch($task)
		{
			case 'edit':
			case 'add':
			case 'apply':
				JToolBarHelper::cancel();
			break;
			case 'statistics':
				JToolBarHelper::custom('back', 'back', '', JText::_("PAYPERDOWNLOADPLUS_BACK"), false);
				break;
			default:	
				JToolBarHelper::custom('statistics', 'statistics', '', JText::_('PAYPERDOWNLOADPLUS_STATISTICS_115'), false);
				JToolBarHelper::custom('edit', 'preview', '', JText::_('PAYPERDOWNLOADPLUS_VIEW_116'));
				JToolBarHelper::deleteList();
				JToolBarHelper::custom('del', 'delete_ex', '', JText::_('PAYPERDOWNLOADPLUS_DELETE_OLD'), false);
			break;
		}
	}
	
	function back($task, $option)
	{
		$this->redirectToList();
	}
	
	function statistics($task, $option)
	{
		jimport('joomla.utilities.date');
		JHTML::_('stylesheet', 'administrator/components/'. $option . '/css/backend.css');
		$today = new JDate();
		$this_month = (int)$today->format('m', true);
		$this_year = (int)$today->format('Y', true); 
		$db = JFactory::getDBO();
		$statistics = array();
		for($i = 0; $i < 12; $i++)
		{
			$month = $this_month - $i;
			$year = $this_year;
			if($month < 1)
			{
				$month += 12;
				$year -= 1;
			}
			$query = "SELECT SUM(amount) as total_amount, SUM(fee) as total_fee FROM #__payperdownloadplus_payments
				WHERE payed = 1 AND to_merchant = 0 AND MONTH(payment_date) = $month AND YEAR(payment_date) = $year";
			$db->setQuery( $query );
			$stats = $db->loadObject();
			$statistics[$month] = $stats;
		}
		$query = "SELECT SUM(amount) as total_amount, SUM(fee) as total_fee FROM #__payperdownloadplus_payments
			WHERE payed = 1 AND to_merchant = 0";
		$db->setQuery( $query );
		$stats = $db->loadObject();
		$statistics[0] = $stats;
		$this->htmlObject->renderStatistics($statistics, $this_month, $this_year);
	}
	
	function del($task, $option)
	{
		$db = JFactory::getDBO();
		$query = "DELETE FROM #__payperdownloadplus_payments WHERE DATE_ADD(payment_date, INTERVAL 1 MONTH) < NOW() ";
		$db->setQuery( $query );
		$db->query();
		$this->redirectToList(JText::_("PAYPERDOWNLOADPLUS_OLDER_THAN_MONTH_DELETED"));
	}
	
}
?>