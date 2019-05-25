ALTER TABLE #__payperdownloadplus_config
	ADD COLUMN `use_discount_coupon` int(11) DEFAULT '0';
	
ALTER TABLE #__payperdownloadplus_download_links
	ADD COLUMN `discount` decimal(11,2) DEFAULT '0';

ALTER TABLE #__payperdownloadplus_download_links
	ADD COLUMN `coupon_code` varchar(64) NULL;
	
CREATE TABLE IF NOT EXISTS #__payperdownloadplus_coupons (
	`coupon_id` int(11) NOT NULL auto_increment,	
	`code` varchar(64),
	`expire_time` datetime NULL, 
	`discount` decimal(11, 2) DEFAULT 10,
	`coupon_code` varchar(64) NULL,
   PRIMARY KEY  (`coupon_id`),
   INDEX(code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_users_licenses_discount` (
  `license_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `discount` decimal(11, 2) DEFAULT 10,
  `coupon_code` varchar(64) NULL,
  PRIMARY KEY  (`license_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

ALTER TABLE `#__payperdownloadplus_users_licenses_discount`
	ADD CONSTRAINT `#__payperdownloadplus_users_licenses_discount_ibfk_1` FOREIGN KEY (`license_id`) 
		REFERENCES `#__payperdownloadplus_licenses` (`license_id`);	 