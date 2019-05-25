<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;

jimport( 'joomla.application.component.model' );

class PayPerDownloadModelNoAccess extends JModelLegacy
{
	function getNoAccessPage()
	{
	    require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");
	    PayPerDownloadPlusDebug::debug("Get no access page");

	    $db = JFactory::getDBO();

	    $query = $db->getQuery(true);

	    $query->select($db->quoteName(array('config_id', 'noaccesspage')));
	    $query->from($db->quoteName('#__payperdownloadplus_config'));

	    $db->setQuery($query, 0, 1);

	    $noaccesspage = '';
	    try {
	        $config = $db->loadObject();
	        if (isset($config) && $config != null)
	            $noaccesspage = $config->noaccesspage;
	    } catch (RuntimeException $e) {
	        PayPerDownloadPlusDebug::debug("Failed database query - getNoAccessPage");
	    }

		if(!$noaccesspage)
		{
			$noaccesspage = "<span size=\"16\">" . JText::_("PAYPERDOWNLOADPLUS_NO_ACCESS_TEXT") . "</span>";
		}

		return $noaccesspage;
	}

}
?>