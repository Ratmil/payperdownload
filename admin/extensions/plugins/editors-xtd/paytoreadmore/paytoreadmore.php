<?php
/**
 * @package	Payperdownload
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;

jimport( 'joomla.plugin.plugin' );

/**
 * Editor PayToReadMore Button
 *
 * @package Editors-xtd
 * @since 1.5
 */
class plgButtonPayToReadmore extends JPlugin
{
    protected $autoloadLanguage = true;

	/**
	 * paytoreadmore button
	 */
	function onDisplay($name)
	{
		$mainframe = JFactory::getApplication();
		$doc 		= JFactory::getDocument();
		$template 	= $mainframe->getTemplate();

		//$doc->addStyleSheet( JURI::root().'plugins/editors-xtd/paytoreadmore/paytoreadmore.css');
		$getContent = $this->_subject->getContent($name);
		$present = JText::_('PAYTOREADMORE_ALREADY_EXISTS', true) ;
		$js = "
			function insertPayToReadmore(editor) {
				var content = $getContent
				if (content.match(/\[PPD_PAYTOREADMORE\]/i)) {
					alert('$present');
					return false;
				} else {
					jInsertEditorText('[PPD_PAYTOREADMORE]', editor);
				}
			}
			";

		$doc->addScriptDeclaration($js);

		$button = new JObject();
		$button->set('modal', false);
		$button->set('class', 'btn');
		$button->set('onclick', 'insertPayToReadmore(\''.$name.'\');return false;');
		$button->set('text', JText::_('PAYTOREADMORE_TEXT'));
		$button->set('name', 'arrow-down');
		// TODO: The button writer needs to take into account the javascript directive
		//$button->set('link', 'javascript:void(0)');
		$button->set('link', '#');

		return $button;
	}
}