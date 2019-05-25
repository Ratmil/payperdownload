<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

// no direct access
defined ( '_JEXEC' ) or die;

require_once(JPATH_COMPONENT.'/controllers/ppd.php');
require_once(JPATH_COMPONENT.'/data/gentable.php');
require_once(JPATH_COMPONENT.'/html/vdatabind.html.php');
require_once(JPATH_COMPONENT.'/html/vexcombo.html.php');
require_once(JPATH_COMPONENT.'/html/vcalendar.html.php');
require_once(JPATH_COMPONENT.'/html/vdatabindmodel.html.php');

/************************************************************
Class to manage sectors
*************************************************************/
class UsersForm extends PPDForm
{
	/**
	Class constructor
	*/
	function __construct()
	{
		parent::__construct();
		$this->context = 'com_payperdownload.users';
		$this->formTitle = JText::_('PAYPERDOWNLOADPLUS_USERSS_LICENSES_131');
		$this->toolbarTitle = JText::_('COM_PAYPERDOWNLOAD_USERLICENSES_TITLE');
		$this->editItemTitle = JText::_("PAYPERDOWNLOADPLUS_EDIT_USER_LICENSE_132");
		$this->newItemTitle = JText::_("PAYPERDOWNLOADPLUS_NEW_USER_LICENSE_133");
		$this->toolbarIcon = 'users';
		$this->registerTask('extend');
		$this->registerTask('del');
		$this->registerTask('upgrade');
		//use transaction to restrict exclusive access to payperdownloadplus_users_licenses table
		$this->useTransaction = true;
	}

	/**
	Create the elements that define how data is to be shown and handled.
	*/
	function createDataBinds()
	{
		if($this->dataBindModel == null)
		{
		    $option = JFactory::getApplication()->input->get('option');

			$this->dataBindModel = new VisualDataBindModel();
			$this->dataBindModel->setKeyField("user_license_id");
			$this->dataBindModel->setTableName("#__payperdownloadplus_users_licenses");

			$bind = new ExComboVisualDataBind('user_id', JText::_('PAYPERDOWNLOADPLUS_USER_134'), '#__users', 'id', 'username');
			$bind->setExtraSearchField("username");
			$bind->setColumnWidth(10);
			$bind->setEditLink(true);
			$bind->disabledEdit = true;
			$bind->useForFilter = false;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_JOOMLA_USER_135"));
			$this->dataBindModel->addDataBind( $bind );

			$bind = new ComboVisualDataBind('license_id', JText::_('PAYPERDOWNLOADPLUS_LICENSE_136'), '#__payperdownloadplus_licenses', 'license_id', 'license_name');
			$bind->setColumnWidth(25);
			$bind->disabledEdit = true;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_LICENSE_BUYED_BY_THE_USER_137"));
			$this->dataBindModel->addDataBind( $bind );

			$bind = new CalendarVisualDataBind('expiration_date', JText::_('PAYPERDOWNLOADPLUS_EXPIRATION_DATE_138'));
			$bind->allowBlank = false;
			$bind->setColumnWidth(15);
			$bind->disabled = false;
			$bind->ignoreToBind = true;
			$bind->showInEditForm = true;
			$bind->showInInsertForm = false;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_LICENSE_EXPIRATION_DATE_139"));
			$this->dataBindModel->addDataBind( $bind );

			$bind = new VisualDataBind('item', JText::_("COM_PAYPERDOWNLOAD_DOWNLOADID"));
			$bind->setColumnWidth(20);
			$bind->allowBlank = true;
			$bind->showInInsertForm = true;
			$bind->showInGrid = true;
			$bind->requiredMark = false;
			$bind->setEditToolTip(JText::_("COM_PAYPERDOWNLOAD_DOWNLOADID"));
			$bind->defaultValue = md5(JFactory::getUser()->id.strtotime('now'));
			$this->dataBindModel->addDataBind( $bind );

			$bind = new VisualDataBind('download_hits', JText::_('PAYPERDOWNLOADPLUS_DOWNLOAD_HITS'));
			$bind->setColumnWidth(10);
			$bind->setRegExp("\\s*\\d+\\s*");
			$bind->showInInsertForm = false;
			$bind->disabled = true;
			$bind->showInGrid = true;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_DOWNLOAD_HITS_DESC"));
			$this->dataBindModel->addDataBind( $bind );

			$bind = new VisualDataBind('license_max_downloads', JText::_('PAYPERDOWNLOADPLUS_MAX_DOWNLOAD_HITS'));
			$bind->setColumnWidth(10);
			$bind->setRegExp("\\s*\\d+\\s*");
			$bind->showInInsertForm = false;
			$bind->showInGrid = true;
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_MAX_DOWNLOAD_HITS_DESC"));
			$this->dataBindModel->addDataBind( $bind );

			$bind = new RadioVisualDataBind('enabled', JText::_('PAYPERDOWNLOADPLUS_LICENSE_ENABLED'));
			$bind->setEditToolTip(JText::_("PAYPERDOWNLOADPLUS_LICENSE_ENABLED_DESC"));
			$bind->setColumnWidth(5);
			$bind->defaultValue = 1;
			$bind->yes_task = "unpublish";
			$bind->no_task = "publish";
			$bind->yes_image = "administrator/components/$option/images/published.png";
			$bind->no_image = "administrator/components/$option/images/unpublished.png";
			$this->dataBindModel->addDataBind( $bind );

			$bind = new VisualDataBind('user_license_id', JText::_("PAYPERDOWNLOADPLUS_ID"));
			$bind->setColumnWidth(10);
			$bind->showInInsertForm = false;
			$bind->disabled = true;
			$bind->showInGrid = true;
			$this->dataBindModel->addDataBind( $bind );
		}
	}

