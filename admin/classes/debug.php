<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class PayPerDownloadPlusDebug
{

	static function debug($text)
	{
		$db = JFactory::getDBO();
		//Delete old debug texts
		$db->setQuery("DELETE FROM #__payperdownloadplus_debug WHERE TO_DAYS(NOW()) - TO_DAYS(debug_time) > 1");
		$db->query();
		$text = $db->escape($text);
		$query = "INSERT INTO #__payperdownloadplus_debug(debug_text, debug_time) VALUES('$text', NOW())";
		$db->setQuery($query);
		$db->query();
	}
}

?>