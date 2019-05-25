<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die();

class JFormFieldAffiliate extends JFormField
{
	protected $type 		= 'Affiliate';

	protected function getInput() {
		
		$db = JFactory::getDBO();
		$query = "SELECT affiliate_program_id as value, program_name as text FROM #__payperdownloadplus_affiliates_programs ORDER BY program_name";
		$db->setQuery( $query );
		$affiliates = $db->loadObjectList();
		return JHTML::_('select.genericlist',  $affiliates,  $this->name, 'class="inputbox"', 'value', 'text', $this->value, $this->id );
	}
}
?>