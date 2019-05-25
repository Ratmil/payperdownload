<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

// no direct access
defined ( '_JEXEC' ) or die;

require_once(JPATH_COMPONENT.'/html/base.html.php');
require_once(JPATH_COMPONENT.'/data/gentable.php');
require_once(JPATH_COMPONENT.'/html/vdatabindmodel.html.php');

/************************************************************
Base class to handle common task like add, edit, save, delete, etc
*************************************************************/
class BaseForm extends JObject
{
	var $htmlObject = null;
	var $context = "";
	var $dataBindModel = null;
	var $debugModeOn = false;
	var $updateNulls = false;
	var $formTitle = "Admin form";
	var $toolbarTitle = "Admin form";
	var $editItemTitle = "Edit";
	var $newItemTitle = "New";
	var $editItemScripts = null;
	var $newItemScripts = null;
	var $listItemsScripts = null;
	var $useTransaction = false;
	var $saveErrorMessage;
	var $deleteErrorMessage;
	var $loadErrorMessage;
	var $successfulSaveMessage;
	var $registeredTasks = null;

	/**
	Class constructor
	*/
	function __construct()
	{
		parent::__construct();
		$this->saveErrorMessage = JText::_("PAYPERDOWNLOADPLUS_ERROR_SAVING_DATA_78");
		$this->deleteErrorMessage = JText::_("PAYPERDOWNLOADPLUS_ERROR_DELETING_79");
		$this->loadErrorMessage = JText::_("PAYPERDOWNLOADPLUS_ERROR_LOADING_DATA_80");
		$this->successfulSaveMessage = JText::_("PAYPERDOWNLOADPLUS_DATA_SUCCESSFULLY_SAVED_81");
		$this->toolbarIcon = 'generic.png';
	}

	/**
	Create the elements that define how data is to be shown and handled. Derived classes must implement this function.
	*/
	function createDataBinds()
	{
		$this->dataBindModel = null;
	}

	/**
	Returns the object that will handle data. In this case the object returned
	is a generic table derived from JTable.
	*/
	function getTableObject()
	{
		if($this->dataBindModel)
		{
			$db = JFactory::getDBO();
			$row = new GenericTable($db, $this->dataBindModel);
			return $row;
		}
		else
			return null;
	}

	/***
	Executes a task.
	 ***/
	function doTask($task, $option)
	{
		if( $task == "ajaxCall")
		{
			$this->ajaxCall($task, $option);
		}
		else
		if( $this->htmlObject != NULL )
		{
			$this->createToolbar($task, $option);
			switch($task)
			{
			case "add":
				$this->add($task, $option);
				break;
			case "edit":
				$this->edit($task, $option);
				break;
			case "save":
				$this->save($task, $option);
				break;
			case "apply":
				$this->apply($task, $option);
				break;
			case "remove":
			case "trash":
				$this->trash($task, $option);
				break;
			case "display":
			case "";
				$this->display($task, $option);
				break;
			case "cancel":
				$this->cancel($task, $option);
				break;
			case "orderup":
				$this->orderup($task, $option);
				break;
			case "orderdown":
				$this->orderdown($task, $option);
				break;
			case "saveorder":
				$this->saveorder($task, $option);
				break;
			case 'publish':
				$this->publish($task, $option);
				break;
			case 'unpublish':
				$this->unpublish($task, $option);
				break;
			default:
				if($this->registeredTasks && array_search($task, $this->registeredTasks) !== false)
					$this->$task($task, $option);
				else
					$this->display($task, $option);
				break;
			}
		}
	}

	function publish($task, $option)
	{
    	$cid = JFactory::getApplication()->input->get('cid', array(0), 'array');
		$row = $this->getTableObject();
		$row->publish($cid);
		$this->redirectToList();
	}

	function unpublish($task, $option)
	{
		$cid = JFactory::getApplication()->input->get('cid', array(0), 'array');
		$row = $this->getTableObject();
		$row->unpublish($cid);
		$this->redirectToList();
	}

	/**
	Handles the task 'cancel'
	*/
	function cancel($task, $option)
	{
		$this->display($task, $option);
	}

