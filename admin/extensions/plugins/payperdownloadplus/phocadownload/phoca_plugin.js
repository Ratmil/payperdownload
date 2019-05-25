var myAjaxTrigger = new createAJAX(); 
var phocadownload_plugin_selected_article = 0;

function phocadownload_plugin_addslashes(text)
{
	return text.replace(/'/g, '\'');
}

function phocadownload_plugin_htmlspecialchars(text)
{
	text = text.replace(/&/g, '&amp;');
	text = text.replace(/"/g, '&quot;');
	text = text.replace(/'/g, '&#039;');
	text = text.replace(/</g, '&lt;');
	text = text.replace(/>/g, '&gt;');
	return text;
}

function phocadownload_plugin_clearText(text)
{
	var s = text.indexOf('<<');
	var e = text.indexOf('>>');
	if(e > s)
		return text.substr(s + 2, e - s - 2);
	else
		return '';
}

function phocadownload_plugin_category_change_result(responseText)
{
	var phocadownload_category = document.getElementById('phocadownload_category'); 
	var phocadownload_file = document.getElementById('phocadownload_file');
	var search_text = document.getElementById('search_text');
	var search_button = document.getElementById('search_button'); 
	while(phocadownload_file.length > 1)
		phocadownload_file.remove(1);
	var text = phocadownload_plugin_clearText(responseText);
	var elements = text.split('>');
	for(var i = 1; i < elements.length; i++)
	{
		var fields = elements[i].split('<');
		var option = document.createElement('option');
		phocadownload_file.options.add(option);
		option.value = fields[0];
		option.innerHTML = fields[1];
	}
	
	if(phocadownload_plugin_selected_article != 0)
	{
		phocadownload_file.value = phocadownload_plugin_selected_article;
		phocadownload_plugin_selected_article = 0;
	}
	
	phocadownload_category.disabled = false;
	phocadownload_file.disabled = false;
	search_text.disabled = false;
	search_button.disabled = false;
}

function phocadownload_plugin_error(status)
{
	var phocadownload_category = document.getElementById('phocadownload_category'); 
	var phocadownload_file = document.getElementById('phocadownload_file');
	var search_text = document.getElementById('search_text');
	var search_button = document.getElementById('search_button');  
	phocadownload_category.disabled = false;
	phocadownload_file.disabled = false;
	search_text.disabled = false;
	search_button.disabled = false;
}

function phocadownload_plugin_clear_combos()
{	
	var phocadownload_category = document.getElementById('phocadownload_category'); 
	var phocadownload_file = document.getElementById('phocadownload_file');
	while(phocadownload_category.length > 1)
		phocadownload_category.remove(1);
	while(phocadownload_file.length > 1)
		phocadownload_file.remove(1);
}

function phocadownload_plugin_category_change()
{
	var phocadownload_category = document.getElementById('phocadownload_category'); 
	var phocadownload_file = document.getElementById('phocadownload_file');
	var search_text = document.getElementById('search_text');
	var search_button = document.getElementById('search_button');  
	
	if(phocadownload_category.value == '0')
	{
		while(phocadownload_file.length > 1)
			phocadownload_file.remove(1);
		return;
	}
	
	phocadownload_category.disabled = true;
	phocadownload_file.disabled = true;
	search_text.disabled = true;
	search_button.disabled = true;
	
	myAjaxTrigger.async_call(site_root + 'administrator/index.php', 'option=' + 
		encodeURIComponent(site_option) + '&adminpage=' + 
		encodeURIComponent(site_adminpage) + '&view=' +  
		encodeURIComponent(site_adminpage) + '&task=ajaxCall&format=raw' +
		'&plugin=phocadownload&t=a&x=' + encodeURIComponent(phocadownload_category.value), 
			phocadownload_plugin_category_change_result, phocadownload_plugin_error);
}

function phocadownload_plugin_search_result(responseText)
{
	var phocadownload_category = document.getElementById('phocadownload_category'); 
	var phocadownload_file = document.getElementById('phocadownload_file');
	var search_text = document.getElementById('search_text');
	var search_button = document.getElementById('search_button');  
	
	var text = phocadownload_plugin_clearText(responseText);
	var elements = text.split('>');
	var html = '';
	for(var i = 1; i < elements.length; i++)
	{
		var fields = elements[i].split('<');
		html += '<a href="#" onclick="phocadownload_plugin_select(\'' + phocadownload_plugin_addslashes(fields[0]) + '\', \'' + 
			phocadownload_plugin_addslashes(fields[2]) + '\');return false;">' + 
			phocadownload_plugin_htmlspecialchars(fields[1]) + '</a><br/>';
	}
	html += '<hr/><a href="#" onclick="phocadownload_plugin_cancel();return false;">' + cancel_text + '</a>';
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

function phocadownload_plugin_cancel(dataField)
{
	var div = document.getElementById('search_result');
	div.style.position = 'absolute';
	div.style.visibility = 'hidden';
	var phocadownload_category = document.getElementById('phocadownload_category'); 
	var phocadownload_file = document.getElementById('phocadownload_file');
	var search_text = document.getElementById('search_text');
	var search_button = document.getElementById('search_button');
	phocadownload_category.disabled = false;
	phocadownload_file.disabled = false;
	search_text.disabled = false;
	search_button.disabled = false;
}

function phocadownload_plugin_search()
{
	var phocadownload_category = document.getElementById('phocadownload_category'); 
	var phocadownload_file = document.getElementById('phocadownload_file');
	var search_text = document.getElementById('search_text');
	var search_button = document.getElementById('search_button');  
	phocadownload_category.disabled = true;
	phocadownload_file.disabled = true;
	search_text.disabled = true;
	search_button.disabled = true;
	
	myAjaxTrigger.async_call(site_root + '/administrator/index.php', 'option=' + 
		encodeURIComponent(site_option) + '&adminpage=' + 
		encodeURIComponent(site_adminpage) + '&view=' + 
		encodeURIComponent(site_adminpage) + '&task=ajaxCall&format=raw' +
		'&plugin=phocadownload&t=s&x=' + encodeURIComponent(search_text.value), phocadownload_plugin_search_result, phocadownload_plugin_error);
}

function phocadownload_plugin_select(id, catid)
{
	phocadownload_plugin_selected_article = id;
	var phocadownload_category = document.getElementById('phocadownload_category'); 
	for(var i = 0; i < phocadownload_category.options.length; i++)
	{
		if(phocadownload_category.options[i].value == catid)
		{
			phocadownload_category.selectedIndex = i;
			break;
		}
	}
	var div = document.getElementById('search_result');
	div.style.position = 'absolute';
	div.style.visibility = 'hidden';
	phocadownload_plugin_category_change();
}