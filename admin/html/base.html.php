<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or
die( 'Direct Access to this location is not allowed.' );

/*** Class to generate HTML code ***/
class BaseHtmlForm
{
	var $extraValidateScript = null;
	var $enctype = "";
	var $showId = false;
	
	function __construct()
	{
	}

	/*** Generate code for admin form beginning***/
	function startForm($task, $option, $dataBindModel)
	{
		JHTML::_('behavior.tooltip');
		$format = JRequest::getVar('format');
		$mode = JRequest::getVar('mode');
		if($format != 'raw' || $mode != 'noform')
		{
		$this->renderScripts($dataBindModel, $option, $task);
		$this->renderVars($option);
	?>
		<form action="index.php" method="post" <?php if($this->enctype) echo "enctype=\"multipart/form-data\"";?> id="adminForm" name="adminForm">
	<?php
		}
	}
	
	function renderVars($option)
	{
		$url = JURI::root();
		?>
		<script language="Javascript">
		var site_root = '<?php echo addslashes($url); ?>';
		var site_option = '<?php echo addslashes($option); ?>';
		var site_adminpage = '<?php echo addslashes(JRequest::getVar("adminpage")); ?>';
		</script>
		<?php
	}
	
	/**
	Renders the table of elements
	*/
	function listItems($option, &$rows, &$pageNav, $head, $dataBindModel, $filters)
	{
		JHtml::_('formbehavior.chosen', 'select');
		$columnCount = 2;
		$dataBinds = $dataBindModel->dataBinds;
		$key = $dataBindModel->getKeyField();
	?>
		<script type="text/javascript">
			Joomla.orderTable = function() {
				table = document.getElementById("sortTable");
				direction = document.getElementById("directionTable");
				order = table.options[table.selectedIndex].value;
				dirn = direction.options[direction.selectedIndex].value;
				Joomla.tableOrdering(order, dirn, '');
			}
		</script>
	<!--<div id="j-sidebar-container" class="span2">
		<?php $this->renderSideBar($dataBindModel);?>
	</div>-->
	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar">
		<?php $this->renderFilterBar($dataBindModel, $filters, $pageNav);?>
		</div>
		<div class="clearfix"> </div>
		<table class="table table-striped" id="adminTable">
		<thead>
		<th width="1%" class="hidden-phone">
			<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
		</th>
		<?php
		foreach($dataBinds as $dataBind)
		{
			if($dataBind->showInGrid)
			{
				$dataBind->renderColumnHeading($filters, $rows);
				$columnCount++;
			}
		}
		if($this->showId)
		{
		?>
		<th  class="nowrap center hidden-phone" width="2%">
		<?php
		echo JHTML::_('grid.sort',  htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_ID")), htmlspecialchars($key), @$filters['order_Dir'], @$filters['order'] );
		?>
		</th>
		<?php
		}
		?>
		</thead>
		<tfoot>
		<td colspan="<?php echo $columnCount;?>" align="center"><?php echo $pageNav->getListFooter(); ?></td>
		<input type="hidden" id="table_row_count" value="<?php echo count($rows);?>" />
		</tfoot>
		<tbody>
		<?php foreach ($rows as $i => $row) :	?>
		<tr class="row<?php echo $i % 2; ?>">
		<td class="center hidden-phone">
			<?php echo JHtml::_('grid.id', $i, $row->$key); ?>
		</td>
		<?php
			$columnNumber = 0;
			$n = count($dataBinds);
			foreach($dataBinds as $dataBind)
			{
				if($dataBind->showInGrid)
				{
					echo "<td class=\"center\">";
					$columnNumber++;
					$dataBind->renderGridCell($row, $i, $columnNumber, $n);
					echo "</td>";
				}
			}
			if($this->showId)
			{
			?>
			<td>
			<?php
			echo htmlspecialchars($row->$key);
			?>
			</td>
			<?php
			}
		?>
		</tr>
		<?php endforeach; ?>
		</tbody>
		</table>
		<input type="hidden" name="filter_order" value="<?php echo htmlspecialchars($filters['order']); ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo htmlspecialchars($filters['order_Dir']); ?>" />
	</div>
	<?php
	}
	
	function renderFilterBar($dataBindModel, $filters, $pageNav)
	{
		$dataBinds = $dataBindModel->dataBinds;
		$sortFields = array();
		foreach($dataBinds as $dataBind)
		{
			if($dataBind->showInGrid)
			{
				$sortField = $dataBind->getOrderField();
				if($sortField)
					$sortFields[$sortField->fieldName] = $sortField->display;
			}
		}
	?>
		<div class="filter-search btn-group pull-left">
			<label for="filter_search" class="element-invisible"><?php echo JText::_('PAYPERDOWNLOADPLUS_SEARCH_56');?></label>
			<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('PAYPERDOWNLOADPLUS_SEARCH_56'); ?>" value="<?php echo htmlspecialchars($filters['search']); ?>" title="<?php echo JText::_('PAYPERDOWNLOADPLUS_SEARCH_56'); ?>" />
		</div>
		<div class="btn-group pull-left">
			<button type="submit" class="btn hasTooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
			<button type="button" class="btn hasTooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
		</div>
		<div class="btn-group pull-right hidden-phone">
			<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
			<?php echo $pageNav->getLimitBox(); ?>
		</div>
		<div class="btn-group pull-right hidden-phone">
			<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');?></label>
			<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
				<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
				<option value="asc" <?php if ($filters['order_Dir'] == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING');?></option>
				<option value="desc" <?php if ($filters['order_Dir'] == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');?></option>
			</select>
		</div>
		<div class="btn-group pull-right">
			<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY');?></label>
			<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
				<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
				<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $filters['order']);?>
			</select>
		</div>
	<?php
	}
	
	function renderSideBar($dataBindModel, $filters)
	{
	}
	
	function renderFieldsetsHeaders($fieldsets)
	{
		if(!(count($fieldsets) == 1 && $fieldsets[0]->name == ""))
		{
			?>
			<ul class="nav nav-tabs">
			<?php
			foreach($fieldsets as $i => $fieldset)
			{
				$active = ($i == 0) ? " class=\"active\" " : "";
				?>
				<li <?php echo $active;?>><a href="#<?php echo htmlspecialchars($fieldset->name);?>" data-toggle="tab">
				<?php
				echo htmlspecialchars($fieldset->caption);
				?>
				</a></li>
				<?php
			}
			?>
			</ul>
			<?php
			return true;
		}
		else
			return false;
	}
	
	/**
	Renders the form to add a new record to a table
	*/
	function add($option, $task, $dataBindModel, $title)
	{
		$fieldsets = $dataBindModel->getFieldSets();
		$key = $dataBindModel->keyField;
	?>
		<fieldset class="adminform">
		<legend><?php echo htmlspecialchars($title);?></legend>
		<script language="JavaScript">
		var html_insert_mode = 'add';
		</script>
		<?php
		$useFieldsets = $this->renderFieldsetsHeaders($fieldsets);
		if($useFieldsets)
		{
		?>
		<div class="tab-content">
		<?php
		}
		foreach($fieldsets as $f => $fieldset)
		{
			$dataBinds = $fieldset->dataBinds;
			if($useFieldsets)
			{
			$active = ($f == 0) ? " active" : "";
			?>
			<div class="tab-pane<?php echo $active;?>" id="<?php echo htmlspecialchars($fieldset->name);?>">
			<?php
			}
			?>
			<table class="admintable">
			<?php
			for ($i=0, $n=count( $dataBinds ); $i < $n; $i++)
			{
				$databind = $dataBinds[$i];
				if($databind->showInInsertForm)
				{
					echo $databind->renderNew();
					if($databind->onRenderJavascriptRoutine != null)
						echo "<script>".$databind->onRenderJavascriptRoutine."</script>";
				}
			}
			?>
			</table>
			<?php
			if($useFieldsets)
			{
			?>
			</div>
			<?php
			}
		}
		if($useFieldsets)
		{
		?>
		</div>
		<?php
		}
		?>
		</fieldset>
		<input type="hidden" id="<?php echo $key?>" name="<?php echo $key?>" value=""/>
	<?php
	}
	
	/**
	Renders the form to edit a record from a table
	*/
	function edit($option, $task, &$row, $dataBindModel, $title)
	{
		$fieldsets = $dataBindModel->getFieldSets();
		$key = $dataBindModel->keyField;
	?>
		<fieldset class="adminform">
		<legend><?php echo htmlspecialchars($title);?></legend>
		<?php
		$useFieldsets = $this->renderFieldsetsHeaders($fieldsets);
		if($useFieldsets)
		{
		?>
		<div class="tab-content">
		<?php
		}
		foreach($fieldsets as $f => $fieldset)
		{
			$dataBinds = $fieldset->dataBinds;
			if($useFieldsets)
			{
			$active = ($f == 0) ? " active" : "";
			?>
			<div class="tab-pane<?php echo $active;?>" id="<?php echo htmlspecialchars($fieldset->name);?>">
			<?php
			}
			?>
			<table class="admintable">
			<script language="JavaScript">
			var html_insert_mode = 'edit';
			</script>
			<?php
			for ($i=0, $n=count( $dataBinds ); $i < $n; $i++)
			{
				$databind = $dataBinds[$i];
				if($databind->showInEditForm)
				{
					echo $databind->renderEdit($row);
					if($databind->onRenderJavascriptRoutine != null)
						echo "<script>".$databind->onRenderJavascriptRoutine."</script>";
				}
			}
			?>
			</table>
			<?php
			if($useFieldsets)
			{
			?>
			</div>
			<?php
			}
		}
		if($useFieldsets)
		{
		?>
		</div>
		<?php
		}
		?>
		</fieldset>
		<input type="hidden" id="<?php echo $key?>" name="<?php echo $key?>" value="<?php echo htmlspecialchars($row->$key);?>"/>
	<?php
	}
	
	/*** Generates admin form end ***/
	function endForm($task, $option)
	{
		$format = JRequest::getVar('format');
		if($format != 'raw')
		{
	?>
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="<?php echo $task;?>" />
		<input type="hidden" name="adminpage" value="<?php echo JRequest::getVar( 'adminpage', '' );?>" />
		<?php 
		$itemId = JRequest::getVar("Itemid", "");
		if($itemId != "")
		{
		?>
		<input type="hidden" name="Itemid" value="<?php echo htmlspecialchars($itemId);?>" />
		<?php
		}
		?>
		<?php 
		$view = JRequest::getVar("view", "");
		if($view != "")
		{
		?>
		<input type="hidden" name="view" value="<?php echo htmlspecialchars($view);?>" />
		<?php
		}
		?>
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHTML::_( 'form.token' ); ?>
		</form>
	<?php
		}
	}
	
	/**
	Renders javascript validation code
	*/
	function renderScripts($dataBindModel, $option, $task)
	{
		if($dataBindModel)
		{
			$dataBinds = $dataBindModel->dataBinds;
			$key = $dataBindModel->keyField;
			if($dataBinds != null)
			{
				if($task == "edit" || $task == "add" || $task == "apply")
				{
					?>
					<script language="JavaScript">
					function validateFormControls()
					{
					<?php
					
						for ($i=0, $n=count( $dataBinds ); $i < $n; $i++)
						{
							$databind = $dataBinds[$i];
							if(!$databind->disabled && (
								(($task == "edit" || $task == "apply") && $databind->showInEditForm) ||	
								($task == "add" && $databind->showInInsertForm) ))
								echo $databind->renderValidateJavascript();
						}
						if($this->extraValidateScript)
							echo $this->extraValidateScript;
					
					?>
					return true;
					}
					</script>
				<?php
				}
			}
			$submitFunc = "Joomla.submitbutton = function(pressbutton)";
			?>
			<script language="JavaScript">
			<?php echo $submitFunc;?>
			{
				var t1 = '<?php echo JText::_("PAYPERDOWNLOADPLUS_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THE_SELECTED_ITEMS_9", true); ?>';
				switch(pressbutton)
				{
				case 'remove':
					if(!confirm(t1))
						return;
					break;
				case 'save':
				case 'apply':
					if(!validateFormControls())
						return;
					if(typeof(validateform) != "undefined")
					{
						if(!validateform())
						{
							return;
						}
					}
					break;
				default:
					if(typeof(validatetask) != "undefined")
					{
						if(!validatetask(pressbutton))
						{
							return;
						}
					}
					break;
				}
				submitform(pressbutton);
			}
			
			</script>
			<?php
		}
	}
}
?>