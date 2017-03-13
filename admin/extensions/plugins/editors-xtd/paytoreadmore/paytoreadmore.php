<?php
/**
 * @package	Payperdownload
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * Editor PayToReadMore Button
 *
 * @package Editors-xtd
 * @since 1.5
 */
class plgButtonPayToReadmore extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param 	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		// load the language file
		$lang = JFactory::getLanguage();
		$lang->load('plg_editors_xtd_paytoreadmore', JPATH_SITE.'/administrator');
	}

	/**
	 * paytoreadmore button
	 */
	function onDisplay($name)
	{
		$mainframe = JFactory::getApplication();
		$doc 		= JFactory::getDocument();
		$template 	= $mainframe->getTemplate();

		$doc->addStyleSheet( JURI::root().'plugins/editors-xtd/paytoreadmore/paytoreadmore.css');
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
		$button->set('onclick', 'insertPayToReadmore(\''.$name.'\');return false;');
		$button->set('text', JText::_('PAYTOREADMORE_TEXT'));
		$button->set('name', 'paytoreadmore');
		// TODO: The button writer needs to take into account the javascript directive
		$button->set('link', 'javascript:void(0)');
		//$button->set('link', '#');

		return $button;
	}
}