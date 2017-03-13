<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die;

// import the JPlugin class
jimport('joomla.event.plugin');

class plgPayperDownloadPlusContent extends JPlugin
{
	public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject);
		// load the language file
		$lang = JFactory::getLanguage();
		$lang->load('plg_payperdownloadplus_content', JPATH_SITE.'/administrator');
	}

	function onIsActive(&$plugins)
	{
		$component = JComponentHelper::getComponent('com_content', true);
		if($component->enabled)
		{
			jimport('joomla.filesystem.file');
			$image = "";
			$version = new JVersion;
			if($version->RELEASE == "1.5")
			{
				if(JFile::exists(JPATH_ROOT . '/administrator/templates/khepri/images/header/icon-48-article.png'))
				$image = "administrator/templates/khepri/images/header/icon-48-article.png";
			}
			else if($version->RELEASE >= "1.6")
			{
				if(JFile::exists(JPATH_ROOT . '/administrator/templates/hathor/images/header/icon-48-article.png'))
					$image = "administrator/templates/hathor/images/header/icon-48-article.png";
			}
				
			$plugins[] = array("name" => "Content", "description" => JText::_("PAYPERDOWNLOADPLUS_CONTENT_ARTICLE_72"), 
				"image" => $image);
		}
	}
	
	function reorderCats(&$cats_ordered, $cats, $parent_id, $depth)
	{
		$count = count($cats);
		for($i = 0; $i < $count; $i++)
		{
			$cat = $cats[$i];
			if($cat->parent_id == $parent_id)
			{
				$cat->depth = $depth;
				$cats_ordered[] = $cat;
				if(!$cat->end_branch)
					$this->reorderCats($cats_ordered, $cats, $cat->id, $depth + 1);
			}
		}
	}
	
	function getArticleCategories()
	{
		$version = new JVersion;
		if($version->RELEASE == "1.5")
		{
			$db = JFactory::getDBO();
			$db->setQuery("SELECT id, title, 0 as parent_id, 1 as sec, 0 as end_branch FROM #__sections WHERE scope='content'");
			$sections = $db->loadObjectList();
			$db->setQuery('SELECT id, title, section as parent_id FROM #__categories');
			$categories = $db->loadObjectList();
			foreach($categories as $category)
			{
				if(preg_match('/^\s*\d+\s*$/', $category->parent_id))
				{
					$category->parent_id = (int)$category->parent_id;
					$category->end_branch = true;
					$sections[] = $category;
				}
			}
			$cats_ordered = array();
			$this->reorderCats($cats_ordered, $sections, 0, 0);
			return $cats_ordered;
		}
		else if($version->RELEASE >= "1.6")
		{
			$db = JFactory::getDBO();
			$db->setQuery("SELECT id, title, parent_id, 0 as end_branch FROM #__categories WHERE extension='com_content'");
			$cats = $db->loadObjectList();
			$cats_ordered = array();
			$this->reorderCats($cats_ordered, $cats, 1, 0);
			return $cats_ordered;
		}
		else
			return array();
	}
	
	function getArticles($category)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT id, title FROM #__content WHERE catid = ' . (int)$category);
		return $db->loadObjectList();
	}
	
	function onRenderConfig($pluginName, $resource)
	{
		if($pluginName == "Content")
		{
			$paytoreadmore = "";
			$content_id = "";
			$category_id = "";
			if($resource)
			{
				$content_id = $resource->resource_id;
				list($category_id, $paytoreadmore) = explode('-', $resource->resource_params);
				if($category_id)
					$articles = $this->getArticles($category_id);
			}
			$uri = JURI::root();
			$scriptPath = "administrator/components/com_payperdownload/js/";
			JHTML::script($scriptPath . 'ajax_source.js', false);
			$version = new JVersion;
			if($version->RELEASE >= "1.6")
				$plugin_path = "plugins/payperdownloadplus/content/";
			else
				$plugin_path = "plugins/payperdownloadplus/";
			$scriptPath = $uri . $plugin_path;
			JHTML::script($scriptPath . 'content_plugin.js', false);
			$cats = $this->getArticleCategories();
			?>
			<tr>
			<td align="left" class="key"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_CONTENT_PLUGIN_CATEGORY"));?></td>
			<td>
			<select id="content_category" name="content_category" onchange="content_plugin_category_change();">
			<option value="0"><?php echo JText::_("PAYPERDOWNLOADPLUS_CONTENT_PLUGIN_ALL_CATEGORIES");?></option>
			<?php
			$group_open = false;
			foreach($cats as $cat)
			{
				if($cat->sec)
				{
					if($group_open)
						echo "</optgroup>";
					echo "<optgroup label=\"" . htmlspecialchars($cat->title) . "\">";
					$group_open = true;
				}
				else
				{
					$space = '';
					for($i = 0; $i < $cat->depth; $i++)
						$space .= '&nbsp;&nbsp;&nbsp;&nbsp;';
					$selected = $cat->id == $category_id ? "selected":"";
					echo "<option value=\"" . htmlspecialchars($cat->id) . "\" $selected>" . $space . htmlspecialchars($cat->title) . "</option>";
				}
			}
			if($group_open)
				echo "</optgroup>";
			?>
			</select>
			</td>
			</tr>
			<tr>
			<td align="left" class="key"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_CONTENT_PLUGIN_ARTICLE"));?></td>
			<td>
			<select id="content_article" name="content_article">
			<option value="0"><?php echo JText::_("PAYPERDOWNLOADPLUS_CONTENT_PLUGIN_ALL_ARTICLES");?></option>
			<?php
			if($articles)
			foreach($articles as $article)
			{
				$selected = $article->id == $content_id ? "selected":"";
				echo "<option value=\"" . htmlspecialchars($article->id) . "\" $selected>" . htmlspecialchars($article->title) . "</option>";
			}
			?>
			</select>
			&nbsp;
			<input type="text" id="search_text" />
			&nbsp;
			<input type="button" id="search_button" value="<?php echo JText::_("PAYPERDOWNLOADPLUS_CONTENT_PLUGIN_SEARCH")?>" onclick="content_plugin_search();" />
			<script type="text/javascript">
			var cancel_text = '<?php echo JText::_("PAYPERDOWNLOADPLUS_CONTENT_PLUGIN_CANCEL", true)?>';
			</script>
			<div id="search_result" style="position:absolute;visibility:hidden;border-width:1px;border-style:solid;background-color:#ffffff;"></div>
			</td>
			</tr>
			<tr>
			<td align="left" class="key"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_CONTENT_PLUGIN_PAY_TO_READ_MORE"));?></td>
			<?php
			$checked = $paytoreadmore == "1" ? "checked" : "";
			?>
			<td><input type="checkbox" name="paytoreadmore" <?php echo $checked;?> value="1"/></td>
			</tr>
			<?php
		}
	}
	
	function onGetSaveData(&$resourceId, 
		$pluginName, &$resourceName, &$resourceParams, &$optionParameter,
		&$resourceDesc)
	{
		if($pluginName == "Content")
		{
			$optionParameter = "com_content";
			$resourceId = JRequest::getInt('content_article');
			$categoryId = JRequest::getInt('content_category');
			$paytoreadmore = JRequest::getInt('paytoreadmore', 0);
			$db = JFactory::getDBO();
			$query = "";
			$resourceDesc = JText::_("PAYPERDOWNLOADPLUS_CONTENT_PLUGIN_ALL_ARTICLES");
			if($resourceId)
			{
				$query = "SELECT id, title FROM #__content WHERE id = " . $resourceId;
				$resourceDesc = JText::_("PAYPERDOWNLOADPLUS_CONTENT_PLUGIN_CONTENT_ARTICLE");
			}
			else if($categoryId)
			{
				$resourceId = -1;
				$query = "SELECT id, title FROM #__categories WHERE id = " . $categoryId;
				$resourceDesc = JText::_("PAYPERDOWNLOADPLUS_CONTENT_PLUGIN_CONTENT_CATEGORY");
			}
			else
				$resourceId = -1;
			$resourceName = JText::_("PAYPERDOWNLOADPLUS_CONTENT_PLUGIN_ALL_ARTICLES");
			if($query)
			{
				$db->setQuery( $query );
				$resource = $db->loadObject();
				if($resource)
					$resourceName = $resource->title;
			}
			
			$resourceParams = $categoryId . "-" . $paytoreadmore;
		}
	}
	
	function onCheckDecreaseDownloadCount($option, $resources, $requiredLicenses, $resourcesId, &$decreaseDownloadCount)
	{
		if($option == 'com_content')
		{
			$decreaseDownloadCount = true;
		}
	}
	
	function getParentCategories($id)
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT catid FROM #__content WHERE id = " . (int)$id);
		$category = (int)$db->loadResult();
		$parentCategories = array();
		while($category)
		{
			$parentCategories []= $category;
			$db->setQuery("SELECT parent_id FROM #__categories WHERE id = " . (int)$category);
			$category = (int)$db->loadResult();
		}
		return $parentCategories;
	}
		
	function onValidateAccess($option, $resources, &$allowAccess, &$requiredLicenses, &$resourcesId)
	{
		$version = PAYPERDOWNLOADPLUS_VERSION;
		if($option == 'com_content' && JRequest::getVar('view') == 'article')
		{
			$requiredLicenses = array();
			$resourcesId = array();
			$download = JRequest::getInt('id', 0);
			if($download == 0)
				return;
			//Check articles licenses
			foreach($resources as $resource)
			{
				list($categoryId, $paytoreadmore) = explode('-', $resource->resource_params);
				if((int)$paytoreadmore != 1 && $resource->resource_id == $download)
				{
					if($resource->license_id)
					{
						if(array_search($resource->license_id, $requiredLicenses) === false)
							$requiredLicenses[] = $resource->license_id;
					}
					else
					{
						if(array_search($resource->resource_license_id, $resourcesId) === false)
							$resourcesId[] = $resource->resource_license_id;
					}
					$allowAccess = false;
				}
			}
			$parentCategories = $this->getParentCategories($download);
			//Check category licenses
			foreach($resources as $resource)
			{
				if($resource->resource_id < 0)
				{
					list($categoryId, $paytoreadmore) = explode('-', $resource->resource_params);
					if((int)$paytoreadmore != 1 && array_search($categoryId, $parentCategories) !== false)
					{
						if($resource->license_id)
						{
							if(array_search($resource->license_id, $requiredLicenses) === false)
								$requiredLicenses[] = $resource->license_id;
						}
						else
						{
							if(array_search($resource->resource_license_id, $resourcesId) === false)
								$resourcesId[] = $resource->resource_license_id;
						}
						$allowAccess = false;
					}
				}
			} 
			
			//Check all articles license
			foreach($resources as $resource)
			{
				if($resource->resource_id < 0)
				{
					list($categoryId, $paytoreadmore) = explode('-', $resource->resource_params);
					if((int)$paytoreadmore != 1 && $categoryId == 0)
					{
						if($resource->license_id)
						{
							if(array_search($resource->license_id, $requiredLicenses) === false)
								$requiredLicenses[] = $resource->license_id;
						}
						else
						{
							if(array_search($resource->resource_license_id, $resourcesId) === false)
								$resourcesId[] = $resource->resource_license_id;
						}
						$allowAccess = false;
					}
				}
			}
		}
	}
	
	function onAjaxCall($plugin, &$output)
	{
		if($plugin == "content")
		{
			$t = JRequest::getVar('t');
			$x = JRequest::getVar('x');
			$db = JFactory::getDBO();
			if($t == 's')
			{
				$x = $db->escape($x);
				$db->setQuery("SELECT id, title, catid FROM #__content WHERE title LIKE '%" . $x . "%'", 0, 10);
				$articles = $db->loadObjectList();
				$output = '<<' . count($articles);
				foreach($articles as $article)
				{
					$output .= '>' . htmlspecialchars($article->id) . "<" . htmlspecialchars($article->title) . "<" . htmlspecialchars($article->catid);
				}
				$output .= '>>';
			}
			else if($t == 'a')
			{
				$db->setQuery('SELECT id, title FROM #__content WHERE catid = ' . (int)$x);
				$articles = $db->loadObjectList();
				$output = '<<' . count($articles);
				foreach($articles as $article)
				{
					$output .= '>' . htmlspecialchars($article->id) . "<" . htmlspecialchars($article->title);
				}
				$output .= '>>';
			}
		}
	}
	
	function getReturnPage($option, &$returnPage)
	{
		/*if($option == "com_content")
		{
			$article = JRequest::getInt('id', 0);
			if(!$article)
			{
				return;
			}
			$link = $this->_getLinkForArticle($article);
			if($link != "")
				$returnPage = $link;
		}*/
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
		return "";
	}
	
	/*
	Returns item id for current article. 
	*/
	function onGetItemId($option, &$itemId)
	{
		if($option == 'com_content')
		{
			$itemId = JRequest::getInt('id', 0);
		}
	}
	
}
?>