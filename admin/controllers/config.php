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
require_once(JPATH_COMPONENT.'/html/vdatabindcb.html.php');
require_once(JPATH_COMPONENT.'/html/menuitem.html.php');
require_once(JPATH_COMPONENT.'/html/vdatabindmodel.html.php');
require_once(JPATH_COMPONENT.'/html/vdgroupsselect.html.php');

/************************************************************
Class to manage configuration
*************************************************************/
class ConfigForm extends PPDForm
{
	/**
	Class constructor
	*/
	function __construct()
	{
		parent::__construct();
		$this->context = 'com_payperdownload.config';
		$this->formTitle = $this->toolbarTitle = JText::_('PAYPERDOWNLOADPLUS_CONFIGURATION');
		$this->editItemTitle = JText::_("PAYPERDOWNLOADPLUS_CONFIGURATION");
		$this->newItemTitle = JText::_("PAYPERDOWNLOADPLUS_CONFIGURATION");
		$this->toolbarIcon = 'config.png';
	}
	
	/**
	Create the elements that define how data is to be shown and handled. 
	*/
	function createDataBinds()
	{
		if($this->dataBindModel == null)
		{
			$this->dataBindModel = new VisualDataBindModel();
			$this->dataBindModel->setKeyField("config_id");
			$this->dataBindModel->setTableName("#__payperdownloadplus_config");
			
			$this->dataBindModel->newFieldSet("Gateway", JText::_("PAYPERDOWNLOADPLUS_PAYMENT_PANE"));
			
			$bind = new RadioVisualDataBind('usepaypal', JText::_('PAYPERDOWNLOADPLUS_USE_PAYPAL'));
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_USE_PAYPAL_DESC"));
			$bind->defaultValue = 1;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new RadioVisualDataBind('usepayplugin', JText::_('PAYPERDOWNLOADPLUS_USE_PAYMENT_PLUGIN'));
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_USE_PAYMENT_PLUGIN_DESC"));
			$bind->defaultValue = 0;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('paypalaccount', JText::_('PAYPERDOWNLOADPLUS_CONFIG_PAYPAL_ACCOUNT_140'));
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_CONFIG_PAYPAL_ACCOUNT_DESC_141"));
			$bind->allowBlank = true;
			$bind->setRegExp("\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*");
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new RadioVisualDataBind('testmode', JText::_('PAYPERDOWNLOADPLUS_CONFIG_TEST_MODE_148'));
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_CONFIG_IF_USING_TEST_MODE_OR_NOT_149"));
			$bind->defaultValue = 1;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new RadioVisualDataBind('usesimulator', JText::_('PAYPERDOWNLOADPLUS_CONFIG_USE_SIMULATOR'));
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_CONFIG_USE_SIMULATOR_DESC"));
			$bind->defaultValue = 0;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new RadioVisualDataBind('apply_discount', JText::_('PAYPERDOWNLOADPLUS_APPLY_DISCOUNT'));
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_APPLY_DISCOUNT_DESC"));
			$bind->defaultValue = 1;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new RadioVisualDataBind('apply_discount_renew', JText::_('PAYPERDOWNLOADPLUS_APPLY_RENEW_DISCOUNT'));
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_APPLY_RENEW_DISCOUNT_DESC"));
			$bind->defaultValue = 1;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('renew_discount_percent', JText::_('PAYPERDOWNLOADPLUS_RENEW_DISCOUNT_PERCENT'));
			$bind->size = 10;
			$bind->setRegExp("\s*\d+(\.\d+)?\s*");
			$bind->allowBlank = true;
			$bind->defaultValue = 10;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_RENEW_DISCOUNT_PERCENT_DESC"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new RadioVisualDataBind('use_discount_coupon', JText::_('PAYPERDOWNLOADPLUS_APPLY_DISCOUNT_COUPONS'));
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_APPLY_DISCOUNT_COUPONS_DESC"));
			$bind->defaultValue = 0;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new RadioVisualDataBind('askemail', JText::_('PAYPERDOWNLOADPLUS_ASK_USER_EMAIL'));
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_ASK_USER_EMAIL_DESC"));
			$bind->defaultValue = 0;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualEditCheckboxDataBind('tax_rate', JText::_('PAYPERDOWNLOADPLUS_TAX_RATE'), 
				JText::_("PAYPERDOWNLOADPLUS_NO_TAX"));
			$bind->size = 10;
			$bind->setRegExp("\s*\d+(\.\d+)?\s*");
			$bind->allowBlank = true;
			$bind->defaultValue = 0;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_TAX_RATE_DESC"));
			$this->dataBindModel->addDataBind( $bind );
			
			$this->dataBindModel->newFieldSet("Notification", JText::_("PAYPERDOWNLOADPLUS_NOTIFICATION_PANE"));
			
			$bind = new VisualDataBind('paymentnotificationemail', JText::_('PAYPERDOWNLOADPLUS_CONFIG_PAYMENT_NOTIFICATION_EMAIL_142'));
			$bind->setColumnWidth(20);
			$bind->setRegExp("\s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*(;\s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*)*");
			$bind->allowBlank = true;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_CONFIG_EMAIL_ACCOUNT_TO_SEND_PAYMENT_NOTIFICATIONS_143"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('notificationsubject', JText::_('PAYPERDOWNLOADPLUS_CONFIG_NOTIFICATION_EMAIL_SUBJECT_144'));
			$bind->setColumnWidth(20);
			$bind->allowBlank = true;
			$bind->defaultValue = "You have received a payment, amount: {amount} {currency}";
			$bind->size = 100;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_CONFIG_EMAIL_BODY_SUBJECT_THAT_WILL_BE_SENT_FOR_A_PAYMENT_NOTIFICATION_145"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new WYSIWYGEditotVisualDataBind('notificationtext', JText::_('PAYPERDOWNLOADPLUS_CONFIG_NOTIFICATION_EMAIL_BODY_146'));
			$bind->setColumnWidth(20);
			$bind->allowBlank = true;
			$bind->defaultValue = "You have received a payment, amount: {amount} {currency}, fee: {fee} {currency}.<br/>From: {user} ({user_email})";
			$bind->setLineCount(10);
			$bind->size = 80;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_CONFIG_EMAIL_BODY_TEXT_THAT_WILL_BE_SENT_FOR_A_PAYMENT_NOTIFICATION_147") . "<br/>".
				JText::_("PAYPERDOWNLOADPLUS_NOTIFICATION_EMAIL_TAGS"));
			$bind->setExtraDescription(JText::_("PAYPERDOWNLOADPLUS_NOTIFICATION_EMAIL_TAGS"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('usernotificationsubject', JText::_('PAYPERDOWNLOADPLUS_CONFIG_USER_NOTIFICATION_EMAIL_SUBJECT'));
			$bind->setColumnWidth(20);
			$bind->allowBlank = true;
			$bind->defaultValue = "You have bought a license.";
			$bind->size = 100;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_CONFIG_USER_NOTIFICATION_EMAIL_SUBJECT_DESC"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new WYSIWYGEditotVisualDataBind('usernotificationtext', JText::_('PAYPERDOWNLOADPLUS_CONFIG_USER_NOTIFICATION_EMAIL_BODY'));
			$bind->setColumnWidth(20);
			$bind->allowBlank = true;
			$bind->setLineCount(10);
			$bind->defaultValue = "You have bought a license.<br/> License: {license}";
			$bind->size = 80;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_CONFIG_USER_NOTIFICATION_EMAIL_BODY_DESC")  . "<br/>".
				JText::_("PAYPERDOWNLOADPLUS_NOTIFICATION_EMAIL_TAGS"));
			$bind->setExtraDescription(JText::_("PAYPERDOWNLOADPLUS_NOTIFICATION_EMAIL_TAGS"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('guestnotificationsubject', JText::_('PAYPERDOWNLOADPLUS_CONFIG_GUEST_NOTIFICATION_EMAIL_SUBJECT'));
			$bind->setColumnWidth(20);
			$bind->allowBlank = true;
			$bind->defaultValue = "You have bought a license.";
			$bind->size = 100;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_CONFIG_GUEST_NOTIFICATION_EMAIL_SUBJECT_DESC"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new WYSIWYGEditotVisualDataBind('guestnotificationtext', JText::_('PAYPERDOWNLOADPLUS_CONFIG_GUEST_NOTIFICATION_EMAIL_BODY'));
			$bind->setColumnWidth(20);
			$bind->allowBlank = true;
			$bind->setLineCount(10);
			$bind->defaultValue = "You have bought a license.<br/> License: {license}. Download: {download_link}";
			$bind->size = 80;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_CONFIG_GUEST_NOTIFICATION_EMAIL_BODY_DESC") . "<br/>".
				JText::_("PAYPERDOWNLOADPLUS_GUEST_NOTIFICATION_EMAIL_TAGS"));
			$bind->setExtraDescription(JText::_("PAYPERDOWNLOADPLUS_GUEST_NOTIFICATION_EMAIL_TAGS"));
			$this->dataBindModel->addDataBind( $bind );
			
			$this->dataBindModel->newFieldSet("Backend", JText::_("PAYPERDOWNLOADPLUS_BACKEND_SETTINGS"));
			
			$bind = new RadioVisualDataBind('show_hints', JText::_('PAYPERDOWNLOADPLUS_SHOW_HINTS'));
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_SHOW_HINTS_DESC"));
			$bind->defaultValue = 1;
			$this->dataBindModel->addDataBind( $bind );
			
			$this->dataBindModel->newFieldSet("Frontend", JText::_("PAYPERDOWNLOADPLUS_FRONTEND_PANE"));
			
			/*$bind = new ComboVisualDataBind('multilicenseview', JText::_('PAYPERDOWNLOADPLUS_MULTILICENSE_VIEW'), "", "", "");
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_MULTILICENSE_VIEW_DESC"));
			$bind->defaultValue = 0;
			$bind->addItem(0, JText::_("PAYPERDOWNLOADPLUS_MULTILICENSE_VIEW_ROW"));
			$bind->addItem(1, JText::_("PAYPERDOWNLOADPLUS_MULTILICENSE_VIEW_SELECT"));
			$this->dataBindModel->addDataBind( $bind );*/
			
			$bind = new RadioVisualDataBind('showresources', JText::_('PAYPERDOWNLOADPLUS_CONFIG_SHOW_RESOURCES'));
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_CONFIG_SHOW_RESOURCES_DESC"));
			$bind->defaultValue = 0;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new RadioVisualDataBind('show_login', JText::_('PAYPERDOWNLOADPLUS_SHOW_LOGIN_IN_PAYVIEWS'));
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_SHOW_LOGIN_IN_PAYVIEWS_DESC"));
			$bind->defaultValue = 1;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new RadioVisualDataBind('show_quick_register', JText::_('PAYPERDOWNLOADPLUS_SHOW_QUICK_REGISTER'));
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_SHOW_QUICK_REGISTER_DESC"));
			$bind->defaultValue = 1;
			$this->dataBindModel->addDataBind( $bind );
			
			/*$bind = new RadioVisualDataBind('use_osol_captcha', JText::_('PAYPERDOWNLOADPLUS_QUICKREGISTER_OSOL_CAPTCHA'));
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_QUICKREGISTER_OSOL_CAPTCHA_DESC"));
			$bind->defaultValue = 1;
			$this->dataBindModel->addDataBind( $bind );*/
			
			$bind = new WYSIWYGEditotVisualDataBind('thank_you_page', JText::_('PAYPERDOWNLOADPLUS_PAYMENT_RETURN_PAGE'));
			$bind->showInGrid = false;
			$bind->allowBlank = true;
			$bind->defaultValue = JText::_("PAYPERDOWNLOADPLUS_PAYMENT_RETURN_PAGE_DEFAULT_VALUE");
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_PAYMENT_RETURN_PAGE_DESC"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new WYSIWYGEditotVisualDataBind('thank_you_page_resource', JText::_('PAYPERDOWNLOADPLUS_PAYMENT_RETURN_PAGE_RESOURCE'));
			$bind->showInGrid = false;
			$bind->allowBlank = true;
			$bind->defaultValue = JText::_("PAYPERDOWNLOADPLUS_PAYMENT_RETURN_PAGE_RESOURCE_DEFAULT_VALUE");
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_PAYMENT_RETURN_PAGE_RESOURCE_DESC"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new WYSIWYGEditotVisualDataBind('payment_header', JText::_('PAYPERDOWNLOADPLUS_PAY_VIEW_HEADER'));
			$bind->showInGrid = false;
			$bind->allowBlank = true;
			$bind->defaultValue = "";
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_PAY_VIEW_HEADER_DESC"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new WYSIWYGEditotVisualDataBind('resource_payment_header', JText::_('PAYPERDOWNLOADPLUS_RESOURCE_PAY_VIEW_HEADER'));
			$bind->showInGrid = false;
			$bind->allowBlank = true;
			$bind->defaultValue = "";
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_RESOURCE_PAY_VIEW_HEADER_DESC"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new WYSIWYGEditotVisualDataBind('alternate_pay_license_header', JText::_('PAYPERDOWNLOADPLUS_ALTERNATE_BUY_LICENSE'));
			$bind->showInGrid = false;
			$bind->allowBlank = true;
			$bind->defaultValue = "";
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_ALTERNATE_BUY_LICENSE_DESC"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new RadioVisualDataBind('usenoaccesspage', JText::_('PAYPERDOWNLOADPLUS_USE_NOACCESS_PAGE'));
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_USE_NOACCESS_PAGE_DESC"));
			$bind->defaultValue = 0;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new WYSIWYGEditotVisualDataBind('noaccesspage', JText::_('PAYPERDOWNLOADPLUS_NOACCESS_PAGE'));
			$bind->showInGrid = false;
			$bind->allowBlank = true;
			$bind->defaultValue = "";
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_NOACCESS_PAGE_DESC"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new ComboVisualDataBind('license_sort', JText::_('PAYPERDOWNLOADPLUS_LICENSE_ORDERING'), "", "", "");
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_LICENSE_ORDERING_DESC"));
			$bind->defaultValue = 1;
			for($i = 1; $i <= 6; $i++)
				$bind->addItem($i, JText::_("PAYPERDOWNLOADPLUS_LICENSE_ORDERING_" . $i));
			$this->dataBindModel->addDataBind( $bind );
			
			$this->dataBindModel->newFieldSet("Menuitems", JText::_("PAYPERDOWNLOADPLUS_MENU_ITEMS_PANE"));
			
			$bind = new MenuItemVisualDataBind("payment_page_menuitem", JText::_("PAYPERDOWNLOADPLUS_CONFIG_PAYMENT_PAGE_MENU_ITEM"));
			$bind->showInGrid = false;
			$bind->allowBlank = true;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_CONFIG_PAYMENT_PAGE_MENU_ITEM_DESC"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new MenuItemVisualDataBind("thankyou_page_menuitem", JText::_("PAYPERDOWNLOADPLUS_CONFIG_THANKYOU_PAGE_MENU_ITEM"));
			$bind->showInGrid = false;
			$bind->allowBlank = true;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_CONFIG_THANKYOU_PAGE_MENU_ITEM_DESC"));
			$this->dataBindModel->addDataBind( $bind );
			
			$this->dataBindModel->newFieldSet("IntegrationPane", JText::_("PAYPERDOWNLOADPLUS_INTEGRATION"));
			
			$bind = new RadioVisualDataBind('show_license_on_kunena', JText::_('PAYPERDOWNLOADPLUS_SHOW_LICENSE_ON_KUNENA'));
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_SHOW_LICENSE_ON_KUNENA_DESC"));
			$bind->defaultValue = 0;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new ComboVisualDataBind('alphapoints', JText::_('PAYPERDOWNLOADPLUS_AUP_CONFIG'), "", "", "");
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_AUP_CONFIG_DESC"));
			$bind->defaultValue = 0;
			$bind->addItem(0, JText::_("PAYPERDOWNLOADPLUS_AUP_NO_INTEGRATION"));
			$bind->addItem(1, JText::_("PAYPERDOWNLOADPLUS_AUP_ASSIGN_POINTS_FOR_BUYING"));
			$bind->addItem(2, JText::_("PAYPERDOWNLOADPLUS_AUP_USE_POINTS_TO_BUY"));
			$this->dataBindModel->addDataBind( $bind );
			
			$this->dataBindModel->newFieldSet("OthersettingsPane", JText::_("PAYPERDOWNLOADPLUS_OTHER_SETTINGS"));
			
			$bind = new MultipleJoomlaGroupsSelect("privilege_groups", JText::_("PAYPERDOWNLOADPLUS_PRIVILEGED_GROUPS"));
			$bind->showInGrid = false;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_PRIVILEGED_GROUPS_DESC"));
			$this->dataBindModel->addDataBind( $bind );
		}
	}
	
	function showForm($task, $option)
	{
		if($task == 'display' || $task == '')
		{
			JRequest::setVar('task', 'edit');
			$task = 'edit';
		}
		parent::showForm($task, $option);
	}
	
	function edit($task, $option)
	{
		if($this->editItemScripts != null)
		{
			$scriptPath = "administrator/components/$option/js/";
			foreach($this->editItemScripts as $script)
			{
				JHTML::script($scriptPath . script, false);
			}
		}
		$db = JFactory::getDBO();
		$db->setQuery("SELECT config_id FROM #__payperdownloadplus_config");
		$id = $db->loadResult();
		if($id)
		{
			$row = $this->getTableObject();
			if($row->load($id))
			{
				$row->config_id = null;
				$this->htmlObject->edit($option, $task, $row, $this->dataBindModel, $this->editItemTitle);
			}
			else
			{
				$msg = JText::_("PAYPERDOWNLOADPLUS_ERROR_LOADING_DATA_80");
				$this->redirectToList($msg, "error");
			}
		}
		else
		{
			$this->htmlObject->add($option, $task, $this->dataBindModel, $this->editItemTitle);
		}
	}
	
	function createToolbar($task, $option)
	{
		JToolBarHelper::title( $this->toolbarTitle, $this->toolbarIcon );
		JToolBarHelper::apply();
		// Options button.
		$version = new JVersion;
		if($version->RELEASE >= "2.5")
			if (JFactory::getUser()->authorise('core.admin', 'com_payperdownload')) 
			{
				JToolBarHelper::preferences('com_payperdownload');
			}
	}
	
	function store()
	{
		$db = JFactory::getDBO();
		$db->setQuery("DELETE FROM #__payperdownloadplus_config");
		$db->query();
		return parent::store();
	}
}
?>