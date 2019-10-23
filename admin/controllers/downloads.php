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
require_once(JPATH_COMPONENT.'/html/vcalendar.html.php');
require_once(JPATH_COMPONENT.'/html/vexcombo.html.php');
require_once(JPATH_COMPONENT.'/html/vdatabindmodel.html.php');
require_once(JPATH_COMPONENT.'/html/payments.html.php');
require_once(JPATH_COMPONENT.'/html/downloads.html.php');

/************************************************************
Class to manage sectors
*************************************************************/
class DownloadsForm extends PPDForm
{
	/**
	Class constructor
	*/
	function __construct()
	{
		parent::__construct();
		$this->context = 'com_payperdownload.downloads';
		$this->formTitle = JText::_('PAYPERDOWNLOADPLUS_DOWNLOAD_LINKS');
		$this->toolbarTitle = JText::_('COM_PAYPERDOWNLOAD_DOWNLOADLINKS_TITLE');
		$this->editItemTitle = JText::_("PAYPERDOWNLOADPLUS_EDIT_DOWNLOAD_LINK");
		$this->toolbarIcon = 'link';
		$this->registerTask('resend');
		$this->registerTask('del');
		$this->registerTask('newdownload');
		$this->registerTask('savenewdownload');
		//use transaction to restrict exclusive access to download_links table
		$this->useTransaction = true;
	}

