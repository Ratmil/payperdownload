<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class PayPerDownloadViewAlllicenses extends JViewLegacy
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

			$licenses = $model->getAllLicenses($start, $limit);

			$showResources = false;
			if ($this->params->get('all_include_resources', 0)) {
			    $showResources = true;
			}

			$total = $model->getTotalLicenses();

			jimport( 'joomla.html.pagination' );
			$objPagination = new JPagination( $total, $start, $limit );

			$this->assignRef("pagination", $objPagination);
			$this->assignRef("licenses", $licenses);
			$this->assignRef("showResources", $showResources); // keep for backward compatibility
			$multipleLicenseView = 0;
			$this->assignRef("multipleLicenseView", $multipleLicenseView);

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
	        $this->params->def('page_heading', JText::_('PAYPERDOWNLOADPLUS_ALL_LICENSES'));
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