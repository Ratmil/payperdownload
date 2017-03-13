<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );
class PayPerDownloadModelNoAccess extends JModelLegacy
{ 
	function getNoAccessPage()
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT config_id, noaccesspage FROM #__payperdownloadplus_config", 0, 1);
		$thank_you_content = null;
		$config = $db->loadObject();
		if(isset($config) && $config != null)
			$noaccesspage = $config->noaccesspage;
		if(!$noaccesspage)
		{
			$noaccesspage = "<span size=\"16\">" . JText::_("PAYPERDOWNLOADPLUS_NO_ACCESS_TEXT") . "</span>";
		}
		return $noaccesspage;
	}
	
	
}
?>