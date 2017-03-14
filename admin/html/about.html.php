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
class AboutHtmlForm extends BaseHtmlForm
{
	function renderDoc()
	{
		$root = JURI::root();
	?>
		<div style="float:right;">
		<img src="<?php echo $root;?>administrator/components/com_payperdownload/images/ppdplus.png" />
		</div>
		<p><font size="5"><strong>Payperdownload 5.7</strong></font> </p>
		<p><font size="5"><strong>Copyright</strong></font></p>
		<p><font size="4">&nbsp;&nbsp;&nbsp;©&nbsp;2010 - <?php echo date("Y"); ?> Ratmil&nbsp;&nbsp;&nbsp;<a href="http://www.ratmilwebsolutions.com">www.ratmilwebsolutions.com</a></font></p>
		<p><font size="4"><strong>Bug Fixes</strong></font></p>
		<p><font size="4">&nbsp;&nbsp;&nbsp;Olivier Buisard&nbsp;&nbsp;&nbsp;<a href="https://simplifyyourweb.com/">www.simplifyyourweb.com</a></font></p>
		<p><font size="4"><a href="http://extensions.joomla.org/extensions/e-commerce/paid-downloads/18146"><?php echo JText::_("PAYPERDOWNLOADPLUS_WRITE_REVIEW");?></a></font></p>
		<p><font size="5"><strong>License.</strong></font></p>
		<font size="4"><a href="http://www.gnu.org/licenses/lgpl-3.0.html">Gnu Public License</a></font>
	<?php
	}
}
?>