<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Payperdownload.
 *
 * @since  1.6
 */
class PayperdownloadViewLicenses extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		//PayParDownloadHelper::addSubmenu('licenses');

		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$canDo = PayParDownloadHelper::getActions();

		JToolBarHelper::title(JText::_('COM_PAYPERDOWNLOAD_LICENSES_TITLE'), 'grid');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/license';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('license.add', 'JTOOLBAR_NEW');

				if (isset($this->items[0]))
				{
					JToolbarHelper::custom('licenses.duplicate', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
				}
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				JToolBarHelper::editList('license.edit', 'JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->enabled))
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('licenses.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('licenses.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
// 			elseif (isset($this->items[0]))
// 			{
				// If this component does not use state then show a direct delete button as we can not trash
// 				JToolBarHelper::deleteList('', 'licenses.delete', 'JTOOLBAR_DELETE');
// 			}

// 			if (isset($this->items[0]->enabled))
// 			{
// 				JToolBarHelper::divider();
// 				JToolBarHelper::archiveList('licenses.archive', 'JTOOLBAR_ARCHIVE');
// 			}

// 			if (isset($this->items[0]->checked_out))
// 			{
// 				JToolBarHelper::custom('licenses.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
// 			}

            if (isset($this->items[0])) {
                JToolBarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'licenses.delete', 'JTOOLBAR_DELETE');
            }
		}

		// Show trash and delete for components that uses the state field
// 		if (isset($this->items[0]->enabled))
// 		{
// 			if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
// 			{
// 				JToolBarHelper::deleteList('', 'licenses.delete', 'JTOOLBAR_EMPTY_TRASH');
// 				JToolBarHelper::divider();
// 			}
// 			elseif ($canDo->get('core.edit.state'))
// 			{
// 				JToolBarHelper::trash('licenses.trash', 'JTOOLBAR_TRASH');
// 				JToolBarHelper::divider();
// 			}
// 		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_payperdownload');
		}

		JHtmlSidebar::setAction('index.php?option=com_payperdownload&view=licenses');
	}

	/**
	 * Method to order fields
	 *
	 * @return void
	 */
	protected function getSortFields()
	{
		return array(
			'a.`id`' => JText::_('JGRID_HEADING_ID'),
			'a.`license_name`' => JText::_('COM_PAYPERDOWNLOAD_LICENSES_LICENSE_NAME'),
			'a.`member_title`' => JText::_('COM_PAYPERDOWNLOAD_LICENSES_MEMBER_TITLE'),
			'a.`expiration`' => JText::_('COM_PAYPERDOWNLOAD_LICENSES_EXPIRATION'),
			'a.`price`' => JText::_('COM_PAYPERDOWNLOAD_LICENSES_PRICE'),
			'a.`currency_code`' => JText::_('COM_PAYPERDOWNLOAD_LICENSES_CURRENCY_CODE'),
		    'a.`level`' => JText::_('COM_PAYPERDOWNLOAD_LICENSES_LEVEL'),
		    'a.`enabled`' => JText::_('COM_PAYPERDOWNLOAD_LICENSES_ENABLED'),
		    'a.`max_download`' => JText::_('COM_PAYPERDOWNLOAD_LICENSES_MAX_DOWNLOADS')
			/*'a.`license_image`' => JText::_('COM_PAYPERDOWNLOAD_LICENSES_LICENSE_IMAGE')*/
			/*'a.`created_by`' => JText::_('COM_PAYPERDOWNLOAD_LICENSES_CREATED_BY'),*/
		);
	}

    /**
     * Check if state is set
     *
     * @param   mixed  $state  State
     *
     * @return bool
     */
    public function getState($state)
    {
        return isset($this->state->{$state}) ? $this->state->{$state} : false;
    }
}
