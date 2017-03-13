<?php
/**
 * @package	Payperdownload
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * Content PayToReadMore Plugin
 *
 */
class plgContentPayToReadmore extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 */
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		// load the language file
		$lang = JFactory::getLanguage();
		$lang->load('plg_content_paytoreadmore', JPATH_SITE.'/administrator');
	}
	
	function onPrepareContent( &$article, &$params, $limitstart )
	{
		$article_id = null;
		if(isset($article->id))
			$article_id = $article->id;
		$article->text = $this->_processText($article->text, $article_id);
	}
	
	function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		$article_id = null;
		if(isset($article->id))
			$article_id = $article->id;
		$article->text = $this->_processText($article->text, $article_id);
	}
	
	function cleanHtml($text)
	{
		$text = preg_replace("/<\/?[a-zA-Z0-9]+[^>]*>/", "", $text);
		$text = preg_replace("/&[a-zA-Z]{1,6};/", "", $text);
		return $text;
	}
	
	function _processText($text, $id, $context = "")
	{
		if(!preg_match("/\[PPD_PAYTOREADMORE\]/", $text) && !preg_match("/<hr\s+id=\"ppd-paytoreadmore\"\s+style=\".*\"\s*\/>/", $text))
		{
			return $text;
		}
		$helper_file = JPATH_SITE . '/components/com_payperdownload/helpers/ppd.php';
		if(file_exists($helper_file))
		{
			require_once( $helper_file );
			if($context == 'com_search.search')
			{
				$cleanText = $this->cleanHtml($text);
			}
			else
			{
				$cleanText = $text;
			}
			if(!isset($id))
			{
				if(preg_match("/\[PPD_PAYTOREADMORE\]/", $cleanText))
				{
					list($text1, $text2) = preg_split( "/\[PPD_PAYTOREADMORE\]/", $cleanText, 2 );
					$text = $text1;
				}
				else if(preg_match("/<hr\s+id=\"ppd-paytoreadmore\"\s+style=\".*\"\s*\/>/", $cleanText))
				{
					list($text1, $text2) = preg_split( "/<hr\s+id=\"ppd-paytoreadmore\"\s+style=\".*\"\s*\/>/", $cleanText, 2 );
					$text = $text1;
				}
			}
			else if($this->_hasAccessToArticle($id))
			{
				$text = preg_replace( "/<hr\s+id=\"ppd-paytoreadmore\"\s+style=\".*\"\s*\/>/", "", $text );
				if(preg_match("/\[PPD_PAYTOREADMORE\]/", $cleanText))
					$text = preg_replace( "/\[PPD_PAYTOREADMORE\]/", "", $cleanText );
			}
			else
			{
				if(preg_match("/\[PPD_PAYTOREADMORE\]/", $cleanText))
				{
					list($text1, $text2) = preg_split( "/\[PPD_PAYTOREADMORE\]/", $cleanText, 2 );
					$text = $text1;
					$link = $this->_getPaymentViewForArticle($id);
					$readmorelink = "<a href=\"" . $link . "\" title=\"" . htmlspecialchars(JText::_("PAYTOREADMORE_PAY_LINK_TEXT")) . "\" class=\"readon\">" . 
					  htmlspecialchars(JText::_("PAYTOREADMORE_PAY_LINK_TEXT"))	. "</a>";
					$text = $text1 . $readmorelink;
				}
				else if(preg_match("/<hr\s+id=\"ppd-paytoreadmore\"\s+style=\".*\"\s*\/>/", $text))
				{
					list($text1, $text2) = preg_split( "/<hr\s+id=\"ppd-paytoreadmore\"\s+style=\".*\"\s*\/>/", $text, 2 );
					$text = $text1;
					$link = $this->_getPaymentViewForArticle($id);
					$readmorelink = "<a href=\"" . $link . "\" title=\"" . htmlspecialchars(JText::_("PAYTOREADMORE_PAY_LINK_TEXT")) . "\" class=\"readon\">" . 
					  htmlspecialchars(JText::_("PAYTOREADMORE_PAY_LINK_TEXT"))	. "</a>";
					$text = $text1 . $readmorelink;
				}
			}
			return $text;
		}
	}
	
	function _hasAccessToArticle($article_id)
	{
		$user = JFactory::getUser();
		$ppd = new PPDAccess();
		if($ppd->isPrivilegedUser($user))
			return true;
		$article_id = (int) $article_id;
		$option = JRequest::getVar('option');
		$db = JFactory::getDBO();
		if($option == "com_content")
			$db->setQuery("SELECT catid FROM #__content WHERE id = " . $article_id);
		else if($option == "com_k2")
			$db->setQuery("SELECT catid FROM #__k2_items WHERE id = " . $article_id);
		else 
			return true;
		$content_category = (int)$db->loadResult();
		$option = $db->escape($option);
		$match1 = $content_category . "-%";
		$match2 = $content_category . "_%";
		$query = "SELECT * FROM #__payperdownloadplus_resource_licenses 
			WHERE 
			(resource_option_parameter = '$option')
			AND (resource_id = $article_id OR (resource_id < 0 AND 
				(resource_params LIKE '$match1' OR resource_params LIKE '$match2')
				) OR 
				(resource_id < 0 AND (resource_params LIKE '0-%' OR resource_params LIKE '0_%'))
				) 
			AND #__payperdownloadplus_resource_licenses.enabled = 1";
		$db->setQuery( $query );
		$resources = $db->loadObjectList();
		
		if($resources && count($resources) > 0)
		{
			return $ppd->isThereAvailableResource($resources, $article_id);
		}
		else
			return true; // the resource (article) is not protected
	}
	
	/**
	Returns the url of a payment view to pay for an article
	*/
	function _getPaymentViewForArticle($article_id)
	{
		$article_id = (int) $article_id;
		$db = JFactory::getDBO();
		$option = JRequest::getVar('option');
		if($option == "com_content")
			$db->setQuery("SELECT catid FROM #__content WHERE id = " . $article_id);
		else if($option == "com_k2")
			$db->setQuery("SELECT catid FROM #__k2_items WHERE id = " . $article_id);
		else 
			return true;
		$content_category = (int)$db->loadResult();
		$option = $db->escape($option);
		$match1 = $content_category . "-%";
		$match2 = $content_category . "_%";
		$query = "SELECT * FROM #__payperdownloadplus_resource_licenses WHERE resource_option_parameter = '$option' 
			AND (resource_id = $article_id OR 
			(resource_id < 0 AND 
				(resource_params LIKE '$match1' OR resource_params LIKE '$match2')
			) OR 
			(resource_id < 0 AND (resource_params LIKE '0-%' OR resource_params LIKE '0_%'))
			) 
			AND #__payperdownloadplus_resource_licenses.enabled = 1";
		$db->setQuery( $query );
		$resources = $db->loadObjectList();
		$licenses = array();
		$resources_ids = array();
		foreach($resources as $resource)
		{
			if($resource->license_id)
			{
				$licenses[] = $resource->license_id;
			}
			else
			{
				$resources_ids[] = $resource->resource_license_id;
			}
		}
		$licenses_ids = implode( ',', $licenses );
		//$return = $this->_getLinkForArticle($article_id);
		//$return = base64_encode($return);
		$link = "index.php?option=com_payperdownload&view=pay&m=1&h=1&lid=" . $licenses_ids;
		if(count($resources_ids) > 0)
		{
			$link .= "&res=" . (int)$resources_ids[0];
			if(!$this->_isResourceShared((int)$resources_ids[0]))
				$link .= "&item=" . (int)$article_id;
		}
		//$link .= "&returnurl=" . urlencode($return);
		$menuitems = $this->_getMenuItems();
		if($menuitems && $menuitems->payment_page_menuitem)
			$link .= "&Itemid=" . (int)$menuitems->payment_page_menuitem;
		return $link;
	}
	
	function _getLinkForArticle($id)
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT catid FROM #__content WHERE id = " . (int)$id);
		$catid = (int)$db->loadResult();
		$contentRouterHelperFile = 
				JPATH_SITE . "/components/com_content/helpers/route.php";
		if(file_exists($contentRouterHelperFile))
		{
			require_once($contentRouterHelperFile);
			if(class_exists("ContentHelperRoute"))
			{
				$link = JRoute::_(ContentHelperRoute::getArticleRoute($id, $catid));
				$protocol = $_SERVER['SERVER_PROTOCOL'];
				if(strtolower(substr($protocol, 0, 5)) == 'https')
					$prefix = "https://";
				else
					$prefix = "http://";
				$port = $_SERVER['SERVER_PORT'];
				if($port == '80')
					$port = '';
				else
					$port = ':' . $port;
				return $prefix . $_SERVER['SERVER_NAME'] . $port . $link;
			}
		}
		return JURI::base() . "index.php?option=com_content&view=article&id=" . $id;
	}
	
	function _getMenuItems()
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT config_id, payment_page_menuitem, thankyou_page_menuitem FROM #__payperdownloadplus_config", 0, 1);
		return $db->loadObject();
	}
	
	function _isResourceShared($resource_id)
	{
		$db = JFactory::getDBO();
		$query = "SELECT shared FROM #__payperdownloadplus_resource_licenses 
			WHERE resource_license_id = " . (int)$resource_id;
		$db->setQuery( $query );
		$shared = $db->loadResult();
		return $shared;
	}

}