<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
?>
<?php
defined('_JEXEC') or die;

class PayPerDownloadController extends JControllerLegacy
{
	function confirm()
	{
		$model = $this->getModel( "Pay", 'PayPerDownloadModel' );
		if($model)
		{
			$model->handleResponse();
		}
	}

	function confirmres()
	{
		$model = $this->getModel( "PayResource", 'PayPerDownloadModel' );
		if($model)
		{
			$model->handleResponse();
		}
	}

	function getfree()
	{
		$result = false;
		$model = $this->getModel( "Pay", 'PayPerDownloadModel' );

		$jinput = JFactory::getApplication()->input;

		if($model)
		{
			$user = JFactory::getUser();
			if($user->id)
			{
			    $license_id = $jinput->getInt('license_id');
				$result = $model->getFree($license_id, $user->id);
			}
		}
		$mainframe = JFactory::getApplication();
		$returnUrl = base64_decode($jinput->getBase64('returnurl', ''));
		$msg = "";
		if(!$result)
			$msg = JText::_("PAYPERDOWNLOADPLUS_DISCOUNT_GET_FREE_ERROR");
		$mainframe->redirect($returnUrl, $msg);
	}

	function joinaffiliate()
	{
	    $jinput = JFactory::getApplication()->input;

	    $aff = $jinput->getInt('aff');
	    $Itemid = $jinput->getInt('Itemid');
		$model = $this->getModel( "Affiliate", 'PayPerDownloadModel' );
		$isUpdate = false;
		$result = $model->updateAffiliateData($isUpdate);
		$mainframe = JFactory::getApplication();
		if($result)
		{
			if($isUpdate)
				$msg = JText::_("PAYPERDOWNLOADPLUS_AFFILIATE_UPDATE_SUCCESSFULL");
			else
				$msg = JText::_("PAYPERDOWNLOADPLUS_AFFILIATE_JOIN_SUCCESSFULL");
			$mainframe->redirect("index.php?option=com_payperdownload&view=affiliate&aff=$aff&Itemid=$Itemid",
				$msg);
		}
		else
		{
			if($isUpdate)
				$msg = JText::_("PAYPERDOWNLOADPLUS_AFFILIATE_UPDATE_FAILED");
			else
				$msg = JText::_("PAYPERDOWNLOADPLUS_AFFILIATE_JOIN_FAILED");
			$mainframe->redirect("index.php?option=com_payperdownload&view=affiliate&aff=$aff&Itemid=$Itemid",
				$msg, "error");
		}
	}

	function confirmaffiliatepayment()
	{
		$model = $this->getModel( "PayAffiliate", 'PayPerDownloadModel' );
		if($model)
		{
			$model->handleResponse();
		}
	}

