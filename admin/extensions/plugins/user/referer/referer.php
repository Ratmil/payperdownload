<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgUserReferer extends JPlugin
{
	public function onUserBeforeSave($user, $isnew, $new)
	{
	}

	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		if ($isnew && $success) {
			$session = JFactory::getSession();
			$ppd_affid = (int)$session->get("ppd_affid", 0);
			if($ppd_affid)
			{
				$user_id = (int)$user['id'];
				$db = JFactory::getDBO();
				$query = "SELECT user_id FROM #__payperdownloadplus_affiliates_users WHERE affiliate_user_id = " . $ppd_affid;
				$db->setQuery($query);
				$referer = (int)$db->loadResult();
				$query = "INSERT INTO #__payperdownloadplus_affiliates_users_refered(referer_user, refered_user) VALUES($referer, $user_id)";
				$db->setQuery($query);
				$db->query();
			}
		}
	}
}