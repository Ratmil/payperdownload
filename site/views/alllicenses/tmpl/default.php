<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;
?>
<div class="all_licenses<?php echo $this->pageclass_sfx ? ' '.$this->pageclass_sfx : ''; ?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
    	<div class="page-header">
    		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    	</div>
    <?php endif; ?>
    <?php if (empty($this->licenses)) : ?>
		<span class="front_message no_license"><?php echo JText::_("PAYPERDOWNLOADPLUS_NO_LICENSES"); ?></span>
	<?php else : ?>
		<div class="licenses">
            <?php foreach ($this->licenses as $i => $license) : ?>
            	<?php $url = JRoute::_("index.php?option=com_payperdownload&view=pay&lid=" . (int)$license->license_id); ?>
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
                	<br/>

                	<?php if ($license->price - $license->discount_price > 0.001) : ?>
                		<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_DISCOUNT_PRICE"));?></span>
                		<span class="front_price_data">
                        	<?php if ($license->discount_price > 0.001) : ?>
                        		<?php echo htmlspecialchars($license->discount_price) . "&nbsp;" . htmlspecialchars($license->currency_code); ?>
                        	<?php else : ?>
                        		<?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_DISCOUNT_FREE")); ?>
                        	<?php endif; ?>
                        </span>
                		<br/>
                	<?php endif; ?>

                	<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_LICENSE_EXPIRATION_TIME"));?></span>
                	<span class="front_price_data">
                		<?php if ($license->expiration > 0) : ?>
                			<?php echo htmlspecialchars(JText::sprintf("PAYPERDOWNLOADPLUS_LICENSE_EXPIRATION_TIME_VALUE", $license->expiration)); ?>
                		<?php else : ?>
                		    <?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_LICENSE_EXPIRATION_LIFE_TIME")); ?>
                		<?php endif; ?>
                	</span>
                	<br/>

                	<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_DOWNLOADS_COUNT"));?></span>
                	<span class="front_price_data">
                		<?php if ($license->max_download > 0) : ?>
                			<?php echo htmlspecialchars($license->max_download); ?>
                		<?php else : ?>
                			<?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_UNLIMITED_DOWNLOADS")); ?>
                		<?php endif; ?>
                	</span>
                	<br/>

                	<?php if ($license->canRenew) : ?>
                		<a class="buy_license" href="<?php echo $url;?>"><?php echo JText::_("PAYPERDOWNLOADPLUS_PAY_LICENSE");?></a>
                	<?php endif; ?>
                	<br/>

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
</div>