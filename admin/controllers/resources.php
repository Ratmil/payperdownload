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
require_once(JPATH_COMPONENT.'/html/vdatabindmodel.html.php');
require_once(JPATH_COMPONENT.'/html/resources.html.php');

/************************************************************
Class to manage sectors
*************************************************************/
class ResourcesForm extends PPDForm
{
	/**
	Class constructor
	*/
	function __construct()
	{
		parent::__construct();
		$this->context = 'com_payperdownload.resources';
		$this->formTitle = $this->toolbarTitle = JText::_('PAYPERDOWNLOADPLUS_RESOURCES_117');
		$this->editItemTitle = JText::_("PAYPERDOWNLOADPLUS_EDIT_RESOURCE_LICENSE_118");
		$this->newItemTitle = JText::_("PAYPERDOWNLOADPLUS_NEW_RESOURCE_LICENSE_119");
		$this->registerTask('newresource');
		$this->registerTask('acceptnewresourcetype');
		$this->registerTask('acceptnewresource');
		$this->registerTask('modalwindow');
		$this->registerTask('copy');
	}
	
	function getHtmlObject()
	{
		return new ResourcesHtmlForm();
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
			$this->dataBindModel->setKeyField("resource_license_id");
			$this->dataBindModel->setTableName("#__payperdownloadplus_resource_licenses");
			
			$bind = new VisualDataBind('resource_id', JText::_('PAYPERDOWNLOADPLUS_RESOURCE_120'));
			$bind->disabledEdit = true;
			$bind->useForFilter = false;
			$bind->showInGrid = false;
			$bind->showInInsertForm = false;
			$bind->showInEditForm = false;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('resource_name', JText::_('PAYPERDOWNLOADPLUS_RESOURCE_NAME_121'));
			$bind->setColumnWidth(25);
			$bind->setEditLink(true);
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('resource_type', JText::_('PAYPERDOWNLOADPLUS_RESOURCE_TYPE_122'));
			$bind->disabledEdit = true;
			$bind->setColumnWidth(20);
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('resource_description', JText::_('PAYPERDOWNLOADPLUS_DESCRIPTION_123'));
			$bind->setColumnWidth(25);
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('alternate_resource_description', JText::_('PAYPERDOWNLOADPLUS_ALTERNATE_DESCRIPTION_124'));
			$bind->showInGrid = false;
			$bind->allowBlank = true;
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new ComboVisualDataBind('license_id', JText::_('PAYPERDOWNLOADPLUS_LICENSE_125'), 
				'#__payperdownloadplus_licenses', 'license_id', 'license_name');
			$bind->setColumnWidth(25);
			$bind->showInEditForm = false;
			$bind->allowBlank = true;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_LICENSE_TO_APPLY_TO_RESOURCE_126"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('resource_price', JText::_('PAYPERDOWNLOADPLUS_PRICE_92'), 'resource_price_currency');
			$bind->showInGrid = false;
			$bind->showInEditForm = false;
			$bind->allowBlank = true;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_RESOURCE_PRICE"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('download_expiration', JText::_('PAYPERDOWNLOADPLUS_DOWNLOAD_LINK_EXPIRATION'));
			$bind->showInGrid = false;
			$bind->showInEditForm = false;
			$bind->allowBlank = true;
			$bind->setRegExp("\\s*\\d+\\s*");
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_DOWNLOAD_LINK_EXPIRATION_DESC"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new VisualDataBind('max_download', JText::_('PAYPERDOWNLOADPLUS_MAX_DOWNLOAD_COUNT'));
			$bind->setColumnWidth(20);
			$bind->setRegExp("\\s*\\d+\\s*");
			$bind->showInGrid = false;
			$bind->showInEditForm = false;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_MAX_DOWNLOAD_COUNT_DESC"));
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new RadioVisualDataBind('enabled', JText::_('PAYPERDOWNLOADPLUS_RESOURCE_ENABLED'));
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_RESOURCE_ENABLED_DESC"));
			$bind->defaultValue = 1;
			$bind->setColumnWidth(5);
			$bind->yes_task = "unpublish";
			$bind->no_task = "publish";
			$bind->yes_image = "administrator/components/$option/images/published.png";
			$bind->no_image = "administrator/components/$option/images/unpublished.png";
			$this->dataBindModel->addDataBind( $bind );
			
			$bind = new WYSIWYGEditotVisualDataBind('payment_header', JText::_('PAYPERDOWNLOADPLUS_RESOURCE_PAYMENT_HEADER'));
			$bind->showInGrid = false;
			$bind->allowBlank = true;
			$bind->showInEditForm = false;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_RESOURCE_PAYMENT_HEADER_DESC"));
			$this->dataBindModel->addDataBind( $bind );
		}
	}
	
	function newresource($task, $option)
	{
		JHTML::_('stylesheet', 'administrator/components/'. $option . '/css/backend.css');
		$this->htmlObject->renderPlugins($task, $option);
		echo "<br/>";
		echo JText::sprintf('PAYPERDOWNLOADPLUS_DOWNLOAD_PLUGINS', '<a href="http://www.ratmilwebsolutions.com">www.ratmilwebsolutions.com</a>');
	}
	
	function acceptnewresourcetype($task, $option)
	{
		$resourceType = JRequest::getVar('resourcetype');
		$this->htmlObject->renderPluginConfig($task, $option, $resourceType);
	}
	
	function edit($task, $option, $resourceParams = null, $resourceId = null, $loadFromPost = false)
	{
		if($loadFromPost)
		{
			$row = $this->loadFromPost();
			$row->resource_id = $resourceId;
			if($resourceParams)
			{
				$row->resource_params = $resourceParams;
			}
		}
		else
		{
			$cid = JRequest::getVar('cid', array(0), '', 'array' );
			$id = (int)$cid[0];
			$db = JFactory::getDBO();
			$db->setQuery("SELECT * FROM #__payperdownloadplus_resource_licenses WHERE resource_license_id = " . $id);
			$row = $db->loadObject();
		}
		if($row)
		{
			$this->htmlObject->renderPluginConfigEdit($task, $option, $this->dataBindModel, $row);
		}
		else
		{
			$msg = htmlspecialchars($this->loadErrorMessage);
			$this->setError($row->getError());
			if($this->debugModeOn)
			{
				$msg .= "<br/>" . $row->getError();
			}
			$this->redirectToList($msg, "error");
		}
	}
	
	function loadFromPost()
	{
		$row = new stdClass();
		$properties = array('resourceType', 'resource_license_id', 'license_id', 'resource_price', 'resource_price_currency', 
			'download_expiration', 'payment_header', 'max_download', 'shared');
		$dataBinds = $this->dataBindModel->dataBinds;
		for ($i=0, $n=count( $dataBinds ); $i < $n; $i++)
		{
			$databind = $dataBinds[$i];
			if($databind->showInEditForm)
			{
				$properties[] = $databind->dataField;
			}
		}
		foreach($properties as $property)
		{
			$row->$property = JRequest::getVar($property);
		}
		return $row;
	}
	
	function redirectToListOrEdit($toList, $msg, $resourceParams, $resourceId, $type = "message")
	{
		if($toList)
			$this->redirectToList($msg, $type);
		else
		{
			$mainframe = JFactory::getApplication();
			$mainframe->enqueueMessage($msg, $type);
			$this->edit('edit', 'com_payperdownload', $resourceParams, $resourceId, true);
		}
	}
	
	function update_resource($redirect_to_list)
	{
		$db = JFactory::getDBO();
		$resourceType = JRequest::getVar('resourceType');
		JPluginHelper::importPlugin("payperdownloadplus");
		$dispatcher	= JDispatcher::getInstance();
		$resource_license_id = JRequest::getInt('resource_license_id');
		$result = $dispatcher->trigger('onGetSaveData', 
			array (&$resourceId, $resourceType, 
				&$resourceName, &$resourceParams, &$optionParameter,
				&$resourceDesc));
		$license_id = JRequest::getInt('license_id', 0);
		$resource_price = JRequest::getVar('resource_price');
		$download_expiration = JRequest::getInt('download_expiration');
		$max_download = JRequest::getInt('max_download', 0);
		$shared = JRequest::getInt('shared', 1);
		$payment_header = JRequest::getVar( 'payment_header', '', 'post','string', JREQUEST_ALLOWRAW );
		if(!$license_id)
		{
			if(!preg_match('/^\s*\d+(\.\d+)?\s*$/', $resource_price))
			{
				$this->redirectToList(JText::_("PAYPERDOWNLOADPLUS_INVALID_PRICE"), "error");
				exit;
			}
			$resource_price = (float)$resource_price;
			$resource_price_currency = JRequest::getVar('resource_price_currency');
			$resource_price_currency = "'" . $db->escape($resource_price_currency) . "'";
			$license_id = "NULL";
		}
		else
		{
			$resource_price = "NULL";
			$resource_price_currency = "NULL";
			$download_expiration = "NULL";
			$max_download = 0;
		}
		if($license_id)
		{
			if($resourceId && $resourceName && $resource_license_id)
			{
				$new_resource_name = JRequest::getVar('resource_name');
				$new_desc = JRequest::getVar('resource_description');
				$alt_desc = JRequest::getVar('alternate_resource_description');
				if($new_resource_name)
					$resourceName = $new_resource_name;
				if($new_desc)
					$resourceDesc = $new_desc;
				$db = JFactory::getDBO();		
				$resourceId = (int)$resourceId;
				$optionParameter = $db->escape($optionParameter);
				$resourceType = $db->escape($resourceType);
				$resourceName = $db->escape($resourceName);
				$resourceDesc = $db->escape($resourceDesc);
				$resourceParams = $db->escape($resourceParams);
				$alt_desc = $db->escape($alt_desc);
				$payment_header = $db->escape($payment_header);
				$query = "UPDATE #__payperdownloadplus_resource_licenses
					SET license_id = $license_id,
					resource_id = $resourceId,
					resource_type = '$resourceType',
					resource_name = '$resourceName',
					resource_description = '$resourceDesc',
					resource_option_parameter = '$optionParameter',
					resource_price = $resource_price,
					resource_price_currency = $resource_price_currency,
					resource_params = '$resourceParams',
					alternate_resource_description = '$alt_desc',
					download_expiration = $download_expiration,
					payment_header = '$payment_header',
					max_download = $max_download,
					shared = $shared
					WHERE resource_license_id = " . (int)$resource_license_id;
				$db->setQuery( $query );
				if($db->query())
				{
					$this->redirectToListOrEdit($redirect_to_list, JText::_("PAYPERDOWNLOADPLUS_RESOURCE_SUCCESSFULL_SAVED_127"), $resourceParams, $resourceId);
				}
				else
					$this->redirectToListOrEdit(false, $db->stderr(), $resourceParams, $resourceId, "error");
			}
			else
				$this->redirectToListOrEdit(false, JText::_("PAYPERDOWNLOADPLUS_YOU_FORGOT_TO_SELECT_RESOURCE_128"), $resourceParams, $resourceId, "error");
		}
		else
			$this->redirectToListOrEdit(false, JText::_("PAYPERDOWNLOADPLUS_YOU_FORGOT_TO_SELECT_LICENSE_129"), $resourceParams, $resourceId, "error");
	}
	
	
	function save_resource($redirect_to_list)
	{
		$db = JFactory::getDBO();
		$license_id = JRequest::getInt('license_id', 0);
		$resource_price = JRequest::getVar('resource_price');
		$download_expiration = JRequest::getInt('download_expiration');
		$payment_header = JRequest::getVar( 'payment_header', '', 'post','string', JREQUEST_ALLOWRAW );
		$max_download = JRequest::getInt('max_download', 0);
		$shared = JRequest::getInt('shared', 1);
		if(!$license_id)
		{
			if(!preg_match('/^\s*\d+(\.\d+)?\s*$/', $resource_price))
			{
				$this->redirectToList(JText::_("PAYPERDOWNLOADPLUS_INVALID_PRICE"), "error");
				exit;
			}
			$resource_price = (float)$resource_price;
			$resource_price_currency = JRequest::getVar('resource_price_currency');
			$resource_price_currency = "'" . $db->escape($resource_price_currency) . "'";
			$license_id = "NULL";
		}
		else
		{
			$resource_price = "NULL";
			$resource_price_currency = "NULL";
			$download_expiration = "NULL";
			$max_download = 0;
		}
		if($license_id)
		{
			$resourceType = JRequest::getVar('resourceType');
			JPluginHelper::importPlugin("payperdownloadplus");
			$dispatcher	= JDispatcher::getInstance();
			$result = $dispatcher->trigger('onGetSaveData', 
				array (&$resourceId, $resourceType, 
					&$resourceName, &$resourceParams, &$optionParameter,
					&$resourceDesc));
			if($resourceId && $resourceName)
			{
				$resourceId = (int)$resourceId;
				$optionParameter = $db->escape($optionParameter);
				$resourceType = $db->escape($resourceType);
				$resourceName = $db->escape($resourceName);
				$resourceDesc = $db->escape($resourceDesc);
				$resourceParams = $db->escape($resourceParams);
				$payment_header = $db->escape($payment_header);
				$query = "INSERT INTO 
					#__payperdownloadplus_resource_licenses(
					license_id,
					resource_id,
					resource_type,
					resource_name,
					resource_description,
					resource_option_parameter,
					resource_price,
					resource_price_currency,
					resource_params,
					download_expiration,
					payment_header,
					max_download,
					shared)
					VALUES($license_id, '$resourceId', '$resourceType', '$resourceName', 
					'$resourceDesc', 
					'$optionParameter', 
					$resource_price,
					$resource_price_currency,
					'$resourceParams',
					$download_expiration,
					'$payment_header', 
					$max_download,
					$shared)";
				$db->setQuery( $query );
				if($db->query())
				{
					$this->redirectToList(JText::_("PAYPERDOWNLOADPLUS_RESOURCE_SUCCESSFULL_SAVED_127"));
				}
				else
					$this->redirectToList($db->stderr(), "error");
			}
			else
				$this->redirectToList(JText::_("PAYPERDOWNLOADPLUS_YOU_FORGOT_TO_SELECT_RESOURCE_128"), "error");
		}
		else
			$this->redirectToList(JText::_("PAYPERDOWNLOADPLUS_YOU_FORGOT_TO_SELECT_LICENSE_129"), "error");
	}
	
	function save($task, $option)
	{
		$this->update_resource(true);
	}
	
	function apply($task, $option)
	{
		$this->update_resource(false);
	}
	
	function acceptnewresource($task, $option)
	{
		$this->save_resource(true);
	}
	
	function createToolbar($task, $option)
	{
		JHTML::_('stylesheet', 'administrator/components/'. $option . '/css/backend.css');
		JToolBarHelper::title( $this->toolbarTitle, 'resources.png' );
		switch($task)
		{
			case 'edit':
			case 'add':
			case 'apply':
				JToolBarHelper::save();
				JToolBarHelper::apply();
				JToolBarHelper::cancel();
			break;
			case 'newresource':
				JToolBarHelper::custom('acceptnewresourcetype', 'forward', '', JText::_("PAYPERDOWNLOADPLUS_NEXT_130"), false);
				JToolBarHelper::cancel();
				break;
			case 'acceptnewresourcetype':
				JToolBarHelper::save('acceptnewresource');
				JToolBarHelper::cancel();
				break;
			default:
				JToolBarHelper::addNew('newresource');
				JToolBarHelper::custom('copy', 'copy', '', JText::_("PAYPERDOWNLOADPLUS_COPY"), true);
				JToolBarHelper::editList();
				JToolBarHelper::deleteList();
				JToolBarHelper::publish();
				JToolBarHelper::unpublish();
			break;
		}
	}
	
	function copy($task, $option)
	{
		$cid = JRequest::getVar('cid', array(0), '', 'array' );
		$id = (int)$cid[0];
		$copy_suffix = JText::_("PAYPERDOWNLOADPLUS_COPY_TEXT");
		$db = JFactory::getDBO();
		$db->setQuery(
			"INSERT INTO #__payperdownloadplus_resource_licenses
			(
				license_id, resource_id, resource_name, resource_description,
				alternate_resource_description, resource_type, resource_option_parameter,
				resource_params, resource_price, resource_price_currency, download_expiration,
				max_download, shared
			)
			SELECT 
			license_id, resource_id, CONCAT(resource_name, ' - " . $db->escape($copy_suffix) . "'),
			resource_description, alternate_resource_description, resource_type, 
			resource_option_parameter, resource_params, resource_price, resource_price_currency, 
			download_expiration, max_download, shared
			FROM #__payperdownloadplus_resource_licenses
			WHERE resource_license_id = $id
			");
		$db->query();
		$this->redirectToList();
	}
	
	function ajaxCall($task, $option)
	{
		JPluginHelper::importPlugin("payperdownloadplus");
		$dispatcher	= JDispatcher::getInstance();
		$plugin = JRequest::getVar('plugin');
		$output = "";
		$result = $dispatcher->trigger('onAjaxCall', array ($plugin, &$output));
		echo $output;
	}
	
	function modalwindow($task, $option)
	{
		JPluginHelper::importPlugin("payperdownloadplus");
		$dispatcher	= JDispatcher::getInstance();
		$plugin = JRequest::getVar('plugin');
		$output = "";
		$result = $dispatcher->trigger('onModalWindow', array ($plugin, &$output));
		echo $output;
	}
}
?>
