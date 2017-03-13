var myAjaxTrigger = new createAJAX(); 

function excombo_addslashes(text)
{
	return text.replace(/'/g, '\'');
}

function excombo_htmlspecialchars(text)
{
	text = text.replace(/&/g, '&amp;');
	text = text.replace(/"/g, '&quot;');
	text = text.replace(/'/g, '&#039;');
	text = text.replace(/</g, '&lt;');
	text = text.replace(/>/g, '&gt;');
	return text;
}

function excombo_search_results(responseText)
{
	var text = excombo_clearText(responseText);
	var elements = text.split('>');
	if(elements.length >= 2 && elements[0] == '1')
	{
		var dataField = elements[1];
		var div_elements = document.getElementById(dataField + '_values');
		var html = '<table style=\"width:100%\" class=\"inputbox\">';
		for(var i = 2; i < elements.length; i++)
		{
			var value_data = elements[i].split('<');
			html += '<tr><td>';
			html += '<a href=\"#\" onclick=\"excombo_select(\'' + 
				excombo_addslashes(dataField) + '\', \'' + 
				excombo_addslashes(value_data[0]) +  '\', \'' + 
				excombo_addslashes(value_data[1]) +
				'\')\">' + value_data[1] + '</a>';
			html += '</td></tr>';
		}
		html += '<tr><td align=\"right\"><a href=\"#\" onclick=\"excombo_cancel(\'' + excombo_addslashes(dataField) + '\')\">' + cancel_text +  '</a></td></tr>';
		html += '</table>';
		var searchTextElement = document.getElementById(dataField + '_search');
		var absx = absy = 0;
		var node = searchTextElement;
		while(node != null)
		{
			absx += node.offsetLeft;
			absy += node.offsetTop;
			node = node.offsetParent;
		}
		div_elements.style.left = absx;
		div_elements.style.top = absy;
		div_elements.innerHTML = html;
		div_elements.style.position = 'absolute';
		div_elements.style.visibility = 'visible';
	}
	else
		alert('Error loading data');
}

function excombo_cancel(dataField)
{
	var div_elements = document.getElementById(dataField + '_values');
	div_elements.style.position = 'absolute';
	div_elements.style.visibility = 'hidden';
	var buttonElement = document.getElementById(dataField + '_btn');
	var searchTextElement = document.getElementById(dataField + '_search');
	buttonElement.disabled = false;
	searchTextElement.disabled = false;
}

function excombo_select(dataField, value, display)
{
	var div_elements = document.getElementById(dataField + '_values');
	div_elements.style.position = 'absolute';
	div_elements.style.visibility = 'hidden';
	var divElement = document.getElementById(dataField + '_show');
	divElement.innerHTML = excombo_htmlspecialchars(display);
	var searchTextElement = document.getElementById(dataField + '_search');
	var element = document.getElementById(dataField);
	element.value = value;
	var buttonElement = document.getElementById(dataField + '_btn');
	buttonElement.disabled = false;
	searchTextElement.disabled = false;
}

function excombo_search_error(status)
{
	alert(status);
}

function excombo_clearText(text)
{
	var s = text.indexOf('<<');
	var e = text.indexOf('>>');
	if(e > s)
		return text.substr(s + 2, e - s - 2);
	else
		return '';
}

function excombo_search(dataField)
{
	var searchTextElement = document.getElementById(dataField + '_search');
	var buttonElement = document.getElementById(dataField + '_btn');
	var searchText = searchTextElement.value;
	searchTextElement.disabled = true;
	buttonElement.disabled = true;
	myAjaxTrigger.async_call(site_root + '/administrator/index.php', 'option=' + 
		encodeURIComponent(site_option) + '&adminpage=' + 
		encodeURIComponent(site_adminpage) + '&task=ajaxCall&format=raw' +
		'&v=' + dataField + '&x=' + encodeURIComponent(searchText), excombo_search_results, excombo_search_error);
}