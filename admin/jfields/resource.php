<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
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