function license_select()
{
	var license = document.getElementById('license_id');
	var resource_price = document.getElementById('resource_price');
	var resource_price_currency = document.getElementById('resource_price_currency');
	var download_expiration = document.getElementById('download_expiration');
	var max_download = document.getElementById('max_download');
	var shared0 = document.getElementById('shared0');
	var shared1 = document.getElementById('shared1');
	var aup = document.getElementById('aup');
	if(license.value == '')
	{
		resource_price.disabled = false;
		resource_price_currency.disabled = false;
		download_expiration.disabled = false;
		max_download.disabled = false;
		shared0.disabled = false;
		shared1.disabled = false;
		if(aup != null)
			aup.disabled = false;
	}
	else
	{
		resource_price.disabled = true;
		resource_price_currency.disabled = true;
		download_expiration.disabled = true;
		max_download.disabled = true;
		shared0.disabled = true;
		shared1.disabled = true;
		if(aup != null)
			aup.disabled = true;
		resource_price.value = '';
		download_expiration.value = '';
		max_download.value = '';
		if(aup != null)
			aup.value = '';
	}
}

function validate_price()
{
	var license = document.getElementById('license_id');
	var resource_price = document.getElementById('resource_price');
	var download_expiration = document.getElementById('download_expiration');
	var max_download = document.getElementById('max_download');
	var aup = document.getElementById('aup');
	if(license.value == '')
	{
		var regExp = /\s*\d+(\.\d+)?\s*/;
		if(!regExp.test(resource_price.value))
		{
			alert(invalid_price_text);
			return false;
		}
		regExp = /\s*\d+\s*/;
		if(!regExp.test(download_expiration.value))
		{
			alert(invalid_expiration_text);
			return false;
		}
		if(!regExp.test(max_download.value))
		{
			alert(invalid_maxdownload_text);
			return false;
		}
		if(aup != null && !regExp.test(aup.value))
		{
			alert(invalid_aup_text);
			return false;
		}
	}
	return true;
}

function validatetask(pressbutton)
{
	if(pressbutton == 'acceptnewresource')
		return validate_price();
	return true;
}