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

require_once(JPATH_COMPONENT.'/html/pricecur.html.php');

/*** Class to generate HTML code ***/
class DownloadsHtmlForm extends BaseHtmlForm
{
	function __construct()
	{
		parent::__construct();
	}

	function addNewDownloadLink()
	{
		$bindemail = new VisualDataBind('user_email', JText::_('PAYPERDOWNLOADPLUS_USER_EMAIL'));
		$bindemail->setRegExp("\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*");
		$bindemail->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_USER_EMAIL_DESC"));
		$bindresource = new ComboVisualDataBind('resource_id', JText::_('PAYPERDOWNLOADPLUS_RESOURCE'), 
				'', 'resource_license_id', 'resource_name');
		$bindresource->setItemsQuery("SELECT resource_license_id AS value, CONCAT(resource_name, ' - ', resource_type) AS display 
			FROM #__payperdownloadplus_resource_licenses
			WHERE license_id IS NULL");
		$bindresource->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_RESOURCE_DESC"));
		?>
		<script language="JavaScript">
		function validatetask(pressbutton)
		{
			if(pressbutton == 'savenewdownload')
			{
			<?php
			echo $bindemail->renderValidateJavascript();
			echo $bindresource->renderValidateJavascript();
			?>
			}
			return true;
		}
		var html_insert_mode = 'add';
		</script>
		<?php
		$this->renderVars(JRequest::getVar('option'));
		?>
		<fieldset class="adminform">
		<legend><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_NEW_DOWNLOADLINK"));?></legend>
		<table class="admintable">
		<?php
		echo $bindemail->renderNew();
		echo $bindresource->renderNew();
		?>
		</table>
		</fieldset>
	<?php
	}
}
?>