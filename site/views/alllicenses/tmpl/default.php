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

<?php
	
$first = true;
foreach($this->licenses as $license)
{
	$url = JRoute::_("index.php?option=com_payperdownload&view=pay&lid=" . (int)$license->license_id);
	?>
	<div class="ppd_license" id="div_license_<?php echo $license->license_id;?>">
	<br/>
	<div class="front_title"><?php echo htmlspecialchars($license->license_name)."&nbsp;&nbsp;&nbsp;";?></div>
	<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_PRICE___4"));?></span>
	<span class="front_price_data"><?php echo htmlspecialchars($license->price) . "&nbsp;" . htmlspecialchars($license->currency_code);?></span>
	<br/>
	<?php 
	if($license->price - $license->discount_price > 0.001)
	{
	?>
	<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_DISCOUNT_PRICE"));?></span>
	<span class="front_price_data">
	<?php 
	if($license->discount_price > 0.001)
		echo htmlspecialchars($license->discount_price) . "&nbsp;" . htmlspecialchars($license->currency_code);
	else
		echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_DISCOUNT_FREE"));
	?></span>
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
		?>
	</span>
	<br/>
	<span class="front_price_label"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_DOWNLOADS_COUNT"));?></span>
	<span class="front_price_data"><?php 
		if($license->max_download > 0)
			echo htmlspecialchars($license->max_download); 
		else
			echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_UNLIMITED_DOWNLOADS"));
		?>
	</span>
	<br/>
	<?php 
	if($license->canRenew)
	{
	?>
	<a class="buy_license" href="<?php echo $url;?>"><?php echo JText::_("PAYPERDOWNLOADPLUS_PAY_LICENSE");?></a>
	<?php
	}
	?>
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
	</div>
	<hr/>
	<?php
}
echo "<div class=\"pagination\">";
echo $this->pagination->getPagesLinks();
echo "</div>";

?>
