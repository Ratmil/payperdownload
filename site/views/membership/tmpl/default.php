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
<ul class="front_list">
<?php
foreach($this->members as $member)
{
echo "<li>";
echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . htmlspecialchars($member->name) . "&nbsp;&nbsp;&nbsp;&nbsp;(" .
	htmlspecialchars($member->member_title). ")";
echo "</li>";
?>
<?php
$last_license = $member->license_id;
}
?>
</ul>
<?php
echo "<div class=\"pagination\">";
echo $this->pagination->getPagesLinks();
echo "</div>";
?>