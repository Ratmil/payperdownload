<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
$root = JURI::root();

if(JRequest::getVar("error") == 1)
{
	echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_PAYPALSIM_ERROR"));
}
else
{ 
?>
	<a href="<?php echo $this->return;?>"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_PAYMENT_IS_MADE"));?></a>
<?php
}
?>