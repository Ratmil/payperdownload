<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Payperdownload model.
 *
 * @since  1.6
 */
class PayperdownloadModelLicense extends JModelAdmin
{
	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_PAYPERDOWNLOAD';

	/**
	 * @var   	string  	Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_payperdownload.license';

	/**
	 * @var null  Item data
	 * @since  1.6
	 */
	protected $item = null;

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 *
	 * @since    1.6
	 */
	public function getTable($type = 'License', $prefix = 'PayperdownloadTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
        // Initialise variables.
        $app = JFactory::getApplication();

        // Get the form.
        $form = $this->loadForm(
                'com_payperdownload.license', 'license',
                array('control' => 'jform',
                        'load_data' => $loadData
                )
        );

        if (empty($form))
        {
            return false;
        }

        return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return   mixed  The data for the form.
	 *
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_payperdownload.edit.license.data', array());

		if (empty($data))
		{
			if ($this->item === null)
			{
				$this->item = $this->getItem();
			}

			$data = $this->item;


			// Support for multiple or not foreign key field: currency_code
// 			$array = array();

// 			foreach ((array) $data->currency_code as $value)
// 			{
// 				if (!is_array($value))
// 				{
// 					$array[] = $value;
// 				}
// 			}
// 			if(!empty($array)){

// 			$data->currency_code = $array;
// 			}

			// Support for multiple or not foreign key field: user_group
			$array = array();

			foreach ((array) $data->user_group as $value)
			{
				if (!is_array($value))
				{
					$array[] = $value;
				}
			}
			if(!empty($array)){

			$data->user_group = $array;
			}
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function getItem($pk = null)
	{
        if ($item = parent::getItem($pk))
        {
            // Do any procesing on fields here if needed
        }

        return $item;
	}

	/**
	 * Method to change the name.
	 *
	 * @param   string   $name        The name.
	 *
	 * @return  string  New name
	 */
	protected function generateNewName($name)
	{
	    return $name . ' - ' . JText::_("PAYPERDOWNLOADPLUS_COPY_TEXT");
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.0
	 */
	public function save($data)
	{
	    $input = JFactory::getApplication()->input;

	    if ($input->get('task') == 'save2copy') {

	        $origTable = clone $this->getTable();
	        $origTable->load($input->getInt('license_id'));

	        $data['license_name'] = $this->generateNewName($origTable->license_name);
	        $data['enabled'] = false;
	    }

	    return parent::save($data);
	}

	/**
	 * Method to duplicate a License
	 *
	 * @param   array  &$pks  An array of primary key IDs.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
	{
		$user = JFactory::getUser();

		// Access checks.
		if (!$user->authorise('core.create', 'com_payperdownload'))
		{
			throw new Exception(JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$dispatcher = JEventDispatcher::getInstance();
		$context    = $this->option . '.' . $this->name;

		// Include the plugins for the save events.
		JPluginHelper::importPlugin($this->events_map['save']);

		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($table->load($pk, true))
			{
				// Reset the id to create a new record.
				$table->license_id = 0;

				$table->license_name = $this->generateNewName($table->license_name);
				$table->enabled = false;

				if (!$table->check())
				{
					throw new Exception($table->getError());
				}

				// Trigger the before save event.
				$result = $dispatcher->trigger($this->event_before_save, array($context, &$table, true));

				if (in_array(false, $result, true) || !$table->store())
				{
					throw new Exception($table->getError());
				}

				// Trigger the after save event.
				$dispatcher->trigger($this->event_after_save, array($context, &$table, true));
			}
			else
			{
				throw new Exception($table->getError());
			}
		}

		// Clean cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to delete a License
	 *
	 * @param   array  &$pks  An array of primary key IDs.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws  Exception
	 */
	public function delete(&$pks)
	{
	    $user = JFactory::getUser();

	    // Access checks.
	    if (!$user->authorise('core.delete', 'com_payperdownload'))
	    {
	        throw new Exception(JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
	    }

	    //$dispatcher = JEventDispatcher::getInstance();
	    $context    = $this->option . '.' . $this->name;

	    // Include the plugins for the save events.
	    //JPluginHelper::importPlugin($this->events_map['save']);

	    $table = $this->getTable();

	    foreach ($pks as $pk)
	    {
	        if ($table->load($pk))
	        {
	            // Trigger the before save event.
	            //$result = $dispatcher->trigger($this->event_before_save, array($context, &$table, true));

	            if (!$table->delete($pk))
	            {
	                throw new Exception($table->getError());
	            }

	            // Trigger the after save event.
	            //$dispatcher->trigger($this->event_after_save, array($context, &$table, true));
	        }
	        else
	        {
	            throw new Exception($table->getError());
	        }
	    }

	    // Clean cache
	    $this->cleanCache();

	    return true;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   JTable  $table  Table Object
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->license_id))
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '')
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__payperdownloadplus_licenses');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}
}
