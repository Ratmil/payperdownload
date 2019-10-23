<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;

jimport('joomla.utilities.date');
?>
<div class="user_licenses<?php echo $this->pageclass_sfx ? ' '.$this->pageclass_sfx : ''; ?>">
    <?php if ($this->params->get('show_page_heading')) : ?>
    	<div class="page-header">
    		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    	</div>
    <?php endif; ?>
    <?php if (!JFactory::getUser()->id) : ?>
    	<?php JFactory::getApplication()->enqueueMessage(JText::_("PAYPERDOWNLOADPLUS_LOGIN_TO_VIEW_YOUR_LICENSES"), 'warning'); ?>
    <?php else : ?>
		<?php if (empty($this->licenses)) : ?>
			<span class="front_message no_license"><?php echo JText::_("PAYPERDOWNLOADPLUS_NO_LICENSES"); ?></span>
		<?php else : ?>
            <div class="licenses">
            	<?php foreach($this->licenses as $i => $license) : ?>

                	<?php $url = JRoute::_("index.php?option=com_payperdownload&view=pay&lid=" . (int)$license->license_id); ?>
                	<?php $date = new JDate($license->expiration_date); ?>

					<div class="ppd_license license <?php echo $license->expired ? 'expired' : 'active'; ?>">

            			<div class="front_title license_title">
            				<?php if (isset($license->image) && $license->image) : ?>
                				<div class="license_image">
    								<img src="<?php echo htmlspecialchars($license->image, ENT_COMPAT, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($license->license_name, ENT_COMPAT, 'UTF-8'); ?>" />
    							</div>
                			<?php endif; ?>
                			<div class="license_name"><?php echo htmlspecialchars($license->license_name); ?></div>
            			</div>

            			<?php if (!$license->expired && $license->license_max_downloads > 0) : ?>
            				<span class="license_downloads"><?php echo JText::sprintf("PAYPERDOWNLOADPLUS_REMAINING_DOWNLOADCOUNT", $license->license_max_downloads - $license->download_hits); ?></span><br />
            			<?php endif; ?>

            			<?php if ($license->expiration_date) : ?>
            				<?php if ($license->expired) : ?>
            					<span class="license_expiration"><?php echo JText::_("PAYPERDOWNLOADPLUS_EXPIRED") . ":&nbsp;" . $date->format(JText::_("DATE_FORMAT_LC1")); ?></span>
                				<?php if ($license->canRenew) : ?>
                					<a class="buy_license" href="<?php echo $url; ?>"><?php echo JText::_("PAYPERDOWNLOADPLUS_BUYAGAIN_LICENSE"); ?></a>
                				<?php endif; ?>
                				<br />
            				<?php else : ?>
            					<span class="license_expiration"><?php echo JText::_("PAYPERDOWNLOADPLUS_EXPIRES") . ":&nbsp;" . $date->format(JText::_("DATE_FORMAT_LC1")); ?></span>
                				<?php if ($license->canRenew) : ?>
                					<a class="buy_license" href="<?php echo $url; ?>"><?php echo JText::_("PAYPERDOWNLOADPLUS_RENEW_LICENSE"); ?></a>
                				<?php endif; ?>
                				<br />
            				<?php endif; ?>
            			<?php else : ?>
            				<span class="license_expiration"><?php echo JText::_("PAYPERDOWNLOADPLUS_NEVEREXPIRES"); ?></span><br />
            			<?php endif; ?>

            			<?php if (isset($license->resources) && count($license->resources) > 0) : ?>
            				<dl class="resources">
            					<dt><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_AVAILABLE_RESOURCES_FOR_THIS_LICENSE_5")); ?></dt>
                				<?php foreach($license->resources as $resource) : ?>
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

            			<?php if (isset($license->description) && $license->description) : ?>
            				<div class="license_description"><?php echo $license->description; ?></div>
            			<?php endif; ?>
            		</div>
        			<?php if ($i < count($this->licenses) - 1) : ?>
                		<hr/>
                	<?php endif; ?>
            	<?php endforeach; ?>
            </div>
            <div class="pagination">
            	<?php echo $this->pagination->getPagesLinks(); ?>
            </div>
    	<?php endif; ?>
    <?php endif; ?>
</div>