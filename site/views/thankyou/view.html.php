<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.view');

class PayPerDownloadViewThankyou extends JViewLegacy
{
	function display($tpl = null)
	{
		$option = JRequest::getVar('option');
		JHTML::_('stylesheet', 'components/'. $option . '/css/frontend.css');
		$model = $this->getModel();
		if($model)
		{
			$askEmail = false;
			$accessCode = '';
			$lid = JRequest::getInt('lid', 0);
			if($lid)
			{
				$thank_you = $model->getLicenseThankyouText($lid);
				if($model->cleanHtml($thank_you) == '')
					$thank_you = $model->getThankyou();
				$thank_you = $model->replaceContinueUrl($thank_you);
			}
			else
			{
				$accesscode = JRequest::getVar('accesscode');
				list($download_id, $hash, $random) = explode("-", $accesscode);
				if($model->validateAccessCode($download_id, $hash, $random))
				{
					if($model->isDownloadPaid($download_id))
					{
						$thank_you = $model->getResourceThankyou($download_id);
						$askEmail = $model->askEmail();
						if($askEmail)
						{
							$accessCode = $model->getDownloadLinkAccessCode($download_id);
							$scriptPath = "administrator/components/$option/js/";
							JHTML::script($scriptPath . "ajax_source.js");
						}
					}
					else
						$thank_you = JText::_("PAYPERDOWNLOADPLUS_DOWNLOADLINK_NOT_PAID");
				}
				else
					$thank_you = "Unauthorized access";
			}
			$this->assignRef("thank_you", $thank_you);
			$this->assignRef("askEmail", $askEmail);
			$this->assignRef("accessCode", $accessCode);
			parent::display($tpl);
		}
		else
			echo "model not found";
	}
}

?>