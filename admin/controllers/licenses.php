<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Licenses list controller class.
 *
 * @since  1.6
 */
class PayperdownloadControllerLicenses extends JControllerAdmin
{
	/**
	 * Method to clone existing Licenses
	 *
	 * @return void
	 */
	public function duplicate()
	{
		// Check for request forgeries
		Jsession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get id(s)
		$pks = $this->input->post->get('cid', array(), 'array');

		try
		{
			if (empty($pks))
			{
				throw new Exception(JText::_('COM_PAYPERDOWNLOAD_NO_ELEMENT_SELECTED'));
			}

			JArrayHelper::toInteger($pks);
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(Jtext::_('COM_PAYPERDOWNLOAD_ITEMS_SUCCESS_DUPLICATED'));
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect('index.php?option=com_payperdownload&view=licenses');
	}

	/**
	 * Method to trash existing Licenses
	 *
	 * @return void
	 */
	public function delete()
	{
	    // Check for request forgeries
	    Jsession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

	    // Get id(s)
	    $pks = $this->input->post->get('cid', array(), 'array');

	    try
	    {
	        if (empty($pks))
	        {
	            throw new Exception(JText::_('COM_PAYPERDOWNLOAD_NO_ELEMENT_SELECTED'));
	        }

	        JArrayHelper::toInteger($pks);
	        $model = $this->getModel();
	        $model->delete($pks);
	        $this->setMessage(Jtext::_('COM_PAYPERDOWNLOAD_ITEMS_SUCCESS_TRASHED'));
	    }
	    catch (Exception $e)
	    {
	        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
	    }

	    $this->setRedirect('index.php?option=com_payperdownload&view=licenses');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. Configuration array for model
	 *
	 * @return  object	The Model
	 *
	 * @since    1.6
	 */
	public function getModel($name = 'license', $prefix = 'PayperdownloadModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = JFactory::getApplication()->input;
		$pks   = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		ArrayHelper::toInteger($pks);
		ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		JFactory::getApplication()->close();
	}
}