	/**
	Handles the task 'remove'
	*/
	function trash($task, $option)
	{
		$cid = JFactory::getApplication()->input->get('cid', array(0), 'array');
		$row = $this->getTableObject();
		$result = true;
		if($this->useTransaction)
		{
			$db = JFactory::getDBO();
			$db->setQuery("START TRANSACTION");
			$db->query();
		}
		if(!$this->onBeforeDelete($row, $cid))
			$result = false;
		$msg = htmlspecialchars($this->deleteErrorMessage);
		if($result)
		{
			$count = 0;
			$deleted = array();
			foreach($cid as $id)
			{
				if($row->delete($id))
				{
					$count++;
					$deleted[] = $id;
				}
			}
			$result = $this->onAfterDelete($row, $deleted);
			if($result)
			{
				$msg = htmlspecialchars(JText::sprintf("PAYPERDOWNLOADPLUS_D_ELEMENTS_DELETED_82", $count));
				if($count < count($cid))
				{
					$msg .= " " . htmlspecialchars($this->deleteErrorMessage);
					$this->setError($row->getError());
					if($this->debugModeOn)
					{
						$msg .= "<br/>" . $row->getError();
					}
				}
			}
			else
				$msg = htmlspecialchars($this->deleteErrorMessage);
		}

		if($this->useTransaction)
		{
			$db = JFactory::getDBO();
			if($result)
			{
				$db->setQuery("COMMIT");
				$result = $db->query();
			}
			else
			{
				$db->setQuery("ROLLBACK");
				$db->query();
			}

		}
		$this->redirectToList($msg, ($count == count($cid) && $result) ? "message" : "error");
	}

	/**
	Handles the task 'add'
	*/
	function add($task, $option)
	{
		if($this->dataBindModel != null)
		{
			if($this->newItemScripts != null)
			{
				$scriptPath = "administrator/components/$option/js/";
				foreach($this->newItemScripts as $script)
				{
					JHTML::script($scriptPath . $script);
				}
			}
			$this->htmlObject->add($option, $task, $this->dataBindModel, $this->newItemTitle);
		}
	}

