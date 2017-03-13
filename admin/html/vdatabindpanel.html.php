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

jimport('joomla.html.pane');

class CPanelSeparatorVisualDataBind extends VisualDataBind
{
	var $close;
	var $open;
	var $paneName;
	var $panelName;
	
	function __construct($displayName, $panelName, $open = true, $close = true)
	{
		parent::__construct("", $displayName);
		$this->open = $open;
		$this->close = $close;
		$this->panelName = $panelName;
		$this->showInGrid = false;
		$this->editLinkText = "";
		$this->ignoreToSelect = true;
		$this->ignoreToBind = true;
		$this->useForTextSearch = false;
		$this->paneName = "unnamed-pane";
	}
	
	function setPaneName($paneName)
	{	
		$this->paneName = $paneName;	
	}
	
	function renderNew()
	{
		return $this->renderEdit();
	}
	
	function renderEdit()
	{
		$pane = JPane::getInstance('sliders');
		if($this->open && $this->close)
		{
			return "</table>".
				$pane->endPanel() . 
				$pane->startPanel($this->displayName, 'panel-' . $this->panelName).
				"<table class=\"admintable\">";
		}
		else if($this->open)
		{
			return "</table>".
				$pane->startPane($this->paneName) . 
				$pane->startPanel($this->displayName, 'panel-' . $this->panelName).
				"<table class=\"admintable\">";
		}
		else if($this->close)
		{
			return "</table>".
				$pane->endPanel() . $pane->endPane().
				"<table class=\"admintable\">";
		}
		else
			return "";
	}
	
	function renderValidateJavascript()
	{
		return "";
	}
	
	
}
/*
<ul class="nav nav-tabs">
	<li class="active"><a href="#details" data-toggle="tab">Details</a></li>

	<li><a href="#publishing" data-toggle="tab">Publishing Options</a></li>
	<li><a href="#metadata" data-toggle="tab">Metadata Options</a></li>
</ul>
<div class="tab-content">
	<div class="tab-pane active" id="details">
	11111111111111111111
	</div>
	<div class="tab-pane" id="publishing">
	22222222222222222222
	</div>
	<div class="tab-pane" id="metadata">
	3333333333
	</div>
</div>	
*/
?>