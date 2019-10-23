<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Payperdownload records.
 *
 * @since  1.6
 */
class PayperdownloadModelLicenses extends JModelList
{

/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @see        JController
	* @since      1.6
	*/
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.license_id',
				'license_name', 'a.license_name',
				'member_title', 'a.member_title',
				'expiration', 'a.expiration',
				'price', 'a.price',
				'currency_code', 'a.currency_code',
				'level', 'a.level',
			    'enabled', 'a.enabled',
			    'max_download', 'a.max_download',
			    'state'
				/*'created_by', 'a.created_by',*/
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = 'a.license_id', $direction = 'asc')
	{
	    $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
	    $this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'cmd'));

        // List state information.
	    parent::populateState($ordering, $direction);

        //$context = $this->getUserStateFromRequest($this->context . '.context', 'context', 'com_content.article', 'CMD');
        //$this->setState('filter.context', $context);

        // Split context into component and optional section
//         $parts = FieldsHelper::extract($context);

//         if ($parts)
//         {
//             $this->setState('filter.component', $parts[0]);
//             $this->setState('filter.section', $parts[1]);
//         }
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return   string A store id.
	 *
	 * @since    1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

        return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT a.*'
			)
		);
		$query->from('#__payperdownloadplus_licenses AS a');

		// Join over the user field 'created_by'
		//$query->select('created_by.name AS created_by');
		//$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.license_id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.license_name LIKE ' . $search . '  OR  a.member_title LIKE ' . $search . ' )');
			}
		}

		// Filter by state
		$state = $this->getState('filter.state');

		if (is_numeric($state)) {
		    $query->where('a.enabled = ' . (int) $state);
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', "a.license_id");
		$orderDirn = $this->state->get('list.direction', "asc");

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		foreach ($items as $oneItem)
		{
            // $oneItem->currency_code = JText::_('COM_PAYPERDOWNLOAD_LICENSES_CURRENCY_CODE_OPTION_' . strtoupper($oneItem->currency_code));
		}

		return $items;
	}
}
