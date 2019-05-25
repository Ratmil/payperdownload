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
require_once(JPATH_COMPONENT.'/html/vcalendar.html.php');

/************************************************************
Class to manage sectors
*************************************************************/
class CouponsForm extends PPDForm
{
	/**
	Class constructor
	*/
	function __construct()
	{
		parent::__construct();
		$this->context = 'com_payperdownload.coupons';
		$this->formTitle = JText::_('COM_PAYPERDOWNLOAD_COUPONS');
		$this->toolbarTitle = JText::_('COM_PAYPERDOWNLOAD_COUPONS_TITLE');
		$this->toolbarIcon = 'scissors';
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
			$this->dataBindModel->setKeyField("coupon_id");
			$this->dataBindModel->setTableName("#__payperdownloadplus_coupons");

			$bind = new VisualDataBind('code', JText::_('PAYPERDOWNLOADPLUS_COUPON_CODE'));
			$bind->setColumnWidth(80);
			$bind->setEditLink(true);
			$bind->allowBlank = true;
			$this->dataBindModel->addDataBind( $bind );

			$bind = new VisualDataBind('discount', JText::_('PAYPERDOWNLOADPLUS_COUPON_DISCOUNT'));
			$bind->size = 10;
			$bind->setRegExp("\s*\d+(\.\d+)?\s*");
			$bind->defaultValue = 10;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_COUPON_DISCOUNT_DESC"));
			$this->dataBindModel->addDataBind( $bind );

			$bind = new CalendarVisualDataBind('expire_time', JText::_('PAYPERDOWNLOADPLUS_COUPON_EXPIRATION'));
			$bind->setColumnWidth(10);
			$this->dataBindModel->addDataBind( $bind );
		}
	}

	function onBeforeStore(&$row, $isUpdate)
	{
		$code = $row->code;
		//if empty then generate random code
		if(trim($code) == "")
		{
			$alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
			$code = "";
			for($i = 0; $i < 8; $i++)
			{
				$n = mt_rand(0, strlen($alphabet) - 1);
				$code .= $alphabet[$n];
			}
			$row->code = $code;
		}
		return true;
	}
}
?>