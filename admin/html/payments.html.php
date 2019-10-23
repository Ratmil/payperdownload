<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 **/

// no direct access
defined ( '_JEXEC' ) or die;

define("HEIGHT", 70);

/*** Class to generate HTML code ***/
class PaymentsHtmlForm extends BaseHtmlForm
{
    function renderStatistics($statistics, $this_month, $this_year)
    {
        $root = JURI::root();
        $max = 0;
        for($i = 1; $i < count($statistics); $i++)
        {
            $stat = $statistics[$i];
            if(!$stat->total_amount)
                $stat->total_amount = '0.00';
                if(!$stat->total_fee)
                    $stat->total_fee = '0.00';
                    if($stat->total_amount > $max)
                        $max = $stat->total_amount;
        }
        if($max < 0.0001)
            $max = 1.0;
            ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo JHtmlSidebar::render(); ?>
		</div>
    	<div id="j-main-container" class="span10">
    		<table class="table table-striped" style="border: 1px solid #ccc;">
    			<caption><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_LATEST_MONTH_STATS"));?></caption>
    			<thead>
            		<tr>
            			<th><?php echo htmlspecialchars(JText::_("JMONTH")); ?></th>
                		<?php
                    		for($i = -11; $i <= 0; $i++)
                    		{
                    			$month = $this_month + $i;
                    			if($month < 1)
                    			{
                    				$month += 12;
                    				$year = $this_year - 1;
                    			}
                    			else
                    				$year = $this_year;
                    			echo '<th style="text-align: center">' . htmlspecialchars($this->monthToString($month, true)) . '<br/>' . htmlspecialchars($year) . '</th>';
                    		}
                		?>
            		</tr>
            	</thead>
            	<tbody>
            		<tr>
            			<td>&nbsp;</td>
                		<?php
                    		for($i = -11; $i <= 0; $i++)
                    		{
                    			$month = $this_month + $i;
                    			if($month < 1)
                    				$month += 12;
                    			$stat = $statistics[$month];
                    			$height = (int)(($stat->total_amount / $max) * HEIGHT);
                    			if($height == 0)
                    				$height = 2;
                    			$this->renderOneBar($height, $root. 'administrator/components/com_payperdownload/images/green.png');
                    		}
                		?>
            		</tr>
            		<tr>
            			<th><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_PAID")); ?> </th>
                		<?php
                    		for($i = -11; $i <= 0; $i++)
                    		{
                    			$month = $this_month + $i;
                    			if($month < 1)
                    				$month += 12;
                    			$stat = $statistics[$month];
                    			echo '<td style="text-align: right">' . sprintf("%.2f", $stat->total_amount) . "</td>";
                    		}
                		?>
            		</tr>
            		<tr>
            			<th><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_FEE")); ?></th>
                		<?php
                    		for($i = -11; $i <= 0; $i++)
                    		{
                    			$month = $this_month + $i;
                    			if($month < 1)
                    				$month += 12;
                    			$stat = $statistics[$month];
                    			echo '<td style="text-align: right">' . sprintf("%.2f", $stat->total_fee) . "</td>";
                    		}
                		?>
            		</tr>
            		<tr class="success">
            			<th><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_COLLECTED")); ?></th>
                		<?php
                    		$sum_paid = 0;
                    		$sum_fee = 0;
                    		for($i = -11; $i <= 0; $i++)
                    		{
                    			$month = $this_month + $i;
                    			if($month < 1)
                    				$month += 12;
                    			$stat = $statistics[$month];
                    			$sum_paid += $stat->total_amount;
                    			$sum_fee += $stat->total_fee;
                    			echo '<td style="text-align: right">' . sprintf("%.2f", ($stat->total_amount - $stat->total_fee)) . "</td>";
                    		}
                		?>
            		</tr>
            	</tbody>
    		</table>
    		<br/>
    		<table class="table table-striped" style="border: 1px solid #ccc;">
    			<thead>
    				<tr>
    					<th>&nbsp;</th>
    					<th style="text-align: right"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_PAID")); ?></th>
    					<th style="text-align: right"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_FEE")); ?></th>
    					<th style="text-align: right"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_COLLECTED")); ?></th>
    				</tr>
    			</thead>
    			<tbody>
    				<tr>
    					<th><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_TOTAL_LATEST_MONTH_STATS")); ?></th>
    					<td style="text-align: right"><?php echo sprintf("%.2f", $sum_paid); ?></td>
    					<td style="text-align: right"><?php echo sprintf("%.2f", $sum_fee); ?></td>
    					<td style="text-align: right"><?php echo sprintf("%.2f", ($sum_paid - $sum_fee)); ?></td>
    				</tr>
    				<tr>
    					<th><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_TOTAL_STATS")); ?></th>
    					<td style="text-align: right"><?php echo sprintf("%.2f", $statistics[0]->total_amount); ?></td>
    					<td style="text-align: right"><?php echo sprintf("%.2f", $statistics[0]->total_fee); ?></td>
    					<td style="text-align: right"><?php echo sprintf("%.2f", ($statistics[0]->total_amount - $statistics[0]->total_fee)); ?></td>
    				</tr>
    			</tbody>
    		</table>
    		<span class="stat_warning"><?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_STATS_WARNING")); ?></span>
		</div>
		<?php
	}

	function monthToString($month, $abbr = false)
	{
		switch ($month)
		{
			case 1:  return $abbr ? JText::_('JANUARY_SHORT')	: JText::_('JANUARY');
			case 2:  return $abbr ? JText::_('FEBRUARY_SHORT')	: JText::_('FEBRUARY');
			case 3:  return $abbr ? JText::_('MARCH_SHORT')		: JText::_('MARCH');
			case 4:  return $abbr ? JText::_('APRIL_SHORT')		: JText::_('APRIL');
			case 5:  return $abbr ? JText::_('MAY_SHORT')		: JText::_('MAY');
			case 6:  return $abbr ? JText::_('JUNE_SHORT')		: JText::_('JUNE');
			case 7:  return $abbr ? JText::_('JULY_SHORT')		: JText::_('JULY');
			case 8:  return $abbr ? JText::_('AUGUST_SHORT')	: JText::_('AUGUST');
			case 9:  return $abbr ? JText::_('SEPTEMBER_SHORT')	: JText::_('SEPTEMBER');
			case 10: return $abbr ? JText::_('OCTOBER_SHORT')	: JText::_('OCTOBER');
			case 11: return $abbr ? JText::_('NOVEMBER_SHORT')	: JText::_('NOVEMBER');
			case 12: return $abbr ? JText::_('DECEMBER_SHORT')	: JText::_('DECEMBER');
		}
	}

	function renderOneBar($height, $background_image)
	{
		?>
		<td style="vertical-align: bottom; height: <?php echo HEIGHT;?>px; width: 20px; text-align: center">
			<div style="width: 20px; background: url(<?php echo $background_image; ?>); height: <?php echo htmlspecialchars($height)?>px; margin: 0 auto;"></div>
		</td>
		<?php
	}
}
?>