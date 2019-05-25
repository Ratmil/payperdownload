var myAjaxTrigger = new createAJAX(); 

function k2_plugin_clearText(text)
{
	var s = text.indexOf('<<');
	var e = text.indexOf('>>');
	if(e > s)
		return text.substr(s + 2, e - s - 2);
	else
		return '';
}

function k2_plugin_category_change_result(responseText)
{
	var k2_category = document.getElementById('k2_category'); 
	var k2_file = document.getElementById('k2_file');
	var k2_attachement = document.getElementById('k2_attachement');
	while(k2_file.length > 1)
		k2_file.remove(1);
	while(k2_attachement.length > 1)
		k2_attachement.remove(1);
	var text = k2_plugin_clearText(responseText);
	var elements = text.split('>');
	for(var i = 1; i < elements.length; i++)
	{
		var fields = elements[i].split('<');
		var option = document.createElement('option');
		k2_file.options.add(option);
		option.value = fields[0];
		option.innerHTML = fields[1];
	}
	
	k2_category.disabled = false;
	k2_file.disabled = false;
	k2_attachement.disabled = false;
}

function k2_plugin_file_change_result(responseText)
{
	var k2_category = document.getElementById('k2_category'); 
	var k2_file = document.getElementById('k2_file');
	var k2_attachement = document.getElementById('k2_attachement');
	while(k2_attachement.length > 1)
		k2_attachement.remove(1);
	var text = k2_plugin_clearText(responseText);
	var elements = text.split('>');
	for(var i = 1; i < elements.length; i++)
	{
		var fields = elements[i].split('<');
		var option = document.createElement('option');
		k2_attachement.options.add(option);
		option.value = fields[0];
		option.innerHTML = fields[1];
	}
	
	k2_category.disabled = false;
	k2_file.disabled = false;
	k2_attachement.disabled = false;
}

function k2_plugin_clear_combos()
{	
	var k2_category = document.getElementById('k2_category'); 
	var k2_file = document.getElementById('k2_file');
	var k2_attachement = document.getElementById('k2_attachement');
	while(k2_file.length > 1)
		k2_file.remove(1);
	while(k2_attachement.length > 1)
		k2_attachement.remove(1);
}

function k2_plugin_error(status)
{
	var k2_category = document.getElementById('k2_category'); 
	var k2_file = document.getElementById('k2_file');
	var k2_attachement = document.getElementById('k2_attachement');
	k2_category.disabled = false;
	k2_file.disabled = false;
	k2_attachement = false;
}

function k2_plugin_category_change()
{
	var k2_category = document.getElementById('k2_category'); 
	var k2_file = document.getElementById('k2_file');
	var k2_attachement = document.getElementById('k2_attachement');
	
	if(k2_category.value == '0')
	{
		k2_plugin_clear_combos();
		return;
	}
	
	k2_category.disabled = true;
	k2_file.disabled = true;
	k2_attachement.disabled = true;
	
	myAjaxTrigger.async_call(site_root + '/administrator/index.php', 'option=' + 
		encodeURIComponent(site_option) + '&adminpage=' + 
		encodeURIComponent(site_adminpage) + '&view=' +  
		encodeURIComponent(site_adminpage) + '&task=ajaxCall&format=raw' +
		'&plugin=k2&t=f&x=' + encodeURIComponent(k2_category.value), k2_plugin_category_change_result, k2_plugin_error);
}

function k2_plugin_file_change()
{
	var k2_category = document.getElementById('k2_category'); 
	var k2_file = document.getElementById('k2_file');
	var k2_attachement = document.getElementById('k2_attachement');
	
	if(k2_file.value == '0')
	{
		while(k2_attachement.length > 1)
			k2_attachement.remove(1);
		return;
	}
	
	k2_category.disabled = true;
	k2_file.disabled = true;
	k2_attachement.disabled = true;
	
	myAjaxTrigger.async_call(site_root + '/administrator/index.php', 'option=' + 
		encodeURIComponent(site_option) + '&adminpage=' + 
		encodeURIComponent(site_adminpage) + '&view=' +  
		encodeURIComponent(site_adminpage) + '&task=ajaxCall&format=raw' +
		'&plugin=k2&t=a&x=' + encodeURIComponent(k2_file.value), k2_plugin_file_change_result, k2_plugin_error);
}