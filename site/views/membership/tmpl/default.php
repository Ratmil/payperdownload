<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die;
?>
<div class="memberships<?php echo $this->pageclass_sfx ? ' '.$this->pageclass_sfx : ''; ?>">
    <?php if ($this->params->get('show_page_heading')) : ?>
    	<div class="page-header">
    		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    	</div>
    <?php endif; ?>
    <?php if (empty($this->members)) : ?>
		<span class="front_message no_members"><?php echo JText::_("PAYPERDOWNLOADPLUS_NO_MEMBERS"); ?></span>
	<?php else : ?>
		<ul class="front_list members">
            <?php foreach ($this->members as $member) : ?>
            	<li>
            		<?php echo htmlspecialchars($member->name) . ' (' . htmlspecialchars($member->member_title). ')'; ?>
            	</li>
            	<?php $last_license = $member->license_id; ?><?php // purpose ? ?>
            <?php endforeach; ?>
    	</ul>
        <div class="pagination">
    		<?php echo $this->pagination->getPagesLinks(); ?>
    	</div>
    <?php endif; ?>
</div>