	function paypalsim()
	{
	    $jinput = JFactory::getApplication()->input;

	    $notify_url = $jinput->getString('notify_url');
	    $req = "receiver_email=" . urlencode($jinput->getString('business'));
	    $req .= "&business=" . urlencode($jinput->getString('business'));
		$req .= "&payer_email=" . urlencode("paypalsimulator@paypal.com");
		$req .= "&txn_id=" . urlencode(rand());
		$req .= "&test_ipn=1";
		$req .= "&custom=" . urlencode($jinput->getInt('custom'));
		$req .= "&item_number=" . urlencode($jinput->getInt('item_number'));
		$req .= "&mc_gross=" . urlencode($jinput->getFloat('amount'));
		$req .= "&mc_currency=" . urlencode($jinput->getString('currency_code'));
		$req .= "&payment_status=COMPLETED";
		$req .= "&mc_fee=0.01";
		$req .= "&tax=0.00";

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_URL, $notify_url);
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$req);
		curl_exec($ch);
		curl_close($ch);
		$mainframe = JFactory::getApplication();
		$return = $jinput->getString('return');
		$mainframe->redirect("index.php?option=com_payperdownload&view=paypalsim&return=" .	urlencode($return));
	}

	function quickRegister()
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_users', JPATH_SITE . '/administrator');

		$jinput = JFactory::getApplication()->input;

		$return = $jinput->getBase64('return', '');
		if($return)
			$return = base64_decode($return);

		$mainframe = JFactory::getApplication();
		$usersConfig = JComponentHelper::getParams( 'com_users' );
		$newUsertype = $usersConfig->get( 'new_usertype' );

		jimport('joomla.user.user');
		$userName = $jinput->getUsername('regusername');
		$userFullName = $jinput->getString('name');

		//$userPassword = JRequest::getString('regpassword', '', 'post', JREQUEST_ALLOWRAW);
		//$userPassword2 = JRequest::getString('regpassword2', '', 'post', JREQUEST_ALLOWRAW);

		$userPassword = $jinput->getRaw('regpassword', '');
		$userPassword2 = $jinput->getRaw('regpassword2', '');

		$email = $jinput->getString('email');
		$email2 = $jinput->getString('email2');
		$params = array("name" => $userFullName, "username" => $userName,
			"password" => $userPassword, "password2" => $userPassword2,
			"email" => $email);

		$user = new JUser();
		$user->groups = array();
		$user->groups []= $newUsertype;

		if(!$user->bind($params))
		{
			$mainframe->redirect($return, JText::_( $user->getError()));
			exit;
		}
		if($email != $email2)
		{
			$mainframe->redirect($return, JText::_('PAYPERDOWNLOADPLUS_REGISTER_EMAILS_DO_NOT_MATCH'));
			exit;
		}
		$date = JFactory::getDate();
		$user->set('registerDate', $date->toSQL());
		if ( !$user->save() )
		{
			$mainframe->redirect($return, JText::_( $user->getError()));
			exit;
		}
		$options = array();
		$options['return'] = $return;
		$options['remember'] = $jinput->getBool('remember', false);
		$credentials = array();
		$credentials['username'] = $userName;
		$credentials['password'] = $userPassword;
		$mainframe->login($credentials, $options);
		$user = JFactory::getUser();
		if(!$user->id)
		{
			$mainframe->redirect($return, JText::_('PAYPERDOWNLOADPLUS_REGISTER_LOGIN_ERROR'));
		}
		else
		{
			if($return)
			{
				$this->_sendmail($email, $userName);
				$mainframe->redirect($return, JText::_( 'PAYPERDOWNLOADPLUS_REGISTER_YOU_HAVE_BEEN_SUCCESSFULLY_REGISTERED' ));
			}
			else
			{
				$mainframe->redirect('index.php', JText::_( 'PAYPERDOWNLOADPLUS_REGISTER_YOU_HAVE_BEEN_SUCCESSFULLY_REGISTERED' ));
			}
		}
	}

	function buyWithAup()
	{
	    $jinput = JFactory::getApplication()->input;

		require_once(JPATH_COMPONENT . '/models/pay.php');
		$model = new PayPerDownloadModelPay();
		$return = $jinput->getBase64('return', '');
		if($return)
			$return = base64_decode($return);
		$mainframe = JFactory::getApplication();
		$alpha_integration = $model->getAlphaIntegration();
		if($alpha_integration == 2)
		{
			$user = JFactory::getUser();
			if($user->id)
			{
				$user_points = $model->getAUP();
				$license_id = $jinput->getInt('lid', 0);
				$license = $model->getLicense($license_id);
				if($license && $license->aup > 0 && $license->aup <= $user_points)
				{
					if($model->removeAUPFromUser($user->id, $license))
					{
						$model->assignLicense($user->id, $license_id, 0, true);
						$mainframe->redirect($return, JText::_("PAYPERDOWNLOADPLUS_LICENSE_BOUGHT_WITH_AUP"));
					}
					else
						$mainframe->redirect($return, JText::_("PAYPERDOWNLOADPLUS_AUP_NOT_ENABLED"), "error");
				}
				else
					$mainframe->redirect($return, JText::_("PAYPERDOWNLOADPLUS_NOT_ENOUGH_AUP"), "error");
			}
			else
				$mainframe->redirect($return, JText::_("PAYPERDOWNLOADPLUS_NOT_LOGGEDIN"), "error");
		}
		else
			$mainframe->redirect($return, JText::_("PAYPERDOWNLOADPLUS_AUP_NOT_ENABLED"), "error");
	}

	function sendLink()
	{
	    $jinput = JFactory::getApplication()->input;

	    $access = $jinput->getRaw('access');
	    $m = $jinput->getString('m', '');
		$regexp = "/^\s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*$/";
		if(preg_match($regexp, $m))
		{
			list($downloadId, $hash, $rand) = explode("-", $access);
			$db = JFactory::getDBO();
			$downloadId = (int)$downloadId;
			$rand = $db->escape($rand);
			$query = "SELECT * FROM #__payperdownloadplus_download_links
				WHERE download_id = $downloadId AND random_value = '$rand' AND payed <> 0 AND
				(expiration_date > NOW() OR expiration_date IS NULL) AND
				(link_max_downloads = 0 OR download_hits < link_max_downloads)";
			$db->setQuery( $query );
			$downloadLink = $db->loadObject();
			if($downloadLink)
			{
				if($hash == sha1($downloadLink->secret_word . $downloadLink->random_value))
				{
					$mail = JFactory::getMailer();
					$mail->setSubject($downloadLink->email_subject);
					$mail->setBody($downloadLink->email_text);
					$mail->ClearAddresses();
					$mail->addRecipient($m);
					$mail->IsHTML(true);
					$joomla_config = new JConfig();
					$mail->setSender(array($joomla_config->mailfrom, $joomla_config->fromname));
					$mail->Send();
					echo "<<1>>";
					exit();
				}
			}
		}
		echo "<<0>>";
		exit();
	}

	function donate()
	{
		JPluginHelper::importPlugin("payperdownloadplus");
		$dispatcher	= JDispatcher::getInstance();
		$dispatcher->trigger('onDonationReceived');
		exit;
	}

	function paypalpay()
	{
	    $jinput = JFactory::getApplication()->input;

		require_once(JPATH_COMPONENT . '/models/pay.php');
		$model = new PayPerDownloadModelPay();
		$paymentInfo = $model->getPaymentInfo();
		$hasAmp = false;
		if($paymentInfo->test_mode)
		{
			if($paymentInfo->usesimulator)
			{
				$paypal = "index.php?option=com_payperdownload&task=paypalsim";
				$hasAmp = true;
			}
			else
				$paypal = "https://www.sandbox.paypal.com/cgi-bin/webscr";
		}
		else
		{
			$paypal = "https://www.paypal.com/cgi-bin/webscr";
		}
		$requests = array();
		$hasBusiness = false;
		$coupon_code = $jinput->getString("coupon_code");
		$price = $jinput->getFloat("amount");
		if($coupon_code)
		{
		    $ppd_item_type = $jinput->getString("ppd_item_type");
			if($ppd_item_type == "license")
			{
			    $item = $jinput->getInt("item_number");
			}
			else
			{
			    $item = $jinput->getInt("custom");
			}
			$new_price = $model->applyDiscountCoupon($coupon_code, $price,
				$item, $ppd_item_type == "license");
			if($new_price && $new_price > 0)
				$price = round($new_price, 2);
		}
		foreach (/*$_POST*/$jinput->post->getArray() as $key => $value)
		{
			if($key != "task" && $key != "option" && $key != "ppd_item_type" && $key != "coupon_code" && $key != "amount")
			{
				if($key == "business")
					$hasBusiness = true;
				$requests []= $key . "=" . urlencode($value);
			}
		}
		$requests []= "amount=" . urlencode($price);
		if(!$hasBusiness)
			$requests []= "business=" . urlencode($paymentInfo->paypal_account);
		$request = implode("&", $requests);
		if($hasAmp)
			$request = $paypal . "&" . $request;
		else
			$request = $paypal . "?" . $request;
		header("Location: $request");
		die();
	}

	function _sendmail($useremail, $username)
	{
		$mainframe = JFactory::getApplication();
		$mailfrom = $mainframe->getCfg( 'mailfrom' );
		$fromname = $mainframe->getCfg( 'fromname' );
		$siteURL  = JURI::base();
		$subject = JText::_("PAYPERDOWNLOADPLUS_USER_REGISTER_SUBJECT");
		$text = JText::sprintf("PAYPERDOWNLOADPLUS_USER_REGISTER_MAIL", $siteURL, $username);
		$mail = JFactory::getMailer();
		$mail->setSubject($subject);
		$mail->setBody($text);
		$mail->clearAddresses();
		$mail->addRecipient($useremail);
		$mail->IsHTML(true);
		$joomla_config = new JConfig();
		$mail->setSender(array($joomla_config->mailfrom, $joomla_config->fromname));
		$mail->send();
	}

	function _getGroupId($groupName)
	{
		$db = JFactory::getDBO();
		$groupName = $db->escape($groupName);
		$query = "SELECT id FROM #__usergroups WHERE title = '$groupName'";
		$db->setQuery( $query );
		return $db->loadResult();
	}
}

?>
