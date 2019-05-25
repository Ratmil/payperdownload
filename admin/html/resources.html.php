<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

// no direct access
defined ( '_JEXEC' ) or die;

require_once(JPATH_COMPONENT.'/html/pricecur.html.php');

/*** Class to generate HTML code ***/
class ResourcesHtmlForm extends BaseHtmlForm
{
	function __construct()
	{
		parent::__construct();
		$this->extraValidateScript = "if(!validate_price()) return false;";
		$this->showId = true;
	}

	function renderPlugins($task, $option)
	{
		$root = JURI::root();
		$plugins = array();
		JPluginHelper::importPlugin("payperdownloadplus");
		$dispatcher	= JDispatcher::getInstance();
		$result = $dispatcher->trigger('onIsActive', array (&$plugins));
		$defaultImage = 'administrator/components/' . $option . '/images/doc.png';
		?>
		<script type="text/javascript">
		function validatetask(pressbutton)
		{
			if(pressbutton == 'acceptnewresourcetype')
			{
				var pluginscount = parseInt(document.getElementById('pluginscount').value);
				var something_checked = false;
				for(var i = 0; i < pluginscount; i++)
				{
					var radio = document.getElementById('resourcetype_' + i);
					if(radio.checked)
						something_checked = true;
				}
				if(!something_checked)
				{
					var select_resource = '<?php echo JText::_("PAYPERDOWNLOADPLUS_SELECT_RESOURCE_TYPE");?>';
					alert(select_resource);
					return false;
				}
			}
			return true;
		}
		</script>
		<div id="j-sidebar-container" class="span2">
		<?php echo JHtmlSidebar::render(); ?>
	</div>
	<div id="j-main-container" class="span10">
		<fieldset class="adminform">
		<legend><?php echo JText::_("PAYPERDOWNLOADPLUS_SELECT_THE_TYPE_OF_RESOURCE_27"); ?></legend>
		<?php
		$i = 0;
		foreach($plugins as $plugin)
		{
		if($plugin['image'])
			$image = $plugin['image'];
		else
			$image = $defaultImage;
		?>
		<div class="plugin_div">
		<table>
		<tr>
		<td>
		<img src="<?php echo $root . $image;?>"/>
		</td>
		</tr>
		<tr>
		<td valign="top">
		<input type="radio" name="resourcetype" id="resourcetype_<?php echo $i; ?>" value="<?php echo htmlspecialchars($plugin['name']); ?>" />
		<?php echo htmlspecialchars($plugin['description']); ?>
		</td>
		</tr>
		</table>
		</div>
		<?php
		$i++;
		} ?>
		<input type="hidden" id="pluginscount" name="pluginscount" value="<?php echo $i;?>"/>
		</fieldset>
		<br/>
		<?php echo JText::sprintf('PAYPERDOWNLOADPLUS_DOWNLOAD_PLUGINS', '<a href="http://www.ratmilwebsolutions.com">www.ratmilwebsolutions.com</a>'); ?>
		</div>
		<?php
	}

	function renderPluginConfig($task, $option, $resourceType)
	{
	    $option = JFactory::getApplication()->input->get("option");
		$scriptPath = "administrator/components/$option/js/";
		JHTML::script($scriptPath . "resources3.js", false);
		JPluginHelper::importPlugin("payperdownloadplus");
		$dispatcher	= JDispatcher::getInstance();
	?>
		<script type="text/javascript">
		var invalid_price_text = '<?php echo JText::_("PAYPERDOWNLOADPLUS_INVALID_PRICE");?>';
		var invalid_expiration_text = '<?php echo JText::_("PAYPERDOWNLOADPLUS_INVALID_RESOURCE_EXPIRATION");?>';
		var invalid_maxdownload_text = '<?php echo JText::_("PAYPERDOWNLOADPLUS_INVALID_MAX_DOWNLOAD_COUNT");?>';
		</script>
		<div id="j-sidebar-container" class="span2">
		<?php echo JHtmlSidebar::render(); ?>
	</div>
	<div id="j-main-container" class="span10">
		<fieldset class="adminform">
		<legend><?php echo JText::_("PAYPERDOWNLOADPLUS_NEW_RESOURCE_28"); ?></legend>
		<table class="admintable">
		<input type="hidden" name="resourceType" value="<?php echo htmlspecialchars($resourceType);?>" />
	<?php
		$dispatcher->trigger('onRenderConfig', array (&$resourceType, null));
		$bind = new ComboVisualDataBind('license_id', JText::_('PAYPERDOWNLOADPLUS_LICENSE_29'),
			'#__payperdownloadplus_licenses', 'license_id', 'license_name');
		$bind->allowBlank = true;
		$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_LICENSE_TO_APPLY_TO_RESOURCE_30"));
		$bind->htmlProperties = array("onchange" => "license_select();");
		$bind->setFirstItem(JText::_("PAYPERDOWNLOAD_NO_LICENSE"));
		echo $bind->renderNew();
		$bind = new PriceCurrencyVisualDataBind('resource_price', JText::_('PAYPERDOWNLOADPLUS_PRICE_92'), 'resource_price_currency');
		$bind->setRegExp("\\s*(\\d+(\\.\\d+)?)?\\s*");
		$bind->allowBlank = true;
		$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_RESOURCE_PRICE"));
		echo $bind->renderNew();
		$bind = new VisualDataBind('download_expiration', JText::_('PAYPERDOWNLOADPLUS_DOWNLOAD_LINK_EXPIRATION'));
		$bind->setRegExp("\\s*\\d+\\s*");
		$bind->allowBlank = true;
		$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_DOWNLOAD_LINK_EXPIRATION_DESC"));
		echo $bind->renderNew();
		$bind = new VisualDataBind('max_download', JText::_('PAYPERDOWNLOADPLUS_MAX_DOWNLOAD_COUNT'));
		$bind->setColumnWidth(20);
		$bind->setRegExp("\\s*\\d+\\s*");
		$bind->showInGrid = false;
		$bind->allowBlank = true;
		$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_MAX_DOWNLOAD_COUNT_DESC"));
		echo $bind->renderNew();
		$bind = new RadioVisualDataBind('shared', JText::_('PAYPERDOWNLOADPLUS_RESOURCE_SHARE'));
		$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_RESOURCE_SHARE_DESC"));
		$bind->defaultValue = 1;
		$bind->showInGrid = false;
		echo $bind->renderNew();
		$bind = new WYSIWYGEditotVisualDataBind('payment_header', JText::_('PAYPERDOWNLOADPLUS_RESOURCE_PAYMENT_HEADER'));
		$bind->showInGrid = false;
		$bind->allowBlank = true;
		$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_RESOURCE_PAYMENT_HEADER_DESC"));
		echo $bind->renderNew();
		?>
		</table>
		</fieldset>
		</div>
	<?php
	}

