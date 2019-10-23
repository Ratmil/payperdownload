<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * General Controller
 */
class PayPerDownloadController extends JControllerLegacy
{
    /**
     * display task
     *
     * @return void
     */
    function display($cachable = false, $urlparams = false)
    {
        require_once JPATH_ADMINISTRATOR.'/components/com_payperdownload/helpers/payperdownload.php';

        $app = JFactory::getApplication();
        $input = $app->input;

        // While moving to all MVC
        $view = $input->get('view', 'info');
        if ($view === 'info' || $view === 'licenses' || $view === 'license') {
            $input->set('view', $view);
        } else {
            $task = $input->get('task', '');
            $option = $input->get('option', '');
            $pageToShow = $input->get('adminpage');
            require_once(JPATH_COMPONENT.'/controllers/'.$pageToShow.'.php');
            $formName = ucfirst($pageToShow).'Form';
            if (class_exists($formName)) {
                // Create the object and show the form
                $form = new $formName();
                $form->showForm($task, $option);
            }

            return;
        }

        PayParDownloadHelper::addSubmenu($view);

        parent::display($cachable, $urlparams);

        return $this;
    }
}
