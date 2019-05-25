<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$documentation_url = 'http://www.ratmilwebsolutions.com/documentation/64-payperdownloadplus-help.html';
$forum_url = 'http://www.ratmilwebsolutions.com/forum/index.html';
$changelog_url = 'http://www.ratmilwebsolutions.com/component/content/article.html?id=54&Itemid=145';
?>

<?php if (!empty($this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
	<div class="row-fluid">
		<div class="span12">
			<div class="row-fluid form-horizontal-desktop">
				<div class="span8">
					<fieldset>
						<legend><?php echo JText::_('COM_PAYPERDOWNLOAD_INFO_TOOLS'); ?></legend>
    					<div class="icons">
                    		<?php echo $this->getIcon('index.php?option=com_payperdownload&amp;adminpage=resources&amp;view=resources', 'icon-grid-2', JText::_ ('COM_PAYPERDOWNLOAD_RESOURCES')); ?>
                    		<?php echo $this->getIcon('index.php?option=com_payperdownload&amp;adminpage=licenses&amp;view=licenses', 'icon-grid', JText::_ ('COM_PAYPERDOWNLOAD_LICENSES')); ?>
                    		<?php echo $this->getIcon('index.php?option=com_payperdownload&amp;adminpage=users&amp;view=users', 'icon-users', JText::_ ('COM_PAYPERDOWNLOAD_USERS_LICENCES')); ?>
                    		<?php echo $this->getIcon('index.php?option=com_payperdownload&amp;adminpage=downloads&amp;view=downloads', 'icon-link', JText::_ ('COM_PAYPERDOWNLOAD_DOWNLOAD_LINKS')); ?>
                    		<?php echo $this->getIcon('index.php?option=com_payperdownload&amp;adminpage=orders&amp;view=orders', 'icon-credit-2', JText::_ ('COM_PAYPERDOWNLOAD_PAYMENTS')); ?>
                    		<?php echo $this->getIcon('index.php?option=com_payperdownload&amp;adminpage=coupons&amp;view=coupons', 'icon-scissors', JText::_ ('COM_PAYPERDOWNLOAD_COUPONS')); ?>
                    		<?php echo $this->getIcon('index.php?option=com_payperdownload&amp;adminpage=backup&amp;view=backup', 'icon-database', JText::_ ('COM_PAYPERDOWNLOAD_BACKUP')); ?>
                    		<?php if ($this->debug) : ?>
                    			<?php echo $this->getIcon('index.php?option=com_payperdownload&amp;adminpage=debug&amp;view=debug', 'icon-wrench', JText::_('COM_PAYPERDOWNLOAD_DEBUG')); ?>
                    		<?php endif;?>
                    		<?php echo $this->getIcon('index.php?option=com_payperdownload&amp;adminpage=config&amp;view=config', 'icon-options', JText::_ ('COM_PAYPERDOWNLOAD_CONFIGURATION')); ?>
    					</div>
    				</fieldset>
					<fieldset>
						<legend><?php echo JText::_('COM_PAYPERDOWNLOAD_INFO_HELP'); ?></legend>
    					<div class="icons">
                    		<?php echo $this->getIcon($documentation_url, 'icon-book', JText::_ ('COM_PAYPERDOWNLOAD_INFO_ONLINEDOC'), '_blank'); ?>
                    		<?php echo $this->getIcon($forum_url, 'icon-comments-2', JText::_ ('COM_PAYPERDOWNLOAD_INFO_HELPINFORUM'), '_blank'); ?>
    					</div>
    				</fieldset>
    				<fieldset>
						<legend><?php echo JText::_('COM_PAYPERDOWNLOAD_INFO_PLUGINS'); ?></legend>
						<table class="table table-striped" cellpading="0" cellspacing="0">
							<tbody>
								<?php foreach(JPluginHelper::getPlugin('payperdownloadplus') as $plugin) : ?>
    								<tr>
    									<td><?php echo JText::_('COM_PAYPERDOWNLOAD_PLUGIN_'.strtoupper($plugin->name)); ?></td>
    									<?php if (JPluginHelper::isEnabled('payperdownloadplus', $plugin->name)) : ?>
    										<td><span class="icon-publish"></span></td>
    										<td><a class="btn btn-small hasTooltip" href="index.php?option=com_plugins&view=plugins&filter_folder=payperdownloadplus&filter_enabled=1"><?php echo JText::_('JLIB_HTML_UNPUBLISH_ITEM'); ?></a></td>
    									<?php else : ?>
    										<td><span class="icon-unpublish"></span></td>
    										<td><a class="btn btn-small hasTooltip" href="index.php?option=com_plugins&view=plugins&filter_folder=payperdownloadplus&filter_enabled=0"><?php echo JText::_('JLIB_HTML_PUBLISH_ITEM'); ?></a></td>
    									<?php endif; ?>
    								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</fieldset>
				</div>
				<div class="span4">
					<fieldset>
						<legend><?php echo JText::_('COM_PAYPERDOWNLOAD'); ?></legend>
						<p><img src="<?php echo JURI::root(true) ?>/administrator/components/com_payperdownload/images/ppdplus.png" /></p>
						<table class="table table-striped" cellpading="0" cellspacing="0">
							<tbody>
								<tr>
									<td><?php echo JText::_('COM_PAYPERDOWNLOAD_INFO_VERSION'); ?></td>
									<td><?php echo $this->extension_version; ?></td>
								</tr>
								<tr>
									<td><?php echo JText::_('COM_PAYPERDOWNLOAD_INFO_CHANGELOGS'); ?></td>
									<td><a href="<?php echo $changelog_url; ?>" target="_blank"><?php echo JText::_('COM_PAYPERDOWNLOAD_INFO_RELEASEHISTORY'); ?></a></td>
								</tr>
								<tr>
									<td><?php echo JText::_('COM_PAYPERDOWNLOAD_INFO_LICENSE'); ?></td>
									<td><a href="http://www.gnu.org/licenses/lgpl-3.0.html" target="_blank">GNU General Public License v3</a></td>
								</tr>
								<tr>
									<td><?php echo JText::_('COM_PAYPERDOWNLOAD_INFO_AUTHORS'); ?></td>
									<td>Ratmil Torres,<br />Olivier Buisard (v6.0 revisions) </td>
								</tr>
							</tbody>
						</table>
					</fieldset>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="span12 copyright-container">
	<p><?php echo JText::_('COM_PAYPERDOWNLOAD'); ?> <?php echo JText::sprintf('COM_PAYPERDOWNLOAD_INFO_VERSION', $this->extension_version); ?></p>
	<p><?php echo JText::sprintf('COM_PAYPERDOWNLOAD_POSTAREVIEW', 'https://extensions.joomla.org/extensions/extension/e-commerce/paid-downloads/pay-per-download/'); ?> <i class="icon-star" style="font-size: 1.1em; color: #f7c41f; vertical-align: middle"></i><i class="icon-star" style="font-size: 1.1em; color: #f7c41f; vertical-align: middle"></i><i class="icon-star" style="font-size: 1.1em; color: #f7c41f; vertical-align: middle"></i><i class="icon-star" style="font-size: 1.1em; color: #f7c41f; vertical-align: middle"></i><i class="icon-star" style="font-size: 1.1em; color: #f7c41f; vertical-align: middle"></i></p>
	<p class="copyright">Copyright &copy; 2010-<?php echo date("Y"); ?> <a href="http://www.ratmilwebsolutions.com" target="_blank">Ratmil Web Solutions</a>. All rights reserved.</p>
	<p class="copyright"><img src="<?php echo JURI::root(true) ?>/administrator/components/com_payperdownload/images/simplifyyourweb.png" alt="Simplify Your Web" /><br />Copyright &copy; 2011-<?php echo date("Y"); ?> <a href="https://simplifyyourweb.com" target="_blank">Simplify Your Web</a>. All rights reserved.</p>
</div>