	function onAfterStore(&$row, $isUpdate)
	{
		jimport('joomla.utilities.date');
		if(!$isUpdate)
		{
			$license_id = (int)$row->license_id;
			$db = JFactory::getDBO();
			$query = "SELECT expiration, max_download, user_group FROM #__payperdownloadplus_licenses WHERE license_id = " . (int)$license_id;
			$db->setQuery($query);
			$lic = $db->loadObject();
			$expiration = (int)$lic->expiration;
			$max_download = (int)$lic->max_download;
			$user_group = "NULL";
			if($lic->user_group)
			{
				$user_group = (int)$lic->user_group;
				$this->assignUserGroup($user_group, $row->user_id);
			}
			$credit = 0;
			$credit_days_used = $duration = $expiration;
			if($expiration > 0)
			{
				$query = "UPDATE #__payperdownloadplus_users_licenses SET credit = 0, credit_days_used = $expiration,
					license_max_downloads = $max_download,
					duration = $expiration, expiration_date = DATE_ADD(NOW(), INTERVAL $expiration DAY),
					assigned_user_group = $user_group
					WHERE user_license_id = " . (int)$row->user_license_id;
			}
			else
			{
				$query = "UPDATE #__payperdownloadplus_users_licenses SET credit = 0, credit_days_used = 0,
					license_max_downloads = $max_download,
					duration = 0, expiration_date = NULL,
					assigned_user_group = $user_group
					WHERE user_license_id = " . (int)$row->user_license_id;
			}
			$db->setQuery($query);
			$db->query();
		}
		return true;
	}


