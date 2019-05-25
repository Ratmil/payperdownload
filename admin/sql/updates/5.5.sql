ALTER TABLE #__payperdownloadplus_config
	ADD COLUMN `apply_discount_renew` int(11) DEFAULT '1';
ALTER TABLE #__payperdownloadplus_config
	ADD COLUMN `renew_discount_percent` int(11) DEFAULT '10';