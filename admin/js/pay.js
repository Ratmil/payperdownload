function select_license_to_pay()
{
	var sel_licenses = document.getElementById('sel_licenses');
	if(sel_licenses.value != '0')
	{
		var i;
		for(i = 1; i < sel_licenses.options.length; i++)
		{
			var div_id = 'div_license_' + sel_licenses.options[i].value;
			var div = document.getElementById(div_id);
			div.style.visibility = 'hidden';
			div.style.position = 'absolute';
		}
		var sel_div_id =  'div_license_' + sel_licenses.value;
		var sel_div = document.getElementById(sel_div_id);
		sel_div.style.visibility = 'visible';
		sel_div.style.position = 'static';
	}
}

function showLicenseById(license_id)
{
	var hid_licenses = document.getElementById('licenses_count');
	var i;
	for(i = 0; i < hid_licenses.value; i++)
	{
		var hid_id = 'license_' + i;
		var hid = document.getElementById(hid_id);
		var div_id = 'div_license_' + hid.value;
		var div = document.getElementById(div_id);
		div.style.visibility = 'hidden';
		div.style.position = 'absolute';
	}
	var sel_div_id =  'div_license_' + license_id;
	var sel_div = document.getElementById(sel_div_id);
	sel_div.style.visibility = 'visible';
	sel_div.style.position = 'static';
}

function validateEmailToPay(email_id, invalid_email_text)
{
	var email = document.getElementById(email_id);
	if(email == null)
		return true;
	var regExp = /\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/;
	if(regExp.test(email.value))
		return true;
	else
	{
		alert(invalid_email_text);
		return false;
	}
}