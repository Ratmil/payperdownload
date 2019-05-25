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
    protected $autoloadLanguage = true;

	function onIsActive(&$plugins)
	{
		$component = JComponentHelper::getComponent('com_content', true);
		if($component->enabled)
		{
		    $plugins[] = array("name" => "Content", "description" => JText::_("PAYPERDOWNLOADPLUS_CONTENT_PLUGIN_JOOMLAARTICLE"), "image" => "plugins/payperdownloadplus/content/content.png");
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
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName('id'));
		$query->select($db->quoteName('title'));
		$query->select($db->quoteName('parent_id'));
		$query->select('0 AS end_branch');
		$query->from($db->quoteName('#__categories'));
		$query->where($db->quoteName('extension') . ' = ' . $db->quote('com_content'));

		$db->setQuery($query);

		$cats_ordered = array();

		try {
		    $cats = $db->loadObjectList();
		    $this->reorderCats($cats_ordered, $cats, 1, 0);
		} catch (RuntimeException $e) {
		    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $cats_ordered;
	}

	function getArticles($category)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'title')));
		$query->from($db->quoteName('#__content'));
		$query->where($db->quoteName('catid') . ' = ' . (int)$category);

		$db->setQuery($query);

		$articles = array();

		try {
		    $articles = $db->loadObjectList();
		} catch (RuntimeException $e) {
		    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $articles;
	}

	function onRenderConfig($pluginName, $resource)
	{
		if($pluginName == "Content")
		{
			$paytoreadmore = "";
			$content_id = "";
			$category_id = "";
			$articles = array();
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

			$plugin_path = "plugins/payperdownloadplus/content/";

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
// 				if($cat->sec)
// 				{
// 					if($group_open)
// 						echo "</optgroup>";
// 					echo "<optgroup label=\"" . htmlspecialchars($cat->title) . "\">";
// 					$group_open = true;
// 				}
// 				else
// 				{
					$space = '';
					for($i = 0; $i < $cat->depth; $i++)
						$space .= '&nbsp;&nbsp;&nbsp;&nbsp;';
					$selected = $cat->id == $category_id ? "selected":"";
					echo "<option value=\"" . htmlspecialchars($cat->id) . "\" $selected>" . $space . htmlspecialchars($cat->title) . "</option>";
// 				}
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
			<div id="search_result" style="position:absolute;visibility:hidden;z-index:1000;border-width:1px;border-style:solid;background-color:#ffffff;"></div>
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

	function onGetSaveData(&$resourceId, $pluginName, &$resourceName, &$resourceParams, &$optionParameter, &$resourceDesc)
	{
		if($pluginName == "Content")
		{
		    $jinput = JFactory::getApplication()->input;

			$optionParameter = "com_content";
			$resourceId = $jinput->getInt('content_article');
			$categoryId = $jinput->getInt('content_category');
			$paytoreadmore = $jinput->getInt('paytoreadmore', 0);

			$resourceDesc = JText::_("PAYPERDOWNLOADPLUS_CONTENT_PLUGIN_ALL_ARTICLES");
			if($resourceId)
			{
			    $db = JFactory::getDBO();

			    $query = $db->getQuery(true);

			    $query->select($db->quoteName(array('id', 'title')));
			    $query->from($db->quoteName('#__content'));
			    $query->where($db->quoteName('id') . ' = ' . $resourceId);

			    $db->setQuery($query);

			    try {
			        $resource = $db->loadObject();
			        if($resource)
			            $resourceName = $resource->title;
			    } catch (RuntimeException $e) {
			        JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			    }

				$resourceDesc = JText::_("PAYPERDOWNLOADPLUS_CONTENT_PLUGIN_CONTENT_ARTICLE");
			}
			else if($categoryId)
			{
				$resourceId = -1;

				$db = JFactory::getDBO();

				$query = $db->getQuery(true);

				$query->select($db->quoteName(array('id', 'title')));
				$query->from($db->quoteName('#__categories'));
				$query->where($db->quoteName('id') . ' = ' . $categoryId);

				$db->setQuery($query);

				try {
				    $resource = $db->loadObject();
				    if($resource)
				        $resourceName = $resource->title;
				} catch (RuntimeException $e) {
				    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
				}

				$resourceDesc = JText::_("PAYPERDOWNLOADPLUS_CONTENT_PLUGIN_CONTENT_CATEGORY");
			}
			else
				$resourceId = -1;
			$resourceName = JText::_("PAYPERDOWNLOADPLUS_CONTENT_PLUGIN_ALL_ARTICLES");

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

		$query = $db->getQuery(true);

		$query->select($db->quoteName('catid'));
		$query->from($db->quoteName('#__content'));
		$query->where($db->quoteName('id') . ' = ' . (int)$id);

		$db->setQuery($query);

		$parentCategories = array();

		try {
		    $category = $db->loadResult();

		    while($category)
		    {
		        $parentCategories[] = $category;

		        $query->clear();

		        $query->select($db->quoteName('parent_id'));
		        $query->from($db->quoteName('#__categories'));
		        $query->where($db->quoteName('id') . ' = ' . (int)$category);

		        $db->setQuery($query);

		        $category = $db->loadResult();
		    }

		} catch (RuntimeException $e) {
		    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $parentCategories;
	}

	function onValidateAccess($option, $resources, &$allowAccess, &$requiredLicenses, &$resourcesId)
	{
	    $jinput = JFactory::getApplication()->input;

	    $download = $jinput->getInt('id', 0);
	    if($download == 0)
	        return;

		if($option == 'com_content' && $jinput->get('view') == 'article')
		{
			$requiredLicenses = array();
			$resourcesId = array();

			//Check articles licenses
			foreach($resources as $resource)
			{
			    if ($resource->resource_type === 'Content') {
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
			}
			$parentCategories = $this->getParentCategories($download);
			//Check category licenses
			foreach($resources as $resource)
			{
			    if ($resource->resource_type === 'Content') {
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
			}

			//Check all articles license
			foreach($resources as $resource)
			{
			    if ($resource->resource_type === 'Content') {
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
	}

	function onAjaxCall($plugin, &$output)
	{
		if($plugin == "content")
		{
		    $jinput = JFactory::getApplication()->input;

		    $t = $jinput->getRaw('t');
		    $x = $jinput->getRaw('x');

			$db = JFactory::getDBO();
			$query = $db->getQuery(true);

			if($t == 's')
			{
				$query->select($db->quoteName(array('id', 'title', 'catid')));
				$query->from($db->quoteName('#__content'));
				$query->where($db->quoteName('title') . ' LIKE ' . $db->quote('%' . $x . '%'));
				$query->setLimit('10');

				$db->setQuery($query);

				$output = '';
				try {
				    $articles = $db->loadObjectList();
				    $output = '<<' . count($articles);
				    foreach($articles as $article)
				    {
				        $output .= '>' . htmlspecialchars($article->id) . "<" . htmlspecialchars($article->title) . "<" . htmlspecialchars($article->catid);
				    }
				    $output .= '>>';
				} catch (RuntimeException $e) {
				    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
				}
			}
			else if($t == 'a')
			{
			    $query->select($db->quoteName(array('id', 'title')));
			    $query->from($db->quoteName('#__content'));
			    $query->where($db->quoteName('catid') . ' = ' . (int)$x);
			    $query->setLimit('10');

			    $db->setQuery($query);

			    $output = '';
			    try {
			        $articles = $db->loadObjectList();
			        $output = '<<' . count($articles);
			        foreach($articles as $article)
			        {
			            $output .= '>' . htmlspecialchars($article->id) . "<" . htmlspecialchars($article->title);
			        }
			        $output .= '>>';
			    } catch (RuntimeException $e) {
			        JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			    }
			}
		}
	}

	function getReturnPage($option, &$returnPage)
	{
		/*if($option == "com_content")
		{
			$article = JFactory::getApplication()->input->getInt('id', 0);
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

		$query = $db->getQuery(true);

		$query->select($db->quoteName('catid'));
		$query->from($db->quoteName('#__content'));
		$query->where($db->quoteName('id') . ' = ' . (int)$id);

		$db->setQuery($query);

		$catid = 0;
		try {
		    $catid = (int)$db->loadResult();

		    $contentRouterHelperFile = JPATH_SITE . "/components/com_content/helpers/route.php";
		    if(file_exists($contentRouterHelperFile))
		    {
		        require_once($contentRouterHelperFile);
		        if(class_exists("ContentHelperRoute"))
		        {
		            $link = JRoute::_(ContentHelperRoute::getArticleRoute($id, $catid));
		            // should always work according to PHP.net
		            // http://www.php.net/manual/en/reserved.variables.server.php
		            // 1 - Set to a non-empty value if the script was queried through the HTTPS protocol.
		            // 2 - Note that when using ISAPI with IIS, the value will be "off" if the request was not made through the HTTPS protocol
		            $is_protocol_https = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== "off") ? true : false;
		            if ($is_protocol_https)
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
		} catch (RuntimeException $e) {
		    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
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
		    $itemId = JFactory::getApplication()->input->getInt('id', 0);
		}
	}

}
?>
