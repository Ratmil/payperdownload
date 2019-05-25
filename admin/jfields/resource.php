<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */
defined('_JEXEC') or die();

class JFormFieldResource extends JFormField
{
	protected $type 		= 'Resource';

	protected function getInput() {
		
		$db = JFactory::getDBO();
		$query = "SELECT resource_license_id as value, 	CONCAT(resource_description, ' ', resource_name) as text FROM #__payperdownloadplus_resource_licenses";
		$db->setQuery( $query );
		$resources = $db->loadObjectList();
		return JHTML::_('select.genericlist',  $resources,  $this->name, 'class="inputbox"', 'value', 'text', $this->value, $this->id );
	}
}
?>