	function getHtmlObject()
	{
		return new DownloadsHtmlForm();
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
			$this->dataBindModel->setKeyField("download_id");
			$this->dataBindModel->setTableName("#__payperdownloadplus_download_links");

			$bind = new VisualDataBind('payer_email', JText::_('PAYPERDOWNLOADPLUS_PAYER_EMAIL'));
			$bind->setEditLink(true);
			$bind->setColumnWidth(15);
			$bind->setRegExp("\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*");
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_PAYER_EMAIL_DESC"));
			$this->dataBindModel->addDataBind( $bind );

			$bind = new ComboVisualDataBind('resource_id', JText::_('PAYPERDOWNLOADPLUS_RESOURCE'),
				"#__payperdownloadplus_resource_licenses",
				"resource_license_id", "resource_name");
			$bind->showInGrid = true;
			$bind->showInEditForm = false;
			$bind->useForFilter = false;
			$this->dataBindModel->addDataBind( $bind );

			$bind = new VisualDataBind('download_hits', JText::_('PAYPERDOWNLOADPLUS_DOWNLOAD_HITS'));
			$bind->allowBlank = true;
			$bind->setColumnWidth(5);
			$bind->setRegExp("\d+");
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_DOWNLOAD_HITS_DESC"));
			$this->dataBindModel->addDataBind( $bind );

			$bind = new VisualDataBind('link_max_downloads', JText::_('PAYPERDOWNLOADPLUS_DOWNLOAD_MAX'));
			$bind->allowBlank = true;
			$bind->setColumnWidth(5);
			$bind->setRegExp("\d+");
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_DOWNLOAD_MAX_DESC"));
			$this->dataBindModel->addDataBind( $bind );

			$bind = new VisualDataBind('email_subject', JText::_('PAYPERDOWNLOADPLUS_DOWNLOAD_EMAIL_SUBJECT'));
			$bind->allowBlank = false;
			$bind->showInGrid = false;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_DOWNLOAD_EMAIL_SUBJECT_DESC"));
			$this->dataBindModel->addDataBind( $bind );

			$bind = new WYSIWYGEditotVisualDataBind('email_text', JText::_('PAYPERDOWNLOADPLUS_DOWNLOAD_EMAIL_BODY'));
			$bind->showInGrid = false;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_DOWNLOAD_EMAIL_BODY_DESC"));
			$this->dataBindModel->addDataBind( $bind );

			$bind = new CalendarVisualDataBind('expiration_date', JText::_('PAYPERDOWNLOADPLUS_DOWNLOAD_EXPIRATION_DATE'));
			$bind->allowBlank = false;
			$bind->setColumnWidth(10);
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_DOWNLOAD_EXPIRATION_DATE_DESC"));
			$this->dataBindModel->addDataBind( $bind );

			$bind = new RadioVisualDataBind('payed', JText::_('PAYPERDOWNLOADPLUS_PAYED_112'));
			$bind->allowBlank = true;
			$bind->setColumnWidth(5);
			$bind->disabled = true;
			$bind->yes_image = "administrator/components/$option/images/published.png";
			$bind->no_image = "administrator/components/$option/images/unpublished.png";
			$this->dataBindModel->addDataBind( $bind );

		}
	}

	function resend($task, $option)
	{
		$cid = JFactory::getApplication()->input->get('cid', array(0), 'array');
		$cids = implode(', ', $cid);
		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__payperdownloadplus_download_links WHERE download_id IN($cids)";
		$db->setQuery($query);
		$links = $db->loadObjectList();
		$mail = JFactory::getMailer();
		foreach($links as $link)
		{
			$mail->ClearAddresses();
			$mail->setSubject($link->email_subject);
			$mail->setBody($link->email_text);
			if($link->user_email)
				$mail->addRecipient($link->user_email);
			if($link->payer_email)
				$mail->addRecipient($link->payer_email);
			$mail->IsHTML(true);
			$joomla_config = new JConfig();
			$mail->setSender(array($joomla_config->mailfrom, $joomla_config->fromname));
			$mail->Send();
			/*$mail->sendMail($joomla_config->mailfrom, $joomla_config->fromname,
				$link->payer_email, $link->email_subject, $link->email_text);*/
		}
		$this->redirectToList(JText::_("PAYPERDOWNLOADPLUS_RESENT"));
	}

	function del($task, $option)
	{
		$db = JFactory::getDBO();
		$db->setQuery("DELETE FROM #__payperdownloadplus_download_links WHERE expiration_date < NOW()");
		$db->query();
		$this->redirectToList(JText::sprintf("PAYPERDOWNLOADPLUS_EXPIRED_DOWNLOADLINKS_DELETED", $db->getAffectedRows()));
	}

	function getFilters()
	{
		$filters = parent::getFilters();
		$this->addSqlCondition($filters['where'], "#__payperdownloadplus_download_links.payed <> 0");
		return $filters;
	}

	function newdownload($task, $option)
	{
		$this->htmlObject->addNewDownloadLink();
	}

	function savenewdownload($task, $option)
	{
	    $jinput = JFactory::getApplication()->input;

		require_once(JPATH_COMPONENT_SITE . '/models/payresource.php');
		$resource_id = $jinput->getInt('resource_id', 0);
		if($resource_id)
		{
			$payResourceModel = new PayPerDownloadModelPayResource();
			$downloadLink = $payResourceModel->createDownloadLink($resource_id);
			if($downloadLink)
			{
				$download_id = (int)$downloadLink->downloadId;
				$user_email = $jinput->getString('user_email');
				$downloadurl = $payResourceModel->setDownloadLinkPayed($download_id, $resource_id, $user_email, $user_email, JURI::root());
				$payResourceModel->updateDownloadLink($download_id, 'download link', $downloadurl, $downloadurl);
				$this->redirectToList(JText::_("PAYPERDOWNLOADPLUS_DOWNLOADLINK_SUCCESSFULLY_CREATED"));
			}
			else
				$this->redirectToList(JText::_("PAYPERDOWNLOADPLUS_DOWNLOADLINK_NOT_CREATED"));
		}
		else
			$this->redirectToList(JText::_("PAYPERDOWNLOADPLUS_DOWNLOADLINK_NOT_CREATED"));
	}

	/*** Creates toolbar***/
	function createToolbar($task, $option)
	{
		JHTML::_('stylesheet', 'administrator/components/'. $option . '/css/backend.css');
		switch($task)
		{
			case 'edit':
			case 'add':
			case 'apply':
				parent::createToolbar($task, $option);
			break;
			case 'newdownload':
				JToolBarHelper::save('savenewdownload');
				JToolBarHelper::cancel();
				break;
			default:
				JToolBarHelper::addNew('newdownload');
				JToolBarHelper::editList();
				JToolBarHelper::deleteList();
				JToolBarHelper::custom('del', 'delete', '', JText::_('PAYPERDOWNLOADPLUS_DELETE_EXPIRED'), false);
				JToolBarHelper::custom('resend', 'mail-2', '', JText::_('PAYPERDOWNLOADPLUS_RESEND'));
			break;
		}
		JToolBarHelper::title( $this->toolbarTitle, $this->toolbarIcon );
	}

}
?>