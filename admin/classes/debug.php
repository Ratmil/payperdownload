<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
// no direct access
defined('_JEXEC') or die;

class PayPerDownloadPlusDebug
{

	static function debug($text)
	{
	    $config = JComponentHelper::getParams('com_payperdownload');
	    $debug = $config->get('debug', false);

	    if ($debug) {
    		$db = JFactory::getDBO();

    		// Delete old debug texts

    		$query = $db->getQuery(true);

    		$query->delete($db->quoteName('#__payperdownloadplus_debug'));
    		$query->where('TO_DAYS(NOW()) - TO_DAYS(' . $db->quoteName('debug_time') . ') > 1');

    		$db->setQuery($query);

    		try {
    		    $db->execute();
    		} catch (RuntimeException $e) {
    		    // could not delete rows
    		}

    		// Insert debug message

    		$query->clear();

    		$query->insert($db->quoteName('#__payperdownloadplus_debug'));
    		$query->columns($db->quoteName(array('debug_text', 'debug_time')));
    		$query->values(implode(',', array($db->quote($text), 'NOW()')));

    		$db->setQuery($query);

    		try {
    		    $db->execute();
    		} catch (RuntimeException $e) {
    		    // could not insert message
    		}
	    }
	}
}

?>