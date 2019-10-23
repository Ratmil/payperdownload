<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;

$root = JURI::root();

if ($this->usePayPlugin) {
    JPluginHelper::importPlugin("payperdownloadplus");
    $dispatcher	= JDispatcher::getInstance();
}

$paypal_button_lang_folder = JText::_("PAYPERDOWNLOADPLUS_PAYPAL_BUTTON_LANGUAGE_FOLDER");
?>
<script type="text/javascript">
	var invalid_email_text = '<?php echo JText::_("PAYPERDOWNLOADPLUS_EMAIL_INVALID", true);?>';
</script>
<div class="pay_license<?php echo $this->pageclass_sfx ? ' '.$this->pageclass_sfx : ''; ?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
    	<div class="page-header">
    		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    	</div>
    <?php endif; ?>

    <?php // open resource div ?>
    <?php if ($this->resourceObj) : ?>

    	<div class="ppd_resource resource">

        	<?php if ($this->showMessage) : ?>
        		<div class="front_message"><?php echo $this->resourceObj->payment_header;?></div>
        	<?php endif; ?>

    		<div class="front_title resource_name"><?php echo htmlspecialchars($this->resourceObj->resource_name) ?></div>

    		<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_PRICE___4"));?></span>
    		<span class="front_price_data"><?php echo htmlspecialchars($this->resourceObj->resource_price) . "&nbsp;" . htmlspecialchars($this->resourceObj->resource_price_currency);?></span>
    		<br />

        	<?php if ($this->tax_percent >= 0.01) : ?>
        		<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_TAX"));?></span>
        		<span class="front_price_data"><?php echo htmlspecialchars(sprintf("%01.2f", $this->resourceObj->resource_price * $this->tax_percent / 100.0)) . "&nbsp;" . htmlspecialchars($this->resourceObj->resource_price_currency);?></span>
        		<br />
        	<?php endif; ?>

    		<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_DOWNLOADS_COUNT"));?></span>
    		<span class="front_price_data">
    			<?php if ($this->resourceObj->max_download > 0) : ?>
    				<?php echo htmlspecialchars($this->resourceObj->max_download); ?>
    			<?php else : ?>
    				<?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_UNLIMITED_DOWNLOADS")); ?>
    			<?php endif; ?>
    		</span>
    		<br />

    		<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_LICENSE_EXPIRATION_TIME"));?></span>
    		<span class="front_price_data">
    			<?php if ($this->resourceObj->download_expiration > 0) : ?>
    				<?php echo htmlspecialchars(JText::sprintf("PAYPERDOWNLOADPLUS_LICENSE_EXPIRATION_TIME_VALUE", $this->resourceObj->download_expiration)); ?>
    			<?php else : ?>
    				<?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_LICENSE_EXPIRATION_LIFE_TIME")); ?>
    			<?php endif; ?>
    		</span>
    		<br />

    	    <?php if ($this->usePaypal) : ?>
    			<?php if ($this->paymentInfo->test_mode) : ?>
    				<?php if ($this->paymentInfo->usesimulator) : ?>
    					<?php $paypal = "index.php?option=com_payperdownload&amp;task=paypalsim"; ?>
    				<?php else : ?>
    					<?php $paypal = "https://www.sandbox.paypal.com/cgi-bin/webscr"; ?>
    				<?php endif; ?>
    			<?php else : ?>
    				<?php $paypal = "https://www.paypal.com/cgi-bin/webscr"; ?>
    			<?php endif; ?>

        		<form action="index.php?option=com_payperdownload" method="post">
            		<input type="hidden" name="task" value="paypalpay"/>
            		<input type="hidden" name="ppd_item_type" value="resource"/>
            		<input type="hidden" name="cmd" value="_xclick"/>
            		<input type="hidden" name="custom" value="<?php echo htmlspecialchars($this->download_id);?>"/>
            		<input type="hidden" name="item_number" value="<?php echo htmlspecialchars($this->resourceObj->resource_license_id); ?>"/>
            		<input type="hidden" name="item_name" value="<?php echo htmlspecialchars($this->resourceObj->resource_name); ?>"/>
            		<input type="hidden" name="amount" value="<?php echo htmlspecialchars($this->resourceObj->resource_price); ?>"/>
            		<input type="hidden" name="currency_code" value="<?php echo htmlspecialchars($this->resourceObj->resource_price_currency); ?>"/>

            		<?php if ($this->tax_percent >= 0.01) : ?>
            			<input type="hidden" name="tax" value="<?php echo htmlspecialchars(sprintf("%01.2f", $this->resourceObj->resource_price * $this->tax_percent / 100.0));?>"/>
            		<?php endif; ?>

        			<?php $params = "&r=" . base64_encode($this->returnUrl); ?>

        			<input type="hidden" name="notify_url" value="<?php echo htmlspecialchars($root . "index.php?option=com_payperdownload&task=confirmres" . $params);?>"/>

            		<?php if ($this->thankyouUrl) : ?>
            			<input type="hidden" name="return" value="<?php echo htmlspecialchars($this->thankyouUrl);?>"/>
            		<?php endif; ?>

        			<input type="hidden" name="no_note" value="1"/>
        			<input type="hidden" name="no_shipping" value="1"/>
        			<input type="hidden" name="rm" value="2"/>
        			<input type="image" src="https://www.paypal.com/<?php echo $paypal_button_lang_folder;?>/i/btn/btn_paynowCC_LG.gif" name="submit" alt="<?php echo JText::_("PAYPERDOWNLOADPLUS_PAYPAL_BUTTON_ALTERNATE_TEXT");?>"/>
        			<img alt="" src="https://www.paypal.com/<?php echo $paypal_button_lang_folder;?>/i/scr/pixel.gif" width="1" height="1"/>
    			</form>
    		<?php endif; ?>

    		<?php if ($this->usePayPlugin) : ?>
    			<?php $dispatcher->trigger('onRenderPaymentForm', array($this->user, null, $this->resourceObj, $this->returnUrl, $this->thankyouUrl)); ?>
    		<?php endif; ?>

    		<?php if ($this->hasLicenses) : ?>
    			<hr/>
    			<div class="front_message"><?php echo $this->alternate_header;?></div>
    		<?php endif; ?>

    	</div>
    <?php endif; // close resource div ?>

    <?php // licenses ?>
    <?php if ($this->hasLicenses) : ?>

    	<?php if ($this->showMessage) : ?>
    		<?php if ($this->header) : ?>
    			<div class="front_message"><?php echo $this->header; ?></div>
    		<?php else : ?>
    			<?php if (count($this->licenses) > 1) : ?>
    				<?php $message = JText::_('PAYPERDOWNLOADPLUS_THE_RESOURCE_YOU_ARE_TRYING_TO_ACCESS_REQUIRES_THE_FOLLOWING_LICENSES'); ?>
    			<?php else : ?>
    				<?php $message = JText::_('PAYPERDOWNLOADPLUS_THE_RESOURCE_YOU_ARE_TRYING_TO_ACCESS_REQUIRES_THE_FOLLOWING_LICENSE_3'); ?>
    			<?php endif; ?>
    			<div class="front_message"><?php echo htmlspecialchars($message);?></div>
    		<?php endif; ?>
    	<?php endif; ?>

		<div class="licenses">
        	<?php foreach ($this->licenses as $i => $license) : ?>

        		<?php if (!$license->canRenew) : ?>
        			<?php continue; ?>
        		<?php endif; ?>

        		<div class="ppd_license license" id="div_license_<?php echo $license->license_id; ?>">

					<div class="front_title license_title">
                		<?php if (isset($license->image) && $license->image) : ?>
            				<div class="license_image">
        						<img src="<?php echo htmlspecialchars($license->image, ENT_COMPAT, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($license->license_name, ENT_COMPAT, 'UTF-8'); ?>" />
        					</div>
            			<?php endif; ?>
                		<div class="license_name"><?php echo htmlspecialchars($license->license_name); ?></div>
                	</div>

            		<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_PRICE___4"));?></span>
            		<span class="front_price_data"><?php echo htmlspecialchars($license->price) . "&nbsp;" . htmlspecialchars($license->currency_code);?></span>
            		<br />

            		<?php if ($this->alpha_integration == 2 && $license->aup > 0) : ?>
            			<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_AUP_PRICE"));?></span>
            			<span class="front_price_data"><?php echo htmlspecialchars($license->aup);?></span>
            			<br />
            		<?php endif; ?>

                	<?php if ($this->applyDiscount && $license->price - $license->discount_price > 0.01) : ?>
                    	<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_DISCOUNT_PRICE"));?></span>
                    	<span class="front_price_data">
                    		<?php if ($license->discount_price > 0) : ?>
                    			<?php echo htmlspecialchars($license->discount_price) . "&nbsp;" . htmlspecialchars($license->currency_code); ?>
                    		<?php else : ?>
                    			<?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_DISCOUNT_FREE")); ?>
                    		<?php endif; ?>
                    		<?php $license->price = $license->discount_price; ?>
                    	</span>
                    	<br />
                	<?php endif; ?>

                	<?php if ($this->tax_percent >= 0.01) : ?>
                		<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_TAX"));?></span>
                		<span class="front_price_data"><?php echo htmlspecialchars(sprintf("%01.2f", $license->price * $this->tax_percent / 100.0)) . "&nbsp;" . htmlspecialchars($license->currency_code);?></span>
                		<br />
                	<?php endif; ?>

            		<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_LICENSE_EXPIRATION_TIME"));?></span>
            		<span class="front_price_data">
            			<?php if ($license->expiration > 0) : ?>
            				<?php echo htmlspecialchars(JText::sprintf("PAYPERDOWNLOADPLUS_LICENSE_EXPIRATION_TIME_VALUE", $license->expiration)); ?>
            			<?php else : ?>
            				<?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_LICENSE_EXPIRATION_LIFE_TIME")); ?>
            			<?php endif; ?>
            		</span>
            		<br />

            		<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_DOWNLOADS_COUNT"));?></span>
            		<span class="front_price_data">
            			<?php if ($license->max_download > 0) : ?>
            				<?php echo htmlspecialchars($license->max_download); ?>
            			<?php else : ?>
            				<?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_UNLIMITED_DOWNLOADS")); ?>
            			<?php endif; ?>
            		</span>
            		<br />

                	<?php if (isset($license->resources) && count($license->resources) > 0) : ?>
	           			<dl class="resources">
            				<dt><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_AVAILABLE_RESOURCES_FOR_THIS_LICENSE_5")); ?></dt>
                    		<?php foreach ($license->resources as $resource) : ?>
                        		<dd class="resource">
                        			<?php if ($resource->alternate_resource_description) : ?>
                        				<?php echo htmlspecialchars($resource->alternate_resource_description); ?>
                        			<?php else : ?>
                        				<?php echo htmlspecialchars($resource->resource_description) . " : " . htmlspecialchars($resource->resource_name); ?>
                        			<?php endif; ?>
                        		</dd>
                    		<?php endforeach; ?>
                		</dl>
            		<?php endif; ?>

            		<div class="license_description"><?php echo $license->description; ?></div>

                	<?php if ($license->price <= 0) : ?>
                		<form action="index.php?option=com_payperdownload">
                    		<input type="hidden" name="task" value="getfree"/>
                    		<input type="hidden" name="license_id" value="<?php echo htmlspecialchars($license->license_id);?>"/>
                    		<input type="hidden" name="returnurl" value="<?php echo base64_encode($this->returnUrl);?>" />
                    		<input type="submit" name="getlicensefree" value="<?php echo JText::_("PAYPERDOWNLOADPLUS_DISCOUNT_GET_FREE");?>"/>
                		</form>
                	<?php elseif ($this->user->id) : ?>
                	    <?php if ($this->usePaypal) : ?>
                		    <?php if ($this->paymentInfo->test_mode) : ?>
                			    <?php if ($this->paymentInfo->usesimulator) : ?>
                					<?php $paypal = "index.php?option=com_payperdownload&amp;task=paypalsim"; ?>
                				<?php else : ?>
                					<?php $paypal = "https://www.sandbox.paypal.com/cgi-bin/webscr"; ?>
                				<?php endif; ?>
                			<?php else : ?>
                				<?php $paypal = "https://www.paypal.com/cgi-bin/webscr"; ?>
                			<?php endif; ?>

                    		<form action="index.php?option=com_payperdownload" method="post">
                        		<input type="hidden" name="task" value="paypalpay"/>
                        		<input type="hidden" name="ppd_item_type" value="license"/>
                        		<input type="hidden" name="cmd" value="_xclick"/>
                        		<input type="hidden" name="custom" value="<?php echo htmlspecialchars($this->user->id); ?>"/>
                        		<input type="hidden" name="item_number" value="<?php echo htmlspecialchars($license->license_id); ?>"/>
                        		<input type="hidden" name="item_name" value="<?php echo htmlspecialchars($license->license_name); ?>"/>
                        		<input type="hidden" name="amount" value="<?php echo htmlspecialchars($license->price); ?>"/>
                        		<input type="hidden" name="currency_code" value="<?php echo htmlspecialchars($license->currency_code); ?>"/>

                        		<?php if ($this->useDiscountCoupon) : ?>
                        			<?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_DISCOUNT_COUPON")); ?>
                        			<input type="text" name="coupon_code"/><br />
                        		<?php endif; ?>

                        		<?php if ($this->tax_percent >= 0.01) : ?>
                        			<input type="hidden" name="tax" value="<?php echo htmlspecialchars(sprintf("%01.2f", $license->price * $this->tax_percent / 100.0));?>"/>
                        		<?php endif; ?>

                				<?php $params = ''; ?>

                				<input type="hidden" name="notify_url" value="<?php echo htmlspecialchars($root . "index.php?option=com_payperdownload&task=confirm" . $params);?>"/>

                				<?php $returnParams = ''; ?>

                    			<?php if ($this->thankyouUrl) : ?>
                        			<?php if (strstr($this->thankyouUrl, "?") === false) : ?>
                        				<?php $returnParams = "?lid=" . (int)$license->license_id; ?>
                        			<?php else : ?>
                        				<?php $returnParams = "&lid=" . (int)$license->license_id; ?>
                        			<?php endif; ?>
                        			<input type="hidden" name="return" value="<?php echo htmlspecialchars($this->thankyouUrl . $returnParams);?>"/>
                    			<?php endif; ?>

                    			<input type="hidden" name="no_note" value="1"/>
                    			<input type="hidden" name="no_shipping" value="1"/>
                    			<input type="hidden" name="rm" value="2"/>
                    			<input type="image" src="https://www.paypal.com/<?php echo $paypal_button_lang_folder;?>/i/btn/btn_paynowCC_LG.gif" name="submit" alt="<?php echo JText::_("PAYPERDOWNLOADPLUS_PAYPAL_BUTTON_ALTERNATE_TEXT");?>"/>
                    			<img alt="" src="https://www.paypal.com/<?php echo $paypal_button_lang_folder;?>/i/scr/pixel.gif" width="1" height="1"/>
                			</form>
                		<?php endif; ?>

                		<?php if ($this->usePayPlugin) : ?>
                			<?php $dispatcher->trigger('onRenderPaymentForm', array($this->user, $license, null, $this->returnUrl, $this->thankyouUrl)); ?>
                		<?php endif; ?>

                		<?php if ($this->alpha_integration == 2 && $license->aup > 0 && $license->aup <= $this->points) : ?>
                			<?php $link = "index.php?option=com_payperdownload&task=buywithaup&lid=" . (int)$license->license_id . "&return=" . base64_encode($this->returnUrl); ?>
                			<br />
                			<a href="<?php echo $link;?>"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_BUY_WITH_AUP"));?></a>
                			<br />
                			<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_YOUR_POINTS"));?></span>
                			<span class="front_price_data"><?php echo htmlspecialchars($this->points);?></span>
                			<br />
                		<?php endif; ?>
            		<?php endif; ?>
        		</div>
    			<?php if ($i < count($this->licenses) - 1) : ?>
            		<hr/>
            	<?php endif; ?>
        	<?php endforeach; ?>
        </div>

    	<?php if (!$this->user->id) : ?>
    		<div class="front_message"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_YOU_NEED_TO_LOGIN_TO_BUY_A_LICENSE"));?></div>
    		<?php if ($this->show_login) : ?>
    			<hr/>
    			<?php require_once(JPATH_COMPONENT . '/views/pay/tmpl/login.php'); ?>
    		<?php endif; ?>
    		<?php if ($this->useQuickRegister) : ?>
    			<hr/>
    			<?php require_once(JPATH_COMPONENT . '/views/pay/tmpl/register.php'); ?>
    		<?php endif; ?>
    	<?php endif; ?>
    <?php endif; ?>
</div>