<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('JHtmlPayPerDownload', JPATH_COMPONENT . '/helpers/html/payperdownload.php');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . 'administrator/components/com_payperdownload/css/backend.css');
$document->addStyleSheet(JUri::root() . 'media/com_payperdownload/css/list.css');

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_payperdownload');
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder) {
	$saveOrderingUrl = 'index.php?option=com_payperdownload&task=licenses.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'licenseList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields(); // TODO not used!

JHtmlPayPerDownload::showHints();
?>

<form action="<?php echo JRoute::_('index.php?option=com_payperdownload&view=licenses'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty($this->sidebar)): ?>
    	<div id="j-sidebar-container" class="span2">
    		<?php echo $this->sidebar; ?>
    	</div>
    	<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif; ?>
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<div class="clearfix"></div>
			<table class="table table-striped" id="licenseList">
				<thead>
    				<tr>
    					<?php if (isset($this->items[0]->ordering)): ?>
    						<th width="1%" class="nowrap center hidden-phone">
                                <?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                            </th>
    					<?php endif; ?>
    					<th width="1%" class="hidden-phone">
    						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
    					</th>
        				<th class='center'><?php echo JHtml::_('searchtools.sort', 'COM_PAYPERDOWNLOAD_HEADING_LICENSES_ENABLED', 'a.enabled', $listDirn, $listOrder); ?></th>
        				<th class='nowrap'><?php echo JHtml::_('searchtools.sort', 'COM_PAYPERDOWNLOAD_HEADING_LICENSES_LICENSE_NAME', 'a.license_name', $listDirn, $listOrder); ?></th>
        				<th class='nowrap hidden-phone'><?php echo JHtml::_('searchtools.sort', 'COM_PAYPERDOWNLOAD_HEADING_LICENSES_MEMBER_TITLE', 'a.member_title', $listDirn, $listOrder); ?></th>
        				<th class='nowrap hidden-phone'><?php echo JHtml::_('searchtools.sort', 'COM_PAYPERDOWNLOAD_HEADING_LICENSES_EXPIRATION', 'a.expiration', $listDirn, $listOrder); ?></th>
        				<th class='nowrap'><?php echo JHtml::_('searchtools.sort', 'COM_PAYPERDOWNLOAD_HEADING_LICENSES_PRICE', 'a.price', $listDirn, $listOrder); ?></th>
        				<th class='nowrap'><?php echo JHtml::_('searchtools.sort', 'COM_PAYPERDOWNLOAD_HEADING_LICENSES_CURRENCY_CODE', 'a.currency_code', $listDirn, $listOrder); ?></th>
        				<th class='nowrap'><?php echo JHtml::_('searchtools.sort', 'COM_PAYPERDOWNLOAD_HEADING_LICENSES_LEVEL', 'a.level', $listDirn, $listOrder); ?></th>
        				<th class='nowrap hidden-phone'><?php echo JHtml::_('searchtools.sort', 'COM_PAYPERDOWNLOAD_HEADING_LICENSES_MAX_DOWNLOAD', 'a.max_download', $listDirn, $listOrder); ?></th>
        				<th class='center hidden-phone'><?php echo JText::_('COM_PAYPERDOWNLOAD_HEADING_LICENSES_LICENSE_IMAGE'); ?></th>
        <!-- 				<th class='left'> -->
        				<?php // echo JHtml::_('searchtools.sort',  'COM_PAYPERDOWNLOAD_LICENSES_CREATED_BY', 'a.created_by', $listDirn, $listOrder); ?>
        <!-- 				</th> -->
        				<th class='left'><?php echo JHtml::_('searchtools.sort',  'JGRID_HEADING_ID', 'a.license_id', $listDirn, $listOrder); ?></th>
    				</tr>
				</thead>
				<tfoot>
    				<tr>
    					<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
    						<?php echo $this->pagination->getListFooter(); ?>
    					</td>
    				</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$ordering   = ($listOrder == 'a.ordering');
					$canCreate  = $user->authorise('core.create', 'com_payperdownload');
					$canEdit    = $user->authorise('core.edit', 'com_payperdownload');
					$canCheckin = $user->authorise('core.manage', 'com_payperdownload');
					$canChange  = $user->authorise('core.edit.state', 'com_payperdownload');
					?>
					<tr class="row<?php echo $i % 2; ?>">

						<?php if (isset($this->items[0]->ordering)) : ?>
							<td class="order nowrap center hidden-phone">
								<?php if ($canChange) :
									$disableClassName = '';
									$disabledLabel    = '';

									if (!$saveOrder) :
										$disabledLabel    = JText::_('JORDERINGDISABLED');
										$disableClassName = 'inactive tip-top';
									endif; ?>
									<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>" title="<?php echo $disabledLabel ?>">
            							<i class="icon-menu"></i>
            						</span>
									<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
								<?php else : ?>
									<span class="sortable-handler inactive">
										<i class="icon-menu"></i>
									</span>
								<?php endif; ?>
							</td>
						<?php endif; ?>
        				<td class="hidden-phone">
        					<?php echo JHtml::_('grid.id', $i, $item->license_id); ?>
        				</td>
        				<td class="center">
        					<?php if ($canChange) : ?>
        						<?php echo JHtml::_('jgrid.state', JHtmlPayPerDownload::enableStates(), !$item->enabled, $i, 'licenses.'); ?>
        					<?php else : ?>
        						<?php echo JText::_($item->enabled ? 'JYES' : 'JNO'); ?>
        					<?php endif; ?>
        				</td>
        				<td>
        				    <!--
            				<?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
            					<?php echo JHtml::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'licenses.', $canCheckin); ?>
            				<?php endif; ?>
            				 -->
            				<?php if ($canEdit) : ?>
            					<a href="<?php echo JRoute::_('index.php?option=com_payperdownload&task=license.edit&license_id='.(int) $item->license_id); ?>">
            					<?php echo $this->escape($item->license_name); ?></a>
            				<?php else : ?>
            					<?php echo $this->escape($item->license_name); ?>
            				<?php endif; ?>
        				</td>
        				<td class="hidden-phone">
        					<?php echo $item->member_title; ?>
        				</td>
        				<td class="hidden-phone">
        					<?php echo $item->expiration; ?>
        				</td>
        				<td>
        					<?php echo $item->price; ?>
        				</td>
        				<td>
        					<?php echo $item->currency_code; ?>
        				</td>
        				<td>
        					<?php echo $item->level; ?>
        				</td>
        				<td class="hidden-phone">
        					<?php echo $item->max_download; ?>
        				</td>
        				<td class="col_picture center hidden-phone">
        					<?php if ($item->license_image): ?>
        						<?php echo JHTML::_('image', $item->license_image, $item->license_name); ?>
        					<?php endif; ?>
        				</td>
        				<td>
        					<?php echo $item->license_id; ?>
        				</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<script>
    window.toggleField = function (id, task, field) {

        var f = document.adminForm, i = 0, cbx, cb = f[ id ];

        if (!cb) return false;

        while (true) {
            cbx = f[ 'cb' + i ];

            if (!cbx) break;

            cbx.checked = false;
            i++;
        }

        var inputField   = document.createElement('input');

        inputField.type  = 'hidden';
        inputField.name  = 'field';
        inputField.value = field;
        f.appendChild(inputField);

        cb.checked = true;
        f.boxchecked.value = 1;
        window.submitform(task);

        return false;
    };
</script>