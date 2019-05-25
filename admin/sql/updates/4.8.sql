
CREATE TABLE IF NOT EXISTS #__payperdownloadplus_coupons_users (
	`coupon_code` varchar(64) NULL,	
	`user_id` int(11) NOT NULL,	
   PRIMARY KEY  (`coupon_code`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
