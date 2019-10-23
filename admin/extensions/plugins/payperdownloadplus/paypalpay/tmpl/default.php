<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// needs extras: language and default

$button_class_attribute = '';
if ($classes) {
    $button_class_attribute = ' class="' . $classes . '"';
}
?>
<?php if ($use_coupons) : ?>
    <label for="coupon_code"><?php echo JText::_('PLG_PAYPERDOWNLOADPLUS_DISCOUNT_COUPON'); ?></label>
	<input type="text" id="coupon_code" name="coupon_code" /><br />
<?php endif;?>
<button id="paypal-checkout-button"<?php echo $button_class_attribute; ?> type="button">
	<?php if ($image == '0') : ?>
		<img alt="<?php echo JText::_('PLG_PAYPERDOWNLOADPLUS_PAYPALPAY_PAYWITHPAYPAL'); ?>" title="<?php echo JText::_('PLG_PAYPERDOWNLOADPLUS_PAYPALPAY_PAYWITHPAYPAL'); ?>" src="https://www.paypal.com/<?php echo JText::_("PLG_PAYPERDOWNLOADPLUS_PAYPALPAY_BUTTON_LANGUAGE_FOLDER") ?>/i/btn/btn_paynowCC_LG.gif" />
	<?php elseif ($image) : ?>
		<img alt="<?php echo JText::_('PLG_PAYPERDOWNLOADPLUS_PAYPALPAY_PAYWITHPAYPAL'); ?>" title="<?php echo JText::_('PLG_PAYPERDOWNLOADPLUS_PAYPALPAY_PAYWITHPAYPAL'); ?>" src="<?php echo JURI::root(true) . '/plugins/payperdownloadplus/paypalpay/images/' . $image ?>" />
	<?php else : ?>
		<?php echo JText::_('PLG_PAYPERDOWNLOADPLUS_PAYPALPAY_PAYWITHPAYPAL'); ?>
	<?php endif; ?>
</button>