UPDATE #__payperdownloadplus_coupons_users 
SET `coupon_code`=0 WHERE `coupon_code` IS NULL;

ALTER TABLE #__payperdownloadplus_coupons_users
CHANGE `coupon_code` `coupon_code` VARCHAR(64) NOT NULL;