	/**
	Handles the task 'edit'
	*/
	function edit($task, $option)
	{
		if($this->dataBindModel != null)
		{
			$cid = JFactory::getApplication()->input->get('cid', array(0), 'array');
			$id = $cid[0];
			$row = $this->getTableObject();
			if($row->load($id))
			{
				if($this->editItemScripts != null)
				{
					$scriptPath = "administrator/components/$option/js/";
					foreach($this->editItemScripts as $script)
					{
						JHTML::script($scriptPath . $script, false);
					}
				}
				$this->htmlObject->edit($option, $task, $row, $this->dataBindModel, $this->editItemTitle);
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
	}

	/**
	Handles the task 'save'
	*/
	function save($task, $option)
	{
		$result = $this->store();
		if(is_numeric($result))
		{
			$this->redirectToList(htmlspecialchars($this->successfulSaveMessage));
		}
		else
		{
			$msg = htmlspecialchars($this->saveErrorMessage);
			if($this->debugModeOn)
			{
				$msg .= "<br/>" . $this->getError();
			}
			$this->editFromPost($task, $option, $msg);
		}
	}

	/**
	Handles the task 'apply'
	*/
	function apply($task, $option)
	{
		$result = $this->store();
		if(is_numeric($result))
		{
		    $jinput = JFactory::getApplication()->input;

		    $link = 'index.php?option='.urlencode($jinput->get('option')).
                '&adminpage='.urlencode($jinput->getString('adminpage')).
                '&view='.urlencode($jinput->get('view')).
                '&task=edit&cid[]='.urlencode($result);
			$this->redirect($link, htmlspecialchars($this->successfulSaveMessage));
		}
		else
		{
			$msg = htmlspecialchars($this->saveErrorMessage);
			$msg .= "<br/>" . $this->getError();
			$this->editFromPost($task, $option, $msg);
		}
	}

	/**
	Show the edit form after an apply task
	*/
	function editFromPost($task, $option, $msg)
	{
	    $jinput = JFactory::getApplication()->input;

		$row = $this->getTableObject();


		if ($row->bind($jinput->post->getArray()))
		//if($row->bind(JRequest::get('post')))
		{
		    $jinput->set("task", "edit");
			$mainframe = JFactory::getApplication();
			$mainframe->enqueueMessage($msg, "error");
			if($this->editItemScripts != null)
			{
				$scriptPath = "administrator/components/$option/js/";
				foreach($this->editItemScripts as $script)
				{
					JHTML::script($scriptPath . $script, false);
				}
			}
			$this->htmlObject->edit($option, $task, $row, $this->dataBindModel, $this->editItemTitle);
		}
	}

	/**
	Stores data into database
	*/
	function store()
	{
		$row = $this->getTableObject();

		if (!$row->bind(JFactory::getApplication()->input->post->getArray()))
		//if (!$row->bind(JRequest::get('post')))
		{
			$this->setError($row->getError());
			return false;
		}
		$keyField = $this->dataBindModel->keyField;
		$isUpdate = $row->$keyField != null;

		$result = true;

		if($this->useTransaction)
		{
			$db = JFactory::getDBO();
			$db->setQuery("START TRANSACTION");
			$db->query();
		}
		if(!$this->onBeforeStore($row, $isUpdate))
			$result = false;
		if ($result && !$row->store($this->updateNulls))
		{
			$this->setError($row->getError());
			$result = false;
		}
		if($result && !$this->onAfterStore($row, $isUpdate))
			$result = false;
		if($this->useTransaction)
		{
			$db = JFactory::getDBO();
			if($result)
			{
				$db->setQuery("COMMIT");
				$result = $db->query();
			}
			else
			{
				$db->setQuery("ROLLBACK");
				$db->query();
			}
		}
		if($result)
			return $row->$keyField;
		else
			return false;
	}

	/**
	Executed before data is stored. Derived classes can redifine this function to take action accordingly
	*/
	function onBeforeStore(&$row, $isUpdate)
	{
		return true;
	}

	/**
	Executed after data is stored. Derived classes can redifine this function to take action accordingly
	*/
	function onAfterStore(&$row, $isUpdate)
	{
		return true;
	}

	/**
	Executed before data is deleted. Derived classes can redifine this function to take action accordingly
	*/
	function onBeforeDelete(&$row, $cid)
	{
		return true;
	}

	/**
	Executed after data is deleted. Derived classes can redifine this function to take action accordingly
	*/
	function onAfterDelete(&$row, $cid)
	{
		return true;
	}

	/**
	Handles the task 'display'. Show lists of elements
	*/
	function display($task, $option)
	{
		$table = $this->getTableObject();
		if($table == null)
			return;
		$filters = $this->getFilters();
		$total = $table->getCount($filters);
		$pageNav = $this->getPaginationObject($total);
		$rows = $table->getList($pageNav->limitstart, $pageNav->limit, $filters);
		if($this->listItemsScripts != null)
		{
			$scriptPath = "administrator/components/$option/js/";
			foreach($this->listItemsScripts as $script)
			{
				JHTML::script($scriptPath . $script, false);
			}
		}
		$this->htmlObject->listItems($option, $rows, $pageNav,
			$this->formTitle, $this->dataBindModel, $filters);
	}

	/*** Returns object that handles html  ***/
	function getHtmlObject()
	{
		return new baseHtmlForm();
	}

	/**
	Renders submenu.
	*/
	function renderSubmenu()
	{
	}

	//Render the admin form and handles the supplied task.
	function showForm($task, $option)
	{
		if($task == "ajaxcall")
		{
			$this->doTask($task, $option);
		}
		else
		{
			$this->renderSubmenu();
			$this->createDataBinds();
			$this->htmlObject = $this->getHtmlObject();
			$this->htmlObject->startForm($task, $option, $this->dataBindModel);
			$this->doTask($task, $option);
			$this->htmlObject->endForm($task, $option);
		}
	}

	/*** Creates toolbar***/
	function createToolbar($task, $option)
	{
		JToolBarHelper::title( $this->toolbarTitle, $this->toolbarIcon );
		switch($task)
		{
			case 'edit':
			case 'add':
			case 'apply':
				JToolBarHelper::save();
				JToolBarHelper::apply();
				JToolBarHelper::cancel();
			break;
			default:
				JToolBarHelper::addNew();
				JToolBarHelper::editList();
				JToolBarHelper::deleteList();
			break;
		}
	}
	/*
	Returns object used for pagination
	*/
	function getPaginationObject($total)
	{
		$mainframe = JFactory::getApplication();
		$limit = $mainframe->getUserStateFromRequest( $this->context.'.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( $this->context.'.list.limitstart', 'limitstart', 0, 'int' );

		jimport( 'joomla.html.pagination' );
		$pageNav = new JPagination( $total, $limitstart, $limit );
		return $pageNav;
	}

	/***
	Adds a condition with an and join to a sql condition
	*/
	function addSqlCondition(&$conditions, $condition)
	{
		if($conditions)
			$conditions = "($conditions) AND ($condition)";
		else
			$conditions = $condition;
	}

	/**
	Returns an array containing data to create a where clause for the elements that will be shown
	*/
	function getFilters()
	{
		$mainframe = JFactory::getApplication();
		$filters = array();
		$filters['search'] = $mainframe->getUserStateFromRequest( $this->context.'.list.search', 'filter_search', '', 'string' );
		$filters['order'] = $mainframe->getUserStateFromRequest( $this->context.'.list.filter_order', 'filter_order', '', 'cmd' );
		$filters['order_Dir'] = $mainframe->getUserStateFromRequest( $this->context.'.list.filter_order_Dir', 'filter_order_Dir', '', 'word' );
		$databinds = $this->dataBindModel->getDataBinds();
		foreach($databinds as $databind)
		{
			if($databind->useForFilter)
			{
				$filterName = $databind->getFilterName();
				$filters[$filterName] = $mainframe->getUserStateFromRequest( $this->context.'.list.' . $filterName, $filterName, '', 'string' );
			}
		}
		return $filters;
	}

	/**
	Handles the task 'orderup'
	*/
	function orderup($task, $option)
	{
		$cid = JFactory::getApplication()->input->get('cid', array(0), 'array');
		if(count($cid) > 0)
		{
			$row = $this->getTableObject();
			$row->load($cid[0]);
			$row->move( -1);
		}
		$this->redirectToList();
	}

	/**
	Handles the task 'orderdown'
	*/
	function orderdown($task, $option)
	{
		$cid = JFactory::getApplication()->input->get('cid', array(0), 'array');
		if(count($cid) > 0)
		{
			$row = $this->getTableObject();
			$row->load($cid[0]);
			$row->move(1);
		}
		$this->redirectToList();
	}

	/**
	Handles the task 'saveorder'
	*/
	function saveorder($task, $option)
	{
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();

		$cid = $app->input->get('cid', array(0), 'array');

		//$order = JRequest::getVar( 'order', array(0), 'post', 'array' );
		$order = JFactory::getApplication()->input->get('order', array(0), 'array');

		$total = count( $cid );
		JArrayHelper::toInteger($order, array(0));
		$row = $this->getTableObject();
		for( $i=0; $i < $total; $i++ ) {
			$row->load( (int) $cid[$i] );

			if ($row->ordering != $order[$i]) {
				$row->ordering = $order[$i];
				if (!$row->store()) {
					$app->enqueueMessage($db->getErrorMsg(), 'error');
					$app->setHeader('status', 500, true);
				}
			}
		}
		$row->reorder();
		$this->redirectToList();
	}

	/**
	Redirects with to show the list of elements of the current admin page.
	*/
	function redirectToList($msg = "", $type = "message")
	{
	    $jinput = JFactory::getApplication()->input;

		$mainframe = JFactory::getApplication();
		$link = 'index.php?option='.urlencode($jinput->get('option')).
            '&adminpage='.urlencode($jinput->getString('adminpage')).
            '&view='.urlencode($jinput->get('view'));
		$mainframe->redirect($link, $msg, $type);
	}

	/**
	Handles an ajax call
	*/
	function ajaxCall($task, $option)
	{
		$this->onAjaxCall = true;
		$this->createDataBinds();
		$databinds = $this->dataBindModel->getDataBinds();
		foreach($databinds as $databind)
		{
			if($databind->supportsAjaxCall)
			{
				echo $databind->ajaxCall($task, $option);
			}
		}
	}

	/**
	Redirects to the specified url
	*/
	function redirect($url, $msg = "", $type = "message")
	{
		$mainframe = JFactory::getApplication();
		$mainframe->redirect($url, $msg, $type);
	}

	/**
	Registers a task that can be call using the task parameter
	*/
	function registerTask($task)
	{
		if($this->registeredTasks == null)
			$this->registeredTasks = array();
		$this->registeredTasks[] = $task;
	}
}
?>