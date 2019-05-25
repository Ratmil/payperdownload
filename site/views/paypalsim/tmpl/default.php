<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;

$root = JURI::root();

if(JFactory::getApplication()->input->getInt("error") == 1)
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