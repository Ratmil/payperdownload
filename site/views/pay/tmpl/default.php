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
<script type="text/javascript">
var invalid_email_text = '<?php echo JText::_("PAYPERDOWNLOADPLUS_EMAIL_INVALID", true);?>';
</script>
<?php
$paypal_button_lang_folder = JText::_("PAYPERDOWNLOADPLUS_PAYPAL_BUTTON_LANGUAGE_FOLDER");
if($this->usePayPlugin)
{
	JPluginHelper::importPlugin("payperdownloadplus");
	$dispatcher	= JDispatcher::getInstance();
}
if($this->resourceObj)
{
	/*open resource div*/
	?>
	<div class="ppd_resource" >
	<?php
	/**
	Resource
	**/
	if($this->showMessage)
	{
		?>
		<div class="front_message"><?php echo $this->resourceObj->payment_header;?></div>
		<br/>
		<?php
	}
	?>

	<br/>
	<div class="front_title"><?php echo htmlspecialchars($this->resourceObj->resource_name)."&nbsp;&nbsp;&nbsp;";?></div>
	<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_PRICE___4"));?></span>
	<span class="front_price_data"><?php echo htmlspecialchars($this->resourceObj->resource_price) . "&nbsp;" . htmlspecialchars($this->resourceObj->resource_price_currency);?></span>
	<br/>
	<?php
	if($this->tax_percent >= 0.01)
	{
	?>
	<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_TAX"));?></span>
	<span class="front_price_data"><?php echo htmlspecialchars(sprintf("%01.2f", $this->resourceObj->resource_price * $this->tax_percent / 100.0)) . "&nbsp;" . htmlspecialchars($this->resourceObj->resource_price_currency);?></span>
	<br/>
	<?php
	}
	?>
	<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_DOWNLOADS_COUNT"));?></span>
	<span class="front_price_data">
	<?php 
		if($this->resourceObj->max_download > 0)
			echo htmlspecialchars($this->resourceObj->max_download);
		else
			echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_UNLIMITED_DOWNLOADS"));
	?></span>
	<br/>
	
	<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_LICENSE_EXPIRATION_TIME"));?></span>
	<span class="front_price_data"><?php 
		if($this->resourceObj->download_expiration > 0)
			echo htmlspecialchars(JText::sprintf("PAYPERDOWNLOADPLUS_LICENSE_EXPIRATION_TIME_VALUE", $this->resourceObj->download_expiration)); 
		else
			echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_LICENSE_EXPIRATION_LIFE_TIME"));
	?></span>
	<br/>
	
	<br/><br/>
	
	<?php
	{
		if($this->usePaypal)
		{
			if($this->paymentInfo->test_mode)
			{
				if($this->paymentInfo->usesimulator)
					$paypal = "index.php?option=com_payperdownload&amp;task=paypalsim";
				else
					$paypal = "https://www.sandbox.paypal.com/cgi-bin/webscr";
			}
			else
			{
				$paypal = "https://www.paypal.com/cgi-bin/webscr";
			}
		?>
		<form action="index.php" method="post">
		<input type="hidden" name="option" value="com_payperdownload"/>
		<input type="hidden" name="task" value="paypalpay"/>
		<input type="hidden" name="ppd_item_type" value="resource"/>
		<input type="hidden" name="cmd" value="_xclick"/>
		<input type="hidden" name="custom" value="<?php echo htmlspecialchars($this->download_id);?>"/>
		<input type="hidden" name="item_number" value="<?php echo htmlspecialchars($this->resourceObj->resource_license_id); ?>"/>
		<input type="hidden" name="item_name" value="<?php echo htmlspecialchars($this->resourceObj->resource_name); ?>"/>
		<input type="hidden" name="amount" value="<?php echo htmlspecialchars($this->resourceObj->resource_price); ?>"/>
		<input type="hidden" name="currency_code" value="<?php echo htmlspecialchars($this->resourceObj->resource_price_currency); ?>"/>
		<?php
		/*if($this->useDiscountCoupon)
		{
		echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_DISCOUNT_COUPON"));
		?>
		<input type="text" name="coupon_code"/><br/>
		<?php
		}*/
		if($this->tax_percent >= 0.01)
		{
		?>
		<input type="hidden" name="tax" value="<?php echo htmlspecialchars(sprintf("%01.2f", $this->resourceObj->resource_price * $this->tax_percent / 100.0));?>"/>
		<?php
		}
		$params = "&r=" . base64_encode($this->returnUrl);
		?>
		<input type="hidden" name="notify_url" value="<?php echo htmlspecialchars($root . "index.php?option=com_payperdownload&task=confirmres" . $params);?>"/>
		<?php 
			if($this->thankyouUrl){
			?>
			<input type="hidden" name="return" value="<?php echo htmlspecialchars($this->thankyouUrl);?>"/>
			<?php }?>
			<input type="hidden" name="no_note" value="1"/>
			<input type="hidden" name="no_shipping" value="1"/>
			<input type="hidden" name="rm" value="2"/>
			<input type="image" src="http://www.paypal.com/<?php echo $paypal_button_lang_folder;?>/i/btn/btn_paynowCC_LG.gif" name="submit" alt="<?php echo JText::_("PAYPERDOWNLOADPLUS_PAYPAL_BUTTON_ALTERNATE_TEXT");?>"/>
			<img alt="" src="https://www.paypal.com/<?php echo $paypal_button_lang_folder;?>/i/scr/pixel.gif" width="1" height="1"/>
			</form>
			<br/>
			<?php
		}
		if($this->usePayPlugin)
		{
			$dispatcher->trigger('onRenderPaymentForm', array($this->user, null, $this->resourceObj, $this->returnUrl, $this->thankyouUrl));
		}
		if($this->hasLicenses)
		{
			?>
			<hr/>
			<div class="front_message"><?php echo $this->alternate_header;?></div>
			<br/>
			<?php
		}
	}
	?>
	</div>
	<?php
	/*close resource div*/
}
/***
licenses
***/
if($this->hasLicenses)
{

	if($this->showMessage)
	{
		if($this->header)
		{
			echo $this->header;
		}
		else
		{
			if(count($this->licenses) > 1)
				$message = JText::_('PAYPERDOWNLOADPLUS_THE_RESOURCE_YOU_ARE_TRYING_TO_ACCESS_REQUIRES_THE_FOLLOWING_LICENSES');
			else
				$message = JText::_('PAYPERDOWNLOADPLUS_THE_RESOURCE_YOU_ARE_TRYING_TO_ACCESS_REQUIRES_THE_FOLLOWING_LICENSE_3');
			?>

			<div class="front_message"><?php echo htmlspecialchars($message);?></div>
			<br/>
			<?php
		}
	}

$first = true;
foreach($this->licenses as $license)
{
	if(!$license->canRenew)
		continue;
	?>
	<div class="ppd_license" id="div_license_<?php echo $license->license_id;?>">
	<br/>
	<div class="front_title"><?php echo htmlspecialchars($license->license_name)."&nbsp;&nbsp;&nbsp;";?></div>
	<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_PRICE___4"));?></span>
	<span class="front_price_data"><?php echo htmlspecialchars($license->price) . "&nbsp;" . htmlspecialchars($license->currency_code);?></span>
	<br/>
	<?php 
	if($this->alpha_integration == 2 && $license->aup > 0)
	{
	?>
		<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_AUP_PRICE"));?></span>
		<span class="front_price_data"><?php echo htmlspecialchars($license->aup);?></span>
		<br/>
	<?php
	}
	?>
	
	<?php
	if($this->applyDiscount && $license->price - $license->discount_price > 0.01)
	{
	?>
	<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_DISCOUNT_PRICE"));?></span>
	<span class="front_price_data">
	<?php 
		if($license->discount_price > 0)
		{
			echo htmlspecialchars($license->discount_price) . "&nbsp;" . htmlspecialchars($license->currency_code);
		}
		else
		{
			echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_DISCOUNT_FREE"));
		}
		$license->price = $license->discount_price;
   ?></span>
	<br/>
	<?php 
	}
	?>
	<?php
	if($this->tax_percent >= 0.01)
	{
	?>
	<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_TAX"));?></span>
	<span class="front_price_data"><?php echo htmlspecialchars(sprintf("%01.2f", $license->price * $this->tax_percent / 100.0)) . "&nbsp;" . htmlspecialchars($license->currency_code);?></span>
	<br/>
	<?php
	}
	?>
	<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_LICENSE_EXPIRATION_TIME"));?></span>
	<span class="front_price_data"><?php 
		if($license->expiration > 0)
			echo htmlspecialchars(JText::sprintf("PAYPERDOWNLOADPLUS_LICENSE_EXPIRATION_TIME_VALUE", $license->expiration)); 
		else
			echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_LICENSE_EXPIRATION_LIFE_TIME"));
	?></span>
	<br/>
	<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_DOWNLOADS_COUNT"));?></span>
	<span class="front_price_data"><?php 
		if($license->max_download > 0)
			echo htmlspecialchars($license->max_download); 
		else
			echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_UNLIMITED_DOWNLOADS"));
	?></span>
	<br/><br/>
	<?php
	$first = false;
	if($this->showResources && $license->resources && count($license->resources) > 0)
	{
	?>
	<div class="front_title2"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_AVAILABLE_RESOURCES_FOR_THIS_LICENSE_5"));?></div>
	<?php
	}
	?>
	
	<?php
	if($this->showResources)
	{
	?>
		<ul class="front_list">
		<?php
		foreach($license->resources as $resource)
		{
		?>
		<li>
		<?php 
			if($resource->alternate_resource_description)
				echo htmlspecialchars($resource->alternate_resource_description);
			else
				echo htmlspecialchars($resource->resource_description) . " : " . 
					htmlspecialchars($resource->resource_name);?>
		</li>
		<?php
		}
		?>
		</ul>
		<?php
	}
	?>
	
	<?php
		echo $license->description;
	?>
	<?php
	if($license->price <= 0)
	{
	?>
		<form action="index.php">
		<input type="submit" name="getlicensefree" value="<?php echo JText::_("PAYPERDOWNLOADPLUS_DISCOUNT_GET_FREE");?>"/>
		<input type="hidden" name="license_id" value="<?php echo htmlspecialchars($license->license_id);?>"/>
		<input type="hidden" name="option" value="com_payperdownload"/>
		<input type="hidden" name="task" value="getfree"/>
		<input type="hidden" name="returnurl" value="<?php echo base64_encode($this->returnUrl);?>" />
		</form>
	<?php
	}
	else if($this->user->id)
	{
		if($this->usePaypal)
		{
			if($this->paymentInfo->test_mode)
			{
				if($this->paymentInfo->usesimulator)
					$paypal = "index.php?option=com_payperdownload&amp;task=paypalsim";
				else
					$paypal = "https://www.sandbox.paypal.com/cgi-bin/webscr";
			}
			else
			{
				$paypal = "https://www.paypal.com/cgi-bin/webscr";
			}
		?>
		<form action="index.php" method="post">
		<input type="hidden" name="option" value="com_payperdownload"/>
		<input type="hidden" name="task" value="paypalpay"/>
		<input type="hidden" name="ppd_item_type" value="license"/>
		<input type="hidden" name="cmd" value="_xclick"/>
		<input type="hidden" name="custom" value="<?php echo htmlspecialchars($this->user->id); ?>"/>
		<input type="hidden" name="item_number" value="<?php echo htmlspecialchars($license->license_id); ?>"/>
		<input type="hidden" name="item_name" value="<?php echo htmlspecialchars($license->license_name); ?>"/>
		<input type="hidden" name="amount" value="<?php echo htmlspecialchars($license->price); ?>"/>
		<input type="hidden" name="currency_code" value="<?php echo htmlspecialchars($license->currency_code); ?>"/>
		<?php
		if($this->useDiscountCoupon)
		{
		echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_DISCOUNT_COUPON"));
		?>
		<input type="text" name="coupon_code"/><br/>
		<?php
		}
		if($this->tax_percent >= 0.01)
		{
		?>
		<input type="hidden" name="tax" value="<?php echo htmlspecialchars(sprintf("%01.2f", $license->price * $this->tax_percent / 100.0));?>"/>
		<?php
		}
		?>
		<?php
		$params = "";
		?>
		<input type="hidden" name="notify_url" value="<?php echo htmlspecialchars($root . "index.php?option=com_payperdownload&task=confirm" . $params);?>"/>
		<?php 
		$returnParams = "";
		if($this->thankyouUrl){
			if(strstr($this->thankyouUrl, "?") === false)
				$returnParams = "?lid=" . (int)$license->license_id;
			else
				$returnParams = "&lid=" . (int)$license->license_id;
			?>
			<input type="hidden" name="return" value="<?php echo htmlspecialchars($this->thankyouUrl . $returnParams);?>"/>
			<?php }?>
			<input type="hidden" name="no_note" value="1"/>
			<input type="hidden" name="no_shipping" value="1"/>
			<input type="hidden" name="rm" value="2"/>
			<input type="image" src="http://www.paypal.com/<?php echo $paypal_button_lang_folder;?>/i/btn/btn_paynowCC_LG.gif" name="submit" alt="<?php echo JText::_("PAYPERDOWNLOADPLUS_PAYPAL_BUTTON_ALTERNATE_TEXT");?>"/>
			<img alt="" src="https://www.paypal.com/<?php echo $paypal_button_lang_folder;?>/i/scr/pixel.gif" width="1" height="1"/>
			</form>
			<br/>
			<?php
		}
		if($this->usePayPlugin)
		{
			$dispatcher->trigger('onRenderPaymentForm', array($this->user, $license, null, $this->returnUrl, $this->thankyouUrl . $returnParams));
		}
		if($this->alpha_integration == 2 && $license->aup > 0 && $license->aup <= $this->points)//Use Alpha Points to buy
		{
			?>
			<br/>
			<?php
			$link = "index.php?option=com_payperdownload&task=buywithaup&lid=" . 
				(int)$license->license_id . "&return=" . base64_encode($this->returnUrl);
			?>
			<a href="<?php echo $link;?>"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_BUY_WITH_AUP"));?></a><br/>
			<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_YOUR_POINTS"));?></span>
			<span class="front_price_data"><?php echo htmlspecialchars($this->points);?></span>
			<br/>
			<?php
		}
	}
	?>
	</div>
		<?php
		if($this->multipleLicenseView == 0)
		{
		?>
		<hr/>
		<?php
		}
	}
	if(!$this->user->id)
	{
	?>
		<div class="front_message"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_YOU_NEED_TO_LOGIN_TO_BUY_A_LICENSE"));?></div>
	<?php
		if($this->show_login)
		{
			echo "<hr/>";
			require_once(JPATH_COMPONENT . '/views/pay/tmpl/login.php');
		}
		if($this->useQuickRegister)
		{
			echo "<hr/>";
			require_once(JPATH_COMPONENT . '/views/pay/tmpl/register.php');
		}
	}
}
?>
