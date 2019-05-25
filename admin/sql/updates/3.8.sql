CREATE TABLE IF NOT EXISTS #__payperdownloadplus_user_balance (
	`user_id` int(11),	
	`balance` decimal(11, 2) DEFAULT 0,
	`currency` varchar(50), 
   PRIMARY KEY  (`user_id`, `currency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

ALTER TABLE #__payperdownloadplus_payments
	ADD COLUMN `used` int(11) NULL DEFAULT '1';