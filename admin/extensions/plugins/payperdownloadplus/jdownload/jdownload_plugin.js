var myAjaxTrigger = new createAJAX(); 

function jdownload_plugin_clearText(text)
{
	var s = text.indexOf('<<');
	var e = text.indexOf('>>');
	if(e > s)
		return text.substr(s + 2, e - s - 2);
	else
		return '';
}

function jdownload_plugin_category_change_result(responseText)
{
	var jdownload_category = document.getElementById('jdownload_category'); 
	var jdownload_file = document.getElementById('jdownload_file');
	while(jdownload_file.length > 1)
		jdownload_file.remove(1);
	var text = jdownload_plugin_clearText(responseText);
	var elements = text.split('>');
	for(var i = 1; i < elements.length; i++)
	{
		var fields = elements[i].split('<');
		var option = document.createElement('option');
		jdownload_file.options.add(option);
		option.value = fields[0];
		option.innerHTML = fields[1];
	}
	
	jdownload_category.disabled = false;
	jdownload_file.disabled = false;
}

function jdownload_plugin_error(status)
{
	var jdownload_category = document.getElementById('jdownload_category'); 
	var jdownload_file = document.getElementById('jdownload_file');
	jdownload_category.disabled = false;
	jdownload_file.disabled = false;
}

function jdownload_plugin_category_change()
{
	var jdownload_category = document.getElementById('jdownload_category'); 
	var jdownload_file = document.getElementById('jdownload_file');
	
	if(jdownload_category.value == '0')
	{
		while(jdownload_file.length > 1)
			jdownload_file.remove(1);
		return;
	}
	
	jdownload_category.disabled = true;
	jdownload_file.disabled = true;
	
	myAjaxTrigger.async_call(site_root + 'administrator/index.php', 'option=' + 
		encodeURIComponent(site_option) + '&adminpage=' + 
		encodeURIComponent(site_adminpage) + '&view=' +
		encodeURIComponent(site_adminpage) + '&task=ajaxCall&format=raw' +
		'&plugin=jdownload&x=' + encodeURIComponent(jdownload_category.value), jdownload_plugin_category_change_result, jdownload_plugin_error);
}