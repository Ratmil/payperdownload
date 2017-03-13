<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or
die( 'Direct Access to this location is not allowed.' );

define("HEIGHT", 70);

/*** Class to generate HTML code ***/
class PaymentsHtmlForm extends BaseHtmlForm
{
	function renderStatistics($statistics, $this_month, $this_year)
	{
		$root = JURI::root();
		?>
		<link rel="stylesheet" href="<?php echo $root;?>administrator/components/com_payperdownload/css/stat.css" type="text/css" />
		<?php
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
		<table border="1px;">
		<tr>
		<td colspan="13" class="stat_title" align="center">
		<?php echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_LATEST_MONTH_STATS"));?>
		</td>
		</tr>
		<tr class="stat_header">
		<td>
		<?php
		echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_MONTH"));
		?>
		</td>
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
			echo "<td>";
			echo htmlspecialchars($this->monthToString($month, true)) . "<br/>" . htmlspecialchars($year);
			echo "</td>";
		}
		?>
		</tr>
		<tr class="stat_bar">
		<td>&nbsp;
		</td>
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
		<tr class="stat_paid">
		<td>
		<?php
		echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_AMOUNT__14"));
		?>
		</td>
		<?php
		for($i = -11; $i <= 0; $i++)
		{
			$month = $this_month + $i;
			if($month < 1)
				$month += 12;
			$stat = $statistics[$month];
			echo "<td align=\"right\">" . htmlspecialchars($stat->total_amount) . "</td>";
		}
		?>
		</tr>
		<tr class="stat_fee">
		<td>
		<?php
		echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_FEE__18"));
		?>
		</td>
		<?php
		for($i = -11; $i <= 0; $i++)
		{
			$month = $this_month + $i;
			if($month < 1)
				$month += 12;
			$stat = $statistics[$month];
			echo "<td align=\"right\">" . htmlspecialchars($stat->total_fee) . "</td>";
		}
		?>
		</tr>
		<tr class="stat_collected">
		<td>
		<?php
		echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_GAIN__22"));
		?>
		</td>
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
			echo "<td align=\"right\">" . htmlspecialchars($stat->total_amount - $stat->total_fee) . "</td>";
		}
		?>
		</tr>
		</table>
		<br/>
		<span class="stat_label">
		<?php
		echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_TOTAL_LATEST_MONTH_STATS"));
		?>
		</span>
		<span class="stat_data">
		<?php
		echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_AMOUNT__15"));
		echo htmlspecialchars($sum_paid);
		echo "&nbsp;&nbsp;&nbsp;";
		echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_FEE__19"));
		echo htmlspecialchars($sum_fee);
		echo "&nbsp;&nbsp;&nbsp;";
		echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_GAIN__23"));
		echo htmlspecialchars($sum_paid - $sum_fee);
		?>
		</span>
		<br/>
		<span class="stat_label">
		<?php
		echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_TOTAL_STATS"));
		?>
		</span>
		<span class="stat_data">
		<?php
		echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_AMOUNT__15"));
		echo htmlspecialchars($statistics[0]->total_amount);
		echo "&nbsp;&nbsp;&nbsp;";
		echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_FEE__19"));
		echo htmlspecialchars($statistics[0]->total_fee);
		echo "&nbsp;&nbsp;&nbsp;";
		echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_GAIN__23"));
		echo htmlspecialchars($statistics[0]->total_amount - $statistics[0]->total_fee);
		?>
		</span>
		<br/>
		<span class="stat_warning">
		<?php
		echo htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_STATS_WARNING"));
		?>
		</span>
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
		<td valign="bottom" align="center" style="height: <?php echo HEIGHT;?>px; width: 20px;">
		<div style="width: 20px; background: url(<?php echo $background_image; ?>);height: <?php echo htmlspecialchars($height)?>px;">
		</div>
		</td>
		<?php
	}
}
?>