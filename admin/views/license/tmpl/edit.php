<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . 'media/com_payperdownload/css/form.css'); // TODO add

$isLicenseNew = true;
if (!empty($this->item->license_id)) {
    $isLicenseNew = false;
}
?>
<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function () {

	});

	Joomla.submitbutton = function (task) {
		if (task == 'license.cancel') {
			Joomla.submitform(task, document.getElementById('license-form'));
		}
		else {

			if (task != 'license.cancel' && document.formvalidator.isValid(document.id('license-form'))) {

				Joomla.submitform(task, document.getElementById('license-form'));
			}
			else {
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_payperdownload&layout=edit&license_id=' . (int) $this->item->license_id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="license-form" class="form-validate">

	<div class="form-inline form-inline-header">
		<?php echo $this->form->renderField('license_name'); ?>
	</div>

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php if (!$isLicenseNew): ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_PAYPERDOWNLOAD_TAB_EDIT_LICENSE', true)); ?>
		<?php else: ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_PAYPERDOWNLOAD_TAB_NEW_LICENSE', true)); ?>
		<?php endif; ?>

			<div class="row-fluid">
    			<div class="span9">
    				<fieldset class="adminform">
        				<input type="hidden" name="jform[license_id]" value="<?php echo $this->item->license_id; ?>" />
        				<?php echo $this->form->renderField('member_title'); ?>
        				<?php echo $this->form->renderField('expiration'); ?>
        				<?php echo $this->form->renderField('price'); ?>
        				<?php echo $this->form->renderField('currency_code'); ?>
        				<?php echo $this->form->renderField('level'); ?>
        				<?php echo $this->form->renderField('max_download'); ?>
        				<?php echo $this->form->renderField('aup'); ?>
        				<?php echo $this->form->renderField('renew'); ?>
        				<?php echo $this->form->renderField('license_image'); ?>
        				<?php echo $this->form->renderField('description'); ?>
        				<?php echo $this->form->renderField('thankyou_text'); ?>
        				<?php /*echo $this->form->renderField('created_by');*/ ?>
    				</fieldset>
    			</div>
    			<div class="span3">
    				<fieldset class="form-vertical">
        				<?php echo $this->form->renderField('enabled'); ?>
        				<?php echo $this->form->renderField('user_group'); ?>
    				</fieldset>
    			</div>
			</div>

		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>

	</div>
</form>
