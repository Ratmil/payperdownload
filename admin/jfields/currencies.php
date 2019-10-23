<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JFormHelper::loadFieldClass('list');

class JFormFieldCurrencies extends JFormFieldList
{
    public $type = 'Currencies';

    protected function getOptions()
    {
        $options = array();

        $db = JFactory::getDBO();

        $query = $db->getQuery(true);

        $fields = $db->quoteName(array('iso', 'currency'));
        $fields[0] = 'DISTINCT ' . $fields[0]; // prepend distinct to the first quoted field
        $query->select($fields);
        $query->from($db->quoteName('#__payperdownloadplus_currencies'));
        $query->where($db->quoteName('iso') . ' <> ' . $db->quote('-'));
        $query->order($db->quoteName('iso') . ' ASC'); // because when translated, currency order has no meaning

        $db->setQuery($query);

        try {
            $results = $db->loadAssocList('iso');
            foreach ($results as $result) {
                $options[] = JHTML::_('select.option', $result['iso'], $result['currency'] . ' (' . $result['iso'] . ')');
            }
        } catch (RuntimeException $e) {
            $options[] = JHTML::_('select.option', 'USD', 'USD');
        }

        return $options;
    }

    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        if ($return) {
            $config = JComponentHelper::getParams('com_payperdownload');
            $default_currency = $config->get('default_currency', 'USD');

            $this->default = ($this->default) ? $this->default : $default_currency;
            $this->value = ($this->value) ? $this->value : $this->default;
        }

        return $return;
    }

}
?>