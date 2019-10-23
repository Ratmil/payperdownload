<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

define ('SERVER_REMOTE_URI', 'https://updates.simplifyyourweb.com/free/payperdownload/');

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * Pay Per Download Info View
 */
class PayPerDownloadViewInfo extends JViewLegacy
{
	/**
	 * display method of view
	 * @return void
	 */
	function display($tpl = null)
	{
		// Check for errors

	    $errors = $this->get('Errors');
	    if (isset($errors) && count($errors)) {

			$app = JFactory::getApplication();
			$app->enqueueMessage(implode('<br />', $errors), 'error');
			$app->setHeader('status', 500, true);

			return false;
		}

		$config = JComponentHelper::getParams('com_payperdownload');
		$this->debug = $config->get('debug', false);

		// extended plugins

		$model = $this->getModel('info');
		$this->extended_plugins = $model->getExtendedPlugins();

		// installed extension version

		$this->extension_version = strval(simplexml_load_file(JPATH_ADMINISTRATOR . '/components/com_payperdownload/payperdownload.xml')->version);

		// available version

		$HTTPClient = new JHttp();

		$this->version_array = '';
		try {
		    $response = $HTTPClient->get(SERVER_REMOTE_URI.'com_payperdownload.json')->body;
		    if ($response) {
		        $this->version_array = json_decode($response, true);
		    }
		} catch(Exception $e) {
		    // ignore error
		}

		// license information

		$this->setDocument();

		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();

		parent::display($tpl);
	}

	protected function getIcon($link, $image, $text, $target = '', $title = '')
	{
	    $lang = JFactory::getLanguage();
	    $float = ($lang->isRTL()) ? 'right' : 'left';

	    if ($target) {
	        $target = ' target="'.$target.'"';
	    }

	    $hastooltip = '';
	    if ($title) {
	        $title = ' title="'.$title.'"';
	        $hastooltip = ' hasTooltip';
	    }

	    $html = '<div class="icon" style="float: '.$float.'">';
	    $html .= '<a class="task'.$hastooltip.'" href="'.$link.'"'.$title.$target.'>';
	    $html .= '<i class="'.$image.'"></i>';
	    $html .= '<span>'.$text.'</span>';
	    $html .= '</a>';
	    $html .= '</div>';

	    return $html;
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_PAYPERDOWNLOAD_INFO_TITLE'), 'dashboard');

		if (JFactory::getUser()->authorise('core.admin', 'com_payperdownload')) {
			JToolBarHelper::preferences('com_payperdownload');
		}
	}

	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument()
	{
	    JHTML::_('stylesheet', 'administrator/components/com_payperdownload/css/backend.css');
	}
}
?>