	function renderPluginConfigEdit($task, $option, $dataBindModel, $resource)
	{
	    $option = JFactory::getApplication()->input->get("option");
		$scriptPath = "administrator/components/$option/js/";
		JHTML::script($scriptPath . "resources3.js", false);
		JPluginHelper::importPlugin("payperdownloadplus");
		$dispatcher	= JDispatcher::getInstance();
		$resourceType = $resource->resource_type;
	?>
		<script type="text/javascript">
		var invalid_price_text = '<?php echo JText::_("PAYPERDOWNLOADPLUS_INVALID_PRICE");?>';
		var invalid_expiration_text = '<?php echo JText::_("PAYPERDOWNLOADPLUS_INVALID_RESOURCE_EXPIRATION");?>';
		var invalid_maxdownload_text = '<?php echo JText::_("PAYPERDOWNLOADPLUS_INVALID_MAX_DOWNLOAD_COUNT");?>';
		</script>
		<div id="j-sidebar-container" class="span2">
		<?php echo JHtmlSidebar::render(); ?>
	</div>
	<div id="j-main-container" class="span10">
		<fieldset class="adminform">
		<legend><?php echo JText::_("PAYPERDOWNLOADPLUS_EDIT_RESOURCE_LICENSE_118"); ?></legend>
		<table class="admintable">
		<script language="JavaScript">
		var html_insert_mode = 'edit';
		</script>
		<input type="hidden" name="resourceType" value="<?php echo htmlspecialchars($resourceType);?>" />
		<input type="hidden" name="resource_license_id" value="<?php echo htmlspecialchars($resource->resource_license_id);?>" />

	<?php
		$dataBinds = $dataBindModel->dataBinds;
		for ($i=0, $n=count( $dataBinds ); $i < $n; $i++)
		{
			$databind = $dataBinds[$i];
			if($databind->showInEditForm)
			{
				echo $databind->renderEdit($resource);
				if($databind->onRenderJavascriptRoutine != null)
					echo "<script>".$databind->onRenderJavascriptRoutine."</script>";
			}
		}

		$dispatcher->trigger('onRenderConfig', array (&$resourceType, $resource));
		$bind = new ComboVisualDataBind('license_id', JText::_('PAYPERDOWNLOADPLUS_LICENSE_29'),
			'#__payperdownloadplus_licenses', 'license_id', 'license_name');
		$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_LICENSE_TO_APPLY_TO_RESOURCE_30"));
		$bind->allowBlank = true;
		$bind->htmlProperties = array("onchange" => "license_select();");
		$bind->setFirstItem(JText::_("PAYPERDOWNLOAD_NO_LICENSE"));
		echo $bind->renderEdit($resource);
		$bind = new PriceCurrencyVisualDataBind('resource_price', JText::_('PAYPERDOWNLOADPLUS_PRICE_92'), 'resource_price_currency');
		$bind->allowBlank = true;
		$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_RESOURCE_PRICE"));
		echo $bind->renderEdit($resource);
		$bind = new VisualDataBind('download_expiration', JText::_('PAYPERDOWNLOADPLUS_DOWNLOAD_LINK_EXPIRATION'));
		$bind->setRegExp("\\s*\\d+\\s*");
		$bind->allowBlank = true;
		$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_DOWNLOAD_LINK_EXPIRATION_DESC"));
		echo $bind->renderEdit($resource);
		$bind = new VisualDataBind('max_download', JText::_('PAYPERDOWNLOADPLUS_MAX_DOWNLOAD_COUNT'));
		$bind->setColumnWidth(20);
		$bind->setRegExp("\\s*\\d+\\s*");
		$bind->showInGrid = false;
		$bind->allowBlank = true;
		$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_MAX_DOWNLOAD_COUNT_DESC"));
		echo $bind->renderEdit($resource);
		$bind = new RadioVisualDataBind('shared', JText::_('PAYPERDOWNLOADPLUS_RESOURCE_SHARE'));
		$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_RESOURCE_SHARE_DESC"));
		$bind->defaultValue = 1;
		$bind->showInGrid = false;
		echo $bind->renderEdit($resource);
		$bind = new WYSIWYGEditotVisualDataBind('payment_header', JText::_('PAYPERDOWNLOADPLUS_RESOURCE_PAYMENT_HEADER'));
		$bind->showInGrid = false;
		$bind->allowBlank = true;
		$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_RESOURCE_PAYMENT_HEADER_DESC"));
		echo $bind->renderEdit($resource);
	?>
		</table>
		</fieldset>
		</div>
		<script type="text/javascript">
		license_select();
		</script>
	<?php
	}
}
?>