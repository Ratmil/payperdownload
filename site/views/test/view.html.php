<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML Article View class for the Content component
 *
 * @package     Joomla.Site
 * @subpackage  com_content
 * @since       1.5
 */
class PayperdownloadViewTest extends JViewLegacy
{
    function display($tpl = null)
    {
        require_once (JPATH_ADMINISTRATOR . "/components/com_payperdownload/classes/debug.php");

        PayPerDownloadPlusDebug::debug("Test debug");

        require_once JPATH_SITE.'/components/com_payperdownload/models/pay.php';

        $model = new PayPerDownloadModelPay();

        //$result = $model->getLicense(1); // OK
        //$result = $model->getRenovationOptions(1); // OK
        //$result = $model->canLicenseBeRenewed(1); // OK
        //$result = $model->getHigherLicenses(1); // OK

        //handleResponse();
        //assignAffiliateCredit($user_id, $license_id, $amount);
        //deleteLicenseDiscount($user_id, $license_id)
        //getLicenseDiscount($user_id, $license_id)

        //$result = $model->getUserData(741); // OK

        //getPaymentData($payment_id)
        //updateDownloadLink($download_id, $subject, $text, $download_link)
        //getUserLicenseExpiration($user_license_id)
        //assignLicense($user_id, $license_id, $credit = 0, $set_credit_days = false)
        //getUserLicense($user_id, $license_id)
        //isTransactionPayed($txn_id)

        //$result = $model->getLicenseResources(1); // OK

        //getDiscountLicense($license, $user_id)
        //removeUsedCredit($license, $user_id)
        //createDownloadLink($resource_id, $itemId)

        //$result = $model->getResource(1); // OK

        //applyDiscountCoupon($coupon_code, $price, $payItemId, $itemIsLicense)

        //var_dump($result);

        // ==================================================================================================

        //require_once JPATH_SITE.'/components/com_payperdownload/models/payresource.php';

        //$model = new PayPerDownloadModelPayResource();

        // ==================================================================================================

        // specific queries

        $db = JFactory::getDBO();

        $query = $db->getQuery(true);

//         $query->insert($db->quoteName('#__payperdownloadplus_coupons_users')); // OK
//         $query->columns($db->quoteName(array('coupon_code', 'user_id')));
//         $query->values(implode(',', array($db->quote('COUPON'), 741)));

//         $db->setQuery($query);

//         if (!$db->execute()) {
//             var_dump('query failed');
//         } else {
//             var_dump('query succeded');
//         }

        $columns = array(
            //'user_id',
            'user_email',
            'resource_id',
            'payed',
            'payment_date',
            'txn_id',
            'response',
            'validate_response',
            'status',
            'amount',
            'tax',
            'fee',
            'currency',
            'receiver_email'
        );

        $values = array(
            //NULL,
            $db->quote('obuisard@gmail.com'),
            1,
            1,
            'NOW()',
            $db->quote('gkfkyfkuytfk'),
            $db->quote('text'),
            $db->quote('VERIFIED'),
            $db->quote('status'),
            20.0,
            10.0,
            0,
            $db->quote('USD'),
            $db->quote('obuisard@gmail.com')
        );

        $query = $db->getQuery(true);

        $query->insert($db->quoteName('#__payperdownloadplus_payments'));
        $query->columns($db->quoteName($columns));
        $query->values(implode(',', $values));

        $db->setQuery($query);

        if (!$db->execute()) {
            var_dump('query failed');
        } else {
            var_dump('query succeded');
        }

    }
}