	function extend($task, $option)
	{
		$db = JFactory::getDBO();
		$cid = JFactory::getApplication()->input->get('cid', array(0), 'array');
		if(count($cid) > 0)
		{
			$cids = implode(",", $cid);
			$query = "SELECT
				#__payperdownloadplus_users_licenses.user_license_id,
				#__payperdownloadplus_licenses.license_id,
				#__payperdownloadplus_licenses.expiration,
				#__payperdownloadplus_licenses.level,
				#__payperdownloadplus_licenses.max_download,
				#__payperdownloadplus_licenses.currency_code,
				#__payperdownloadplus_users_licenses.user_id
				FROM #__payperdownloadplus_users_licenses
				INNER JOIN #__payperdownloadplus_licenses
				ON #__payperdownloadplus_users_licenses.license_id = #__payperdownloadplus_licenses.license_id
				WHERE #__payperdownloadplus_users_licenses.expiration_date IS NOT NULL AND #__payperdownloadplus_users_licenses.user_license_id IN ($cids)";
			$db->setQuery($query);
			$licenses = $db->loadObjectList();
			$count = 0;
			foreach($licenses as $license)
			{
				$expiration = (int)$license->expiration;
				$user_license_id = (int)$license->user_license_id;
				$max_downloads = (int)$license->max_download;
				$query = "UPDATE #__payperdownloadplus_users_licenses
					SET expiration_date = DATE_ADD(expiration_date, INTERVAL $expiration DAY),
					credit = 0,
					duration = duration + $expiration,
					license_max_downloads = license_max_downloads + $max_downloads,
					credit_days_used = duration
					WHERE user_license_id = $user_license_id";
				$db->setQuery($query);
				if($db->query())
					$count++;
				$this->removeUsedCredit(
					$license->license_id, $license->currency_code, $license->level, $license->user_id);
			}
		}
		$this->redirectToList(JText::sprintf("PAYPERDOWNLOADPLUS_LICENSES_EXTENDED", $count));
	}

	function unassignUserGroupsForExpiredLicenses()
	{
		$db = JFactory::getDBO();
		$query = "SELECT user_license_id, assigned_user_group, user_id FROM #__payperdownloadplus_users_licenses
			WHERE expiration_date < NOW() AND expiration_date IS NOT NULL AND assigned_user_group IS NOT NULL";
		$db->setQuery($query);
		$expired = $db->loadObjectList();
		foreach($expired as $expired_user_license)
		{
			$this->unassignUserGroupForLicense($expired_user_license);
		}
	}

	function unassignUserGroupForLicense($user_license)
	{
		if($user_license->assigned_user_group)
		{
			$user = JFactory::getUser($user_license->user_id);
			$gid = array_search($user_license->assigned_user_group, $user->groups);
			if($gid !== false)
			{
				unset($user->groups[$gid]);
				$user->save();
			}
		}
	}

	function unassignUserGroupForLicenseId($user_license_id)
	{
		$db = JFactory::getDBO();
		$query = "SELECT user_license_id, assigned_user_group, user_id FROM #__payperdownloadplus_users_licenses
				WHERE user_license_id = " . (int)$user_license_id;
		$db->setQuery( $query );
		$user_license = $db->loadObject();
		if($user_license)
			$this->unassignUserGroupForLicense($user_license);
	}

	function assignUserGroup($user_group, $user_id)
	{
		$user = JFactory::getUser($user_id);
		if(array_search($user_group,  $user->groups) === false)
		{
			$user->groups []= $user_group;
			$user->save();
		}
	}

	function upgrade($task, $option)
	{
		$db = JFactory::getDBO();
		$cid = JFactory::getApplication()->input->get('cid', array(0), 'array');
		if(count($cid) > 0)
		{
			$cids = implode(",", $cid);
			$query = "SELECT
				#__payperdownloadplus_users_licenses.user_license_id,
				#__payperdownloadplus_licenses.license_id,
				#__payperdownloadplus_licenses.expiration,
				#__payperdownloadplus_users_licenses.expiration_date,
				#__payperdownloadplus_licenses.max_download,
				#__payperdownloadplus_licenses.level,
				#__payperdownloadplus_licenses.currency_code,
				#__payperdownloadplus_users_licenses.user_id
				FROM #__payperdownloadplus_users_licenses
				INNER JOIN #__payperdownloadplus_licenses
				ON #__payperdownloadplus_users_licenses.license_id = #__payperdownloadplus_licenses.license_id
				WHERE #__payperdownloadplus_users_licenses.user_license_id IN ($cids) AND
					(#__payperdownloadplus_users_licenses.expiration_date IS NULL OR
					#__payperdownloadplus_users_licenses.expiration_date >= NOW()) AND
					#__payperdownloadplus_users_licenses.enabled = 1 AND
					#__payperdownloadplus_licenses.enabled = 1";
			$db->setQuery($query);
			$licenses = $db->loadObjectList();
			$count = 0;
			foreach($licenses as $license)
			{
				$expiration = (int)$license->expiration;
				$user_license_id = (int)$license->user_license_id;
				$user_id = (int)$license->user_id;
				$level = (int)$license->level;
				$higher_license = $this->getAHigherLicense($level, $user_id);
				if($higher_license)
				{
					$license_id = (int)$higher_license->license_id;
					$expiration = (int)$higher_license->expiration;
					$max_download = (int)$higher_license->max_download;
					$user_group = "NULL";
					if($higher_license->user_group)
						$user_group = (int)$higher_license->user_group;
					if($expiration > 0)
						$query = "INSERT INTO
							#__payperdownloadplus_users_licenses
							(license_id, user_id, expiration_date, enabled, credit, duration, credit_days_used, license_max_downloads, assigned_user_group, item)
							VALUES($license_id, $user_id, DATE_ADD(NOW(), INTERVAL $expiration DAY),
								1, 0, $expiration, $expiration, $max_download, $user_group, MD5(CONCAT($user_id, NOW())))";
					else
						$query = "INSERT INTO
							#__payperdownloadplus_users_licenses
							(license_id, user_id, expiration_date, enabled, credit, duration, credit_days_used, license_max_downloads, assigned_user_group, item)
							VALUES($license_id, $user_id, NULL, 1, 0, $expiration, $expiration, $max_download, $user_group, MD5(CONCAT($user_id, NOW())))";
					$db->setQuery($query);
					if($db->query())
						$count++;
					$new_user_license_id = $db->insertid();
					if($higher_license->user_group)
						$this->assignUserGroup($higher_license->user_group, $user_id);
					$new_date = $this->getLicenseDate($new_user_license_id);
					if($expiration == 0 || $new_date > $license->expiration_date)
					{
						$this->unassignUserGroupForLicenseId($user_license_id);
						$query = "DELETE FROM #__payperdownloadplus_users_licenses
							WHERE user_license_id = " . $user_license_id;
						$db->setQuery($query);
						$db->query();
					}
					$this->removeUsedCredit(
						$higher_license->license_id, $higher_license->currency_code,
							$higher_license->level, $user_id);
				}
			}
		}

		$this->redirectToList(JText::sprintf("PAYPERDOWNLOADPLUS_LICENSES_UPGRADED", $count));
	}

	function getLicenseDate($user_license_id)
	{
		$db = JFactory::getDBO();
		$query = "SELECT expiration_date FROM #__payperdownloadplus_users_licenses
			WHERE user_license_id = " . (int)$user_license_id;
		$db->setQuery($query);
		$user_license = $db->loadObject();
		if($user_license)
		{
			return $user_license->expiration_date;
		}
		return null;
	}

	function hasHigherLicensesForUser($user_id, $level)
	{
		if($level <= 0) // Level zero license are not upgraded
			return false;
		$user_id = (int)$user_id;
		$query = "SELECT COUNT(*)
				FROM #__payperdownloadplus_users_licenses
				INNER JOIN #__payperdownloadplus_licenses
				ON #__payperdownloadplus_users_licenses.license_id = #__payperdownloadplus_licenses.license_id
				WHERE #__payperdownloadplus_licenses.level > $level  AND
					(#__payperdownloadplus_users_licenses.expiration_date >= NOW()
					OR #__payperdownloadplus_users_licenses.expiration_date IS NULL)
					AND
					#__payperdownloadplus_users_licenses.enabled = 1 AND
					#__payperdownloadplus_licenses.enabled = 1 AND
					#__payperdownloadplus_users_licenses.user_id = $user_id";
		$db = JFactory::getDBO();
		$db->setQuery($query);
		return $db->loadResult() > 0;
	}

	function getAHigherLicense($level, $user_id)
	{
		if($level <= 0) // Level zero license are not upgraded
			return null;
		if($this->hasHigherLicensesForUser($user_id, $level))
		{
			return null;
		}
		$db = JFactory::getDBO();
		$level = (int)$level + 1;
		$query = "SELECT
				#__payperdownloadplus_licenses.license_id,
				#__payperdownloadplus_licenses.expiration,
				#__payperdownloadplus_licenses.level,
				#__payperdownloadplus_licenses.max_download,
				#__payperdownloadplus_licenses.currency_code,
				#__payperdownloadplus_licenses.user_group
				FROM #__payperdownloadplus_licenses
				WHERE level >= $level ORDER BY level, expiration DESC";
		$db->setQuery($query);
		return $db->loadObject();
	}

	function removeUsedCredit($license_id, $currency_code, $level, $user_id)
	{
		$level = (int)$level;
		$db = JFactory::getDBO();
		$cur = $db->escape($currency_code);

		$query = "SELECT
			#__payperdownloadplus_users_licenses.user_license_id
			FROM #__payperdownloadplus_users_licenses
			INNER JOIN #__payperdownloadplus_licenses
			ON #__payperdownloadplus_users_licenses.license_id = #__payperdownloadplus_licenses.license_id
			WHERE #__payperdownloadplus_users_licenses.expiration_date > NOW() AND
				#__payperdownloadplus_users_licenses.enabled <> 0 AND
				#__payperdownloadplus_licenses.enabled <> 0 AND
				#__payperdownloadplus_licenses.level > 0 AND
				#__payperdownloadplus_licenses.level < $level AND
				#__payperdownloadplus_licenses.currency_code = '$cur' AND
				#__payperdownloadplus_users_licenses.user_id = " . (int)$user_id;
		$db->setQuery($query);
		$user_licenses = $db->loadColumn();
		if(count($user_licenses) > 0)
		{
			$licenses = implode(",", $user_licenses);
			$query = "UPDATE #__payperdownloadplus_users_licenses SET credit = 0, credit_days_used = duration
				WHERE #__payperdownloadplus_users_licenses.user_license_id IN ($licenses)";
			$db->setQuery($query);
			$db->query();
		}
	}

	function del($task, $option)
	{
		$this->unassignUserGroupsForExpiredLicenses();
		$db = JFactory::getDBO();
		$query = "DELETE FROM #__payperdownloadplus_users_licenses
			WHERE expiration_date < NOW() and expiration_date IS NOT NULL";
		$db->setQuery( $query );
		$db->query();
		$total = $db->getAffectedRows();
		$this->redirectToList(JText::sprintf("PAYPERDOWNLOADPLUS_EXPIRED_LICENSES_DELETED", $total));
	}

	function onBeforeDelete(&$row, $cid)
	{
		$db = JFactory::getDBO();
		$cids = implode(',', $cid);
		$query = "SELECT user_license_id, assigned_user_group, user_id FROM #__payperdownloadplus_users_licenses
			WHERE user_license_id IN ($cids)";
		$db->setQuery( $query );
		$user_licenses = $db->loadObjectList();
		foreach($user_licenses as $user_license)
		{
			$this->unassignUserGroupForLicense($user_license);
		}
		return true;
	}

	function createToolbar($task, $option)
	{
		JHTML::_('stylesheet', 'administrator/components/'. $option . '/css/backend.css');
		parent::createToolbar($task, $option);
		if($task == 'display' || $task == 'cancel' || $task == '')
		{
			JToolBarHelper::custom('upgrade', 'arrow-up-4', '', JText::_('PAYPERDOWNLOADPLUS_UPGRADE'));
			JToolBarHelper::custom('extend', 'calendar', '', JText::_('PAYPERDOWNLOADPLUS_EXTEND'));
			JToolBarHelper::custom('del', 'delete', '', JText::_('PAYPERDOWNLOADPLUS_DELETE_EXPIRED'), false);
			JToolBarHelper::publish();
			JToolBarHelper::unpublish();
		}
	}
}
?>