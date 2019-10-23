<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class PayPerDownloadViewMembership extends JViewLegacy
{
    protected $params;

	function display($tpl = null)
	{
	    $app = JFactory::getApplication();

	    $this->params = $app->getParams('com_payperdownload');

	    $jinput = $app->input;

	    $this->_prepareDocument();
	    $this->pageclass_sfx = trim(htmlspecialchars($this->params->get('pageclass_sfx', '')));

	    JHTML::_('stylesheet', 'components/com_payperdownload/css/frontend.css');

		$model = $this->getModel();
		if($model)
		{
		    $limit = $jinput->getInt('limit', $app->getCfg('list_limit'));
		    $start = $jinput->getInt('limitstart', 0);

			$members = $model->getMembers($start, $limit);
			$total = $model->getTotalMembers();

			jimport( 'joomla.html.pagination' );
			$objPagination = new JPagination( $total, $start, $limit );

			$this->assignRef("members", $members);
			$this->assignRef("pagination", $objPagination);

			parent::display($tpl);
		} else {
			echo "model not found";
		}
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 * @throws Exception
	 */
	protected function _prepareDocument()
	{
	    $app = JFactory::getApplication();
	    $menus = $app->getMenu();
	    $title = null;

	    // Because the application sets a default page title,
	    // we need to get it from the menu item itself
	    $menu = $menus->getActive();

	    if ($menu) {
	        $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
	    } else {
	        $this->params->def('page_heading', JText::_('PAYPERDOWNLOADPLUS_MEMBERSHIPS'));
	    }

	    $title = $this->params->get('page_title', '');

	    if (empty($title)) {
	        $title = $app->getCfg('sitename');
	    } elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
	        $title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
	    } elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
	        $title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
	    }

	    $this->document->setTitle($title);

	    if ($this->params->get('menu-meta_description')) {
	        $this->document->setDescription($this->params->get('menu-meta_description'));
	    }

	    if ($this->params->get('menu-meta_keywords')) {
	        $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
	    }

	    if ($this->params->get('robots')) {
	        $this->document->setMetadata('robots', $this->params->get('robots'));
	    }
	}
}
?>