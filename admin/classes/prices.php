<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

// no direct access
defined('_JEXEC') or die;

class PayPerDownloadPrices
{
	static function replaceResourcePrice($matches)
	{
		$lang = JFactory::getLanguage();
		$lang->load('plg_system_payperdownloadplus', JPATH_SITE.'/administrator');
		$resources = $matches[1];
		$resources = explode(",", $resources);
		$html = self::getResourcePricesHtml($resources);
		return $html;
	}

	static function getResourcePricesHtml($resources_id)
	{
		$html = "";
		$resources = self::getResourcesData( $resources_id );
		$licenses = self::getLicensesForResources( $resources_id );
		$html = "";
		if($resources != null || $licenses != null)
		{
			$html .= "<ul class=\"ppd_licenses_title\">";
			if($resources)
			{
				foreach($resources as $resource)
				{
					$html .= "<li>";
					$html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
						htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_SYSTEM_PLUGIN_GUEST_DOWNLOAD"));
					$html .= "&nbsp;&nbsp;";
					$html .= htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_SYSTEM_PLUGIN_PRICE"));
					$html .= htmlspecialchars($resource->resource_price . " " . $resource->resource_price_currency) . ", ";
					$html .= htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_SYSTEM_PLUGIN_DOWNLOADS"));
					if($resource->max_download > 0)
						$html .= htmlspecialchars($resource->max_download) . ", ";
					else
						$html .= htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_SYSTEM_PLUGIN_UNLIMITED_DOWNLOADS")) . ", ";
					$html .= htmlspecialchars(JText::sprintf("PAYPERDOWNLOADPLUS_SYSTEM_PLUGIN_EXPIRESIN", $resource->download_expiration));
					$html .= "</li>";
				}
			}
			if($licenses)
			{
				foreach($licenses as $license)
				{
					$html .= "<li>";
					$html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
						htmlspecialchars($license->license_name);
					$html .= ":&nbsp;&nbsp;";
					$html .= htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_SYSTEM_PLUGIN_PRICE"));
					$html .= htmlspecialchars($license->price . " " . $license->currency_code) . ", ";
					$html .= htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_SYSTEM_PLUGIN_DOWNLOADS"));
					if($license->max_download > 0)
						$html .= htmlspecialchars($license->max_download) . ", ";
					else
						$html .= htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_SYSTEM_PLUGIN_UNLIMITED_DOWNLOADS")) . ", ";
					$html .= htmlspecialchars(JText::sprintf("PAYPERDOWNLOADPLUS_SYSTEM_PLUGIN_EXPIRESIN", $license->expiration));
					$html .= "</li>";
				}
			}
			$html .= "</ul>";
		}
		return $html;
	}

	static function getLicensesForResources($resources)
	{
		$licenses = array();
		foreach($resources as $resource_id)
		{
			$resource_id = (int)$resource_id;
			$lics = self::getLicensesForResource($resource_id);
			if($lics)
			{
				foreach($lics as $lic)
				{
					$found = false;
					foreach($licenses as $license)
					{
						if($license->license_id == $lic->license_id)
						{
							$found = true;
							break;
						}
					}
					if(!$found)
						$licenses []= $lic;
				}
			}
		}
		return $licenses;
	}

	static function getLicensesForResource($resource_id)
	{
		$db = JFactory::getDBO();
		$query = "SELECT license_id FROM #__payperdownloadplus_resource_licenses
			WHERE resource_license_id = " . (int)$resource_id;
		$db->setQuery( $query );
		$license_id = (int)$db->loadResult();
		$query = "SELECT license_id, license_name, expiration, price, currency_code, level, max_download
			FROM #__payperdownloadplus_licenses WHERE license_id = " . (int)$license_id;
		$db->setQuery( $query );
		$license = $db->loadObject();
		if($license)
		{

			$level = (int)$license->level;
			if($level > 0)
			{
				$query = "SELECT license_id, license_name, expiration, price, currency_code, level, max_download
					FROM #__payperdownloadplus_licenses WHERE level > $level";
				$db->setQuery( $query );
				$higher_licenses = $db->loadObjectList();
				$higher_licenses []= $license;
				return $higher_licenses;
			}
			else
			{
				$licenses = array();
				$licenses []= $license;
				return $licenses;
			}
		}
		else
			return null;
	}

	static function getResourcesData($resources)
	{
		$resources = implode(",", $resources);
		$db = JFactory::getDBO();
		$query = "SELECT resource_price, resource_price_currency, download_expiration, max_download
			FROM #__payperdownloadplus_resource_licenses
			WHERE license_id IS NULL AND resource_license_id IN ($resources)";
		$db->setQuery( $query );
		return $db->loadObjectList();
	}
}

?>