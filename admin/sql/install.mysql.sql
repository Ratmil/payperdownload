DROP TABLE IF EXISTS #__payperdownloadplus_coupons_users;
DROP TABLE IF EXISTS #__payperdownloadplus_users_licenses_discount;
DROP TABLE IF EXISTS #__payperdownloadplus_affiliates_users_refered;
DROP TABLE IF EXISTS #__payperdownloadplus_affiliates_users;
DROP TABLE IF EXISTS #__payperdownloadplus_affiliates_banners;
DROP TABLE IF EXISTS #__payperdownloadplus_affiliates_programs;
DROP TABLE IF EXISTS #__payperdownloadplus_download_links;
DROP TABLE IF EXISTS #__payperdownloadplus_resource_licenses;
DROP TABLE IF EXISTS #__payperdownloadplus_orders;
DROP TABLE IF EXISTS #__payperdownloadplus_users_licenses;
DROP TABLE IF EXISTS #__payperdownloadplus_licenses;
DROP TABLE IF EXISTS #__payperdownloadplus_config;
DROP TABLE IF EXISTS #__payperdownloadplus_coupons;
DROP TABLE IF EXISTS #__payperdownloadplus_payments;
DROP TABLE IF EXISTS #__payperdownloadplus_last_time_check;
DROP TABLE IF EXISTS #__payperdownloadplus_debug;
DROP TABLE IF EXISTS #__payperdownloadplus_user_balance;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_licenses` (
  `license_id` int(11) NOT NULL auto_increment,
  `license_name` varchar(255) NOT NULL,
  `member_title` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  `price` decimal(11,2),
  `currency_code` varchar(50),
  `level` int(11) NOT NULL,
  `description` text,
  `thankyou_text` text,
  `notify_url` varchar(1024) NOT NULL,
  `max_download` int(11) NOT NULL DEFAULT 0,
  `license_image` varchar(255) NULL,
  `aup` int(11) NOT NULL DEFAULT 0,
  `renew` int(11) DEFAULT '0', /*0 : always renew, 1: only if not active, 2: never*/
  `enabled` int(11) DEFAULT '1',
  `user_group` int(11) NULL,
  PRIMARY KEY  (`license_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_affiliates_programs` (
  `affiliate_program_id` int(11) NOT NULL auto_increment,
  `program_name` varchar(256),
  `program_description` text,
  `license_id` int(11) NOT NULL,
  `percent` decimal(11,2),
  `enabled` int(11) DEFAULT '1',
  PRIMARY KEY  (`affiliate_program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_affiliates_users` (
  `affiliate_user_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `affiliate_program_id` int(11) NOT NULL,
  `credit` decimal(11,2) DEFAULT '0',
  `paypal_account` varchar(256),
  `website` varchar(256),
  PRIMARY KEY  (`affiliate_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_affiliates_users_refered` (
  `referer_user` int(11) NOT NULL,
  `refered_user` int(11) NOT NULL,
  PRIMARY KEY  (`referer_user`, refered_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_affiliates_banners` (
  `affiliate_banner_id` int(11) NOT NULL auto_increment,
  `affiliate_program_id` int(11) NOT NULL,
  `banner_title` varchar(256),
  `image` varchar(512),
  PRIMARY KEY  (`affiliate_banner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_download_links` (
  `download_id` int(11) NOT NULL auto_increment,	
  `resource_id` int(11) NOT NULL,
  `item_id` varchar(128) NULL,
  `secret_word` varchar(128) NOT NULL,
  `random_value` varchar(128) NOT NULL,
  `user_ip` varchar(32) NOT NULL,
  `user_id` int(11) NULL,
  `user_email` varchar(256) NOT NULL,
  `payer_email` varchar(256) NOT NULL,
  `email_subject` varchar(256) NULL,
  `email_text` text NULL,
  `expiration_date` datetime NULL,
  `creation_date` datetime NULL,
  `download_link` varchar(256) NULL,
  `download_hits` int(11) DEFAULT '0',
  `link_max_downloads` int(11) DEFAULT '0',
  `discount` decimal(11,2) DEFAULT '0',
  `coupon_code` varchar(64) NULL,
  `payed` int(11) NOT NULL DEFAULT 1,
   PRIMARY KEY  (`download_id`),
   KEY  (`resource_id`, random_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_resource_licenses` (
  `resource_license_id` int(11) NOT NULL auto_increment,
  `license_id` int(11) NULL,
  `resource_id` int(11) NOT NULL,
  `resource_name` varchar(256) NOT NULL,
  `resource_description` varchar(256) NOT NULL,
  `alternate_resource_description` varchar(256) NOT NULL,
  `resource_type` varchar(256) NOT NULL,
  `resource_option_parameter` varchar(256) NOT NULL,
  `resource_params` varchar(1024) NOT NULL,
  `resource_price` decimal(11,2) NULL,
  `resource_price_currency` varchar(50) NULL,
  `download_expiration` int(11) DEFAULT 365,
  `max_download` int(11) NOT NULL DEFAULT 0,
  `payment_header` text,
  `shared` int(11) DEFAULT '1',
  `enabled` int(11) DEFAULT '1',
  PRIMARY KEY  (`resource_license_id`),
  KEY (`resource_option_parameter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
 
CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_users_licenses` (
  `user_license_id` int(11) NOT NULL auto_increment,
  `license_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expiration_date` datetime NULL,
  `enabled` int(11) DEFAULT '1',
  `credit` int(11) DEFAULT '0',
  `duration` int(11) DEFAULT '0',
  `credit_days_used` int(11) DEFAULT '0',
  `download_hits` int(11) DEFAULT '0',
  `license_max_downloads` int(11) DEFAULT '0',
  `item` varchar(128) NULL,
  `assigned_user_group` int(11) NULL,
  PRIMARY KEY  (`user_license_id`),
  KEY `user_id` (`user_id`),
  KEY expiration_date (expiration_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_payments` (
  `payment_id` int(11) NOT NULL auto_increment,	
  `txn_id` varchar(256) NOT NULL,
  `user_id` int(11) NULL,
  `user_email` varchar(256) NULL,
  `receiver_email` varchar(256) NULL,
  `license_id` int(11) NULL,
  `resource_id` int(11) NULL,
  `affiliate_user_id` int(11) NULL,
  `payed` int(11) NOT NULL DEFAULT '0',
  `amount` decimal(11,2) NULL,
  `currency` varchar(128) NULL,
  `fee` decimal(11,2) NULL,
  `tax` decimal(11,2) NULL,
  `status` varchar(128) NULL,
  `payment_date` datetime NULL,
  `validate_response` varchar(256) NULL,
  `response` varchar(2048) NULL,
  `to_merchant` int(11) default 0,
  `used` int(11) NULL DEFAULT '1',
   PRIMARY KEY  (`payment_id`),
   KEY `txn_id` (`txn_id`),
   KEY `payment_date` (`payment_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_orders` (
  `order_id` int(11) NOT NULL auto_increment,	
  `param1` int(11),	
  `param2` int(11),	
  `param3` int(11),	
  `create_time` datetime NULL,	
  `param4` varchar(256) NULL,
   PRIMARY KEY  (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

	

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_config` (
  `config_id` int(11) NOT NULL auto_increment,	
  `usepaypal` int(11) DEFAULT '1',
  `usepayplugin` int(11),
  `paypalaccount` varchar(128),
  `testmode` int(11),
  `usesimulator` int(11) DEFAULT '0',
  `askemail` int(11) DEFAULT '0',
  `paymentnotificationemail` varchar(128),
  `notificationsubject` varchar(2048),
  `notificationtext` varchar(2048),
  `usernotificationsubject` varchar(2048),
  `usernotificationtext` varchar(2048),
  `guestnotificationsubject` varchar(2048),
  `guestnotificationtext` varchar(2048),
  `usenoaccesspage` int(11) DEFAULT '0',
  `noaccesspage` varchar(2048),
  `multilicenseview` int(11),
  `showresources` int(11),
  `apply_discount` int(11) DEFAULT '1',
  `apply_discount_renew` int(11) DEFAULT '1',
  `renew_discount_percent` int(11) DEFAULT '10',
  `loginurl` varchar(128),
  `return_param` varchar(128),
  `payment_header` text,
  `resource_payment_header` text,
  `alternate_pay_license_header` text,
  `thank_you_page` text,
  `thank_you_page_resource` text,
  `payment_page_menuitem` int(11),
  `thankyou_page_menuitem` int(11),
  `show_license_on_kunena` int(11) DEFAULT '0',
  `privilege_groups` varchar(256),
  `show_hints` int(11) DEFAULT '1',
  `show_login` int(11) DEFAULT '1',
  `show_quick_register` int(11) DEFAULT '0',
  `use_osol_captcha` int(11) DEFAULT '1',
  `license_sort` int(11) DEFAULT '2', /*1- level then name, 2-level then price, 3 - level then expiration days, 4-name then level, 5-price then level, 6 - expiration days then level*/
  /*Integration with Alpha User points 
	0: no integration, 
	1: Assign points for buying a license, 
	2: Use points to buy a license*/
  `alphapoints` int(11) DEFAULT '0', 
  `tax_rate` decimal(11,2) DEFAULT '0', 
  `use_discount_coupon` int(11) DEFAULT '0',
   PRIMARY KEY  (`config_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE IF NOT EXISTS #__payperdownloadplus_debug (
	`debug_id` int(11) NOT NULL auto_increment,	
	`debug_text` varchar(256),
	`debug_time` datetime NULL, 
   PRIMARY KEY  (`debug_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_last_time_check` (
  `last_time_check` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE IF NOT EXISTS #__payperdownloadplus_user_balance (
	`user_id` int(11),	
	`balance` decimal(11, 2) DEFAULT 0,
	`currency` varchar(50), 
   PRIMARY KEY  (`user_id`, `currency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE IF NOT EXISTS #__payperdownloadplus_coupons (
	`coupon_id` int(11) NOT NULL auto_increment,	
	`code` varchar(64),
	`expire_time` datetime NULL, 
	`discount` decimal(11, 2) DEFAULT 10,
   PRIMARY KEY  (`coupon_id`),
   KEY (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_users_licenses_discount` (
  `license_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `discount` decimal(11, 2) DEFAULT 10,
  `coupon_code` varchar(64) NULL,
  PRIMARY KEY  (`license_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE IF NOT EXISTS #__payperdownloadplus_coupons_users (
	`coupon_code` varchar(64) NOT NULL,	
	`user_id` int(11) NOT NULL,	
   PRIMARY KEY  (`coupon_code`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

ALTER TABLE `#__payperdownloadplus_resource_licenses`
	ADD CONSTRAINT `#__payperdownloadplus_file_licenses_ibfk_1` FOREIGN KEY (`license_id`) 
		REFERENCES `#__payperdownloadplus_licenses` (`license_id`);	  
	
ALTER TABLE `#__payperdownloadplus_users_licenses`
	ADD CONSTRAINT `#__payperdownloadplus_users_licenses_ibfk_1` FOREIGN KEY (`license_id`) 
		REFERENCES `#__payperdownloadplus_licenses` (`license_id`);	
		
ALTER TABLE `#__payperdownloadplus_download_links`
	ADD CONSTRAINT `#__payperdownloadplus_download_links_ibfk_1` FOREIGN KEY (`resource_id`) 
		REFERENCES `#__payperdownloadplus_resource_licenses` (`resource_license_id`) ON DELETE CASCADE;	

ALTER TABLE `#__payperdownloadplus_affiliates_programs`
	ADD CONSTRAINT `#__payperdownloadplus_affiliates_programs_ibfk_1` FOREIGN KEY (`license_id`) 
		REFERENCES `#__payperdownloadplus_licenses` (`license_id`);	
		
ALTER TABLE `#__payperdownloadplus_affiliates_users`
	ADD CONSTRAINT `#__payperdownloadplus_affiliates_users_ibfk_1` FOREIGN KEY (`affiliate_program_id`) 
		REFERENCES `#__payperdownloadplus_affiliates_programs` (`affiliate_program_id`);	
		
ALTER TABLE `#__payperdownloadplus_affiliates_banners`
	ADD CONSTRAINT `#__payperdownloadplus_affiliates_banners_ibfk_1` FOREIGN KEY (`affiliate_program_id`) 
		REFERENCES `#__payperdownloadplus_affiliates_programs` (`affiliate_program_id`);

ALTER TABLE `#__payperdownloadplus_users_licenses_discount`
	ADD CONSTRAINT `#__payperdownloadplus_users_licenses_discount_ibfk_1` FOREIGN KEY (`license_id`) 
		REFERENCES `#__payperdownloadplus_licenses` (`license_id`);	 
