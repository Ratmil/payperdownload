<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;

$form_id = "login-form";
$form_name = "com-login";
$pwd_id = "modlgn-passwd";
$pwd_name = "password";
$user_com = "com_users";
$login_task = "user.login";
$registerview = "registration";
?>
<form action="index.php" method="post" name="<?php echo $form_name;?>" id="<?php echo $form_id;?>">
<fieldset class="input">
	<p id="com-form-login-username">
		<label for="username"><?php echo JText::_('PAYPERDOWNLOADPLUS_USERNAME') ?></label><br />
		<input name="username" id="username" type="text" class="inputbox" alt="username" size="18" />
	</p>
	<p id="com-form-login-password">
		<label for="passwd"><?php echo JText::_('PAYPERDOWNLOADPLUS_PASSWORD') ?></label><br />
		<input type="password" id="<?php echo $pwd_id;?>" name="<?php echo $pwd_name;?>" class="inputbox" size="18" alt="password" />
	</p>
	<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
	<p id="com-form-login-remember">
		<label for="remember"><?php echo JText::_('PAYPERDOWNLOADPLUS_REMEMBER_ME') ?></label>
		<input type="checkbox" id="remember" name="remember" class="inputbox" value="yes" alt="Remember Me" />
	</p>
	<?php endif; ?>
	<div id="form-login-submit" class="control-group">
		<div class="controls">
			<button type="submit" tabindex="0" name="Submit" class="btn btn-primary"><?php echo JText::_('PAYPERDOWNLOADPLUS_LOGIN_BUTTON') ?></button>
		</div>
	</div>
</fieldset>

	<input type="hidden" name="option" value="<?php echo $user_com;?>" />
	<input type="hidden" name="task" value="<?php echo $login_task;?>" />
	<input type="hidden" name="return" value="<?php echo base64_encode($this->returnUrl); ?>" />
	<ul>
	<li>
		<a href="<?php echo JURI::base();?>index.php?option=<?php echo $user_com;?>&amp;view=reset">
		<?php echo JText::_("PAYPERDOWNLOADPLUS_FORGOT_YOUR_PASSWORD");?></a>
	</li>
	<li>
		<a href="<?php echo JURI::base();?>index.php?option=<?php echo $user_com;?>&amp;view=remind">
		<?php echo JText::_("PAYPERDOWNLOADPLUS_FORGOT_YOUR_USERNAME");?></a>
	</li>

			<li>
		<a href="<?php echo JURI::base();?>index.php?option=<?php echo $user_com;?>&amp;view=<?php echo $registerview; ?>">
			<?php echo JText::_("PAYPERDOWNLOADPLUS_CREATE_AN_ACCOUNT");?></a>
	</li>
	</ul>

	<?php echo JHTML::_( 'form.token' ); ?>
</form>