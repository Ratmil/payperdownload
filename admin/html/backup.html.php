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
class BackupHtmlForm extends BaseHtmlForm
{
	function __construct()
	{
		parent::__construct();
		$this->enctype = 'multipart/form-data';
	}

	function renderBackup()
	{
	?>
		<div style="padding: 12px;">
		<input type="submit" name="backup" 
			value="<?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_BACKUP"));?>" 
			onclick="javascript: submitbutton('backup');"/>
		</div>
		<div style="padding: 12px;">
		<input type="file" name="importxml"/>&nbsp;&nbsp;
		<input type="submit" name="restore" 
			value="<?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_RESTORE"));?>"
			onclick="javascript: submitbutton('restore');" />
		</div>
	<?php
	}
	
}
?>