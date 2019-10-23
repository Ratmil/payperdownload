<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die();

class PlgSystemUserLicenses extends JPlugin
{
	protected $autoloadLanguage = true;
	protected $app;
	protected $url;

	function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->app = JFactory::getApplication();
		$this->url = 'administrator/index.php?option=com_payperdownload&adminpage=users&view=users&filter_search';
	}

	/**
	 * The user list is modified in the onAfterRender trigger
	 * Supported backend templates: Isis and Hathor
	 */
	public function onAfterRender()
	{
		if ($this->app->isAdmin() && empty(JFactory::getUser()->guest)) {
			if($this->filterUserGroups()) {
				$option = $this->app->input->get('option', '');
				$view = $this->app->input->get('view', '');

				if($option == 'com_users' && ($view == 'users' OR $view == '')) {
					$body = JFactory::getApplication()->getBody();
					preg_match_all('@<tr class="row[01]+">.*(<div class="(btn-group|fltrt)">.*task=note\.add.*u_id=(\d+)".*<\/div>).*<\/tr>@isU', $body, $matches);

					if(!empty($matches[0])) {
						$template = JFactory::getApplication()->getTemplate();

						$lang = JFactory::getLanguage();
						$lang->load('plg_system_userlicenses.sys', JPATH_ADMINISTRATOR, 'en-GB', true);
						$lang->load('plg_system_userlicenses.sys', JPATH_ADMINISTRATOR, $lang->getTag(), true);

						foreach($matches[0] as $key => $value) {
							if(strpos($value, 'users.unblock') === false) {
								$replace_content = '<div class="' . $matches[2][$key] . '">';
								$replace_content .= '<a href="' . JUri::root().$this->url . '=' . $matches[3][$key] . '" class="hasTooltip btn btn-mini" title="' . JText::_('PLG_SYSTEM_USERLICENSES_TOOLTIP') . '">';
								$replace_content .= '<span class="icon-grid" aria-hidden="true"></span><span class="hidden-phone">' . JText::_('PLG_SYSTEM_USERLICENSES_USERBUTTON') . '</span>';
								$replace_content .= '</a></div>';

								$replace = $matches[1][$key].$replace_content;

								if($template == 'hathor') {
									$replace = $replace_content.$matches[1][$key];
								}

								$body = str_replace($matches[1][$key], $replace, $body);
							}
						}

						JFactory::getApplication()->setBody($body);
					}
				}
			}
		}
	}

	/**
	 * Checks the group of the user who has triggered the login process
	 *
	 * @param bool $user_id
	 *
	 * @return bool
	 */
	private function filterUserGroups($user_id = false)
	{
	    if(empty($user_id)) {
	        $user = JFactory::getUser();
	        $user_id = $user->id;
	    }

	    // Hard coded group due to security reasons - Joomla! default: 8 = Super Users
	    $filter_groups = array(8);
	    $user_groups = JAccess::getGroupsByUser($user_id);

	    foreach($user_groups as $user_group) {
	        if(in_array($user_group, $filter_groups)) {
	            return true;
	        }
	    }

	    return false;
	}

}
