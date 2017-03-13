<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
$root = JURI::root();
?>

<?php
if($this->askEmail)
{
?>

<script type="text/javascript">
var myAjaxTrigger = new createAJAX(); 
function sendLink()
{
	var site_root = '<?php echo addslashes($root);?>';
	var site_option = '<?php echo addslashes(JRequest::getCmd('option'));?>';
	var access = '<?php echo addslashes($this->accessCode);?>';
	var email = document.getElementById('email');
	if(validateEmailToPay(email.value))
	{
		var sendlinkbutton = document.getElementById('sendlinkbutton');
		sendlinkbutton.disabled = true;
		email.disabled = true;
		myAjaxTrigger.async_call(site_root + '/index.php', 'option=' + 
			encodeURIComponent(site_option) + '&task=sendLink&format=raw' +
			'&access=' + encodeURIComponent(access) + 
			'&m=' + encodeURIComponent(email.value), sendLinkOK, sendLinkError);
	}
	return false;
}

function sendLinkOK(text)
{
	var email = document.getElementById('email');
	var sendlinkbutton = document.getElementById('sendlinkbutton');
	sendlinkbutton.disabled = false;
	email.disabled = false;
	var errorMsg = '<?php echo JText::_("PAYPERDOWNLOADPLUS_ERROR_SENDING_LINK", true);?>';
	var sendOkMsg = '<?php echo JText::_("PAYPERDOWNLOADPLUS_LINK_SENT", true);?>';
	var s = text.indexOf('<<');
	var e = text.indexOf('>>');
	if(e > s)
		text = text.substr(s + 2, e - s - 2);
	if(text == '1')
		alert(sendOkMsg);
	else
		alert(errorMsg);
}

function sendLinkError()
{
	var email = document.getElementById('email');
	var sendlinkbutton = document.getElementById('sendlinkbutton');
	sendlinkbutton.disabled = false;
	email.disabled = false;
	var errorMsg = '<?php echo JText::_("PAYPERDOWNLOADPLUS_ERROR_SENDING_LINK", true);?>';
	alert(errorMsg);
}

function validateEmailToPay(email)
{
	var regExp = /^\s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*$/;
	if(regExp.test(email))
		return true;
	else
	{
		var errorMsg = '<?php echo JText::_("PAYPERDOWNLOADPLUS_INVALID_EMAIL_FOR_SEND_LINK", true);?>';
		alert(errorMsg);
		return false;
	}
}

</script>

<?php
}
?>
<?php echo $this->thank_you;?>
<?php
	if($this->askEmail)
	{
	?>
	<br/>
	<div class="front_title2"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_ASKEMAIL_HEADER"));?></div>
	<div class="front_title2"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_ASKEMAIL_HEADER2"));?></div>
	<div class="front_input_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_EMAIL_ADDRESS"));?></div>
	<br/>
	<input type="text" id="email" size="30"/>
	<input type="button" id="sendlinkbutton"
		onclick="return sendLink();"
		value="<?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_SEND_EMAIL"));?>"/>
	<?php
	}
?>