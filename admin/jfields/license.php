<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die();

class JFormFieldLicense extends JFormField
{
	protected $type 		= 'License';

	protected function getInput() {
		
		$db = JFactory::getDBO();
		$query = "SELECT license_id as value, license_name as text FROM #__payperdownloadplus_licenses ORDER BY license_name";
		$db->setQuery( $query );
		$licenses = $db->loadObjectList();
		return JHTML::_('select.genericlist',  $licenses,  $this->name, 'class="inputbox"', 'value', 'text', $this->value, $this->id );
	}
}
?>