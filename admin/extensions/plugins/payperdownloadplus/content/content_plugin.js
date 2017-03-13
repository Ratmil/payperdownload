var myAjaxTrigger = new createAJAX(); 
var content_plugin_selected_article = 0;

function content_plugin_addslashes(text)
{
	return text.replace(/'/g, '\'');
}

function content_plugin_htmlspecialchars(text)
{
	text = text.replace(/&/g, '&amp;');
	text = text.replace(/"/g, '&quot;');
	text = text.replace(/'/g, '&#039;');
	text = text.replace(/</g, '&lt;');
	text = text.replace(/>/g, '&gt;');
	return text;
}

function content_plugin_clearText(text)
{
	var s = text.indexOf('<<');
	var e = text.indexOf('>>');
	if(e > s)
		return text.substr(s + 2, e - s - 2);
	else
		return '';
}

function content_plugin_category_change_result(responseText)
{
	var content_category = document.getElementById('content_category'); 
	var content_article = document.getElementById('content_article');
	var search_text = document.getElementById('search_text');
	var search_button = document.getElementById('search_button');
	
	while(content_article.length > 1)
		content_article.remove(1);
	var text = content_plugin_clearText(responseText);
	var elements = text.split('>');
	for(var i = 1; i < elements.length; i++)
	{
		var fields = elements[i].split('<');
		var option = document.createElement('option');
		content_article.options.add(option);
		option.value = fields[0];
		option.innerHTML = fields[1];
	}
	if(content_plugin_selected_article != 0)
	{
		content_article.value = content_plugin_selected_article;
		content_plugin_selected_article = 0;
	}
	
	content_category.disabled = false;
	content_article.disabled = false;
	search_text.disabled = false;
	search_button.disabled = false;
}

function content_plugin_error(status)
{
	var content_category = document.getElementById('content_category'); 
	var content_article = document.getElementById('content_article');
	var search_text = document.getElementById('search_text');
	var search_button = document.getElementById('search_button');
	content_category.disabled = false;
	content_article.disabled = false;
	search_text.disabled = false;
	search_button.disabled = false;
}

function content_plugin_clear_combos()
{	
	var content_category = document.getElementById('content_category'); 
	var content_article = document.getElementById('content_article');
	while(content_category.length > 1)
		content_category.remove(1);
	while(content_article.length > 1)
		content_article.remove(1);
}

function content_plugin_category_change()
{
	var content_category = document.getElementById('content_category'); 
	var content_article = document.getElementById('content_article');
	var search_text = document.getElementById('search_text');
	var search_button = document.getElementById('search_button');
	
	if(content_category.value == '0')
	{
		while(content_article.length > 1)
			content_article.remove(1);
		return;
	}
	
	content_category.disabled = true;
	content_article.disabled = true;
	search_text.disabled = true;
	search_button.disabled = true;
	
	myAjaxTrigger.async_call(site_root + 'administrator/index.php', 'option=' + 
		encodeURIComponent(site_option) + '&adminpage=' + 
		encodeURIComponent(site_adminpage) + '&task=ajaxCall&format=raw' +
		'&plugin=content&t=a&x=' + encodeURIComponent(content_category.value), content_plugin_category_change_result, content_plugin_error);
}

function content_plugin_search_result(responseText)
{
	var content_category = document.getElementById('content_category'); 
	var content_article = document.getElementById('content_article');
	var search_text = document.getElementById('search_text');
	var search_button = document.getElementById('search_button');  
	
	var text = content_plugin_clearText(responseText);
	var elements = text.split('>');
	var html = '';
	for(var i = 1; i < elements.length; i++)
	{
		var fields = elements[i].split('<');
		html += '<a href="#" onclick="content_plugin_select(\'' + content_plugin_addslashes(fields[0]) + '\', \'' + 
			content_plugin_addslashes(fields[2]) + '\');return false;">' + 
			content_plugin_htmlspecialchars(fields[1]) + '</a><br/>';
	}
	html += '<hr/><a href="#" onclick="content_plugin_cancel();return false;">' + cancel_text + '</a>';
	var div = document.getElementById('search_result');
	var absx = absy = 0;
	var node = search_button;
	while(node != null)
	{
		absx += node.offsetLeft;
		absy += node.offsetTop;
		node = node.offsetParent;
	}
	div.style.left = absx;
	div.style.top = absy;
	div.innerHTML = html;
	div.style.position = 'absolute';
	div.style.visibility = 'visible';
}

function content_plugin_cancel(dataField)
{
	var div = document.getElementById('search_result');
	div.style.position = 'absolute';
	div.style.visibility = 'hidden';
	var content_category = document.getElementById('content_category'); 
	var content_article = document.getElementById('content_article');
	var search_text = document.getElementById('search_text');
	var search_button = document.getElementById('search_button');
	content_category.disabled = false;
	content_article.disabled = false;
	search_text.disabled = false;
	search_button.disabled = false;
}

function content_plugin_search()
{
	var content_category = document.getElementById('content_category'); 
	var content_article = document.getElementById('content_article');
	var search_text = document.getElementById('search_text');
	var search_button = document.getElementById('search_button');  
	content_category.disabled = true;
	content_article.disabled = true;
	search_text.disabled = true;
	search_button.disabled = true;
	
	myAjaxTrigger.async_call(site_root + '/administrator/index.php', 'option=' + 
		encodeURIComponent(site_option) + '&adminpage=' + 
		encodeURIComponent(site_adminpage) + '&task=ajaxCall&format=raw' +
		'&plugin=content&t=s&x=' + encodeURIComponent(search_text.value), content_plugin_search_result, content_plugin_error);
}

function content_plugin_select(id, catid)
{
	content_plugin_selected_article = id;
	var content_category = document.getElementById('content_category'); 
	for(var i = 0; i < content_category.options.length; i++)
	{
		if(content_category.options[i].value == catid)
		{
			content_category.selectedIndex = i;
			break;
		}
	}
	var div = document.getElementById('search_result');
	div.style.position = 'absolute';
	div.style.visibility = 'hidden';
	content_plugin_category_change();
}