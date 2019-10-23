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
DROP TABLE IF EXISTS #__payperdownloadplus_currencies;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_affiliates_programs` (
  `affiliate_program_id` int(11) NOT NULL auto_increment,
  `program_name` varchar(256),
  `program_description` text,
  `license_id` int(11) NOT NULL,
  `percent` decimal(11,2),
  `enabled` int(11) DEFAULT '1',
  PRIMARY KEY  (`affiliate_program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_affiliates_users` (
  `affiliate_user_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `affiliate_program_id` int(11) NOT NULL,
  `credit` decimal(11,2) DEFAULT '0',
  `paypal_account` varchar(256),
  `website` varchar(256),
  PRIMARY KEY  (`affiliate_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_affiliates_users_refered` (
  `referer_user` int(11) NOT NULL,
  `refered_user` int(11) NOT NULL,
  PRIMARY KEY  (`referer_user`, refered_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_affiliates_banners` (
  `affiliate_banner_id` int(11) NOT NULL auto_increment,
  `affiliate_program_id` int(11) NOT NULL,
  `banner_title` varchar(256),
  `image` varchar(512),
  PRIMARY KEY  (`affiliate_banner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_resource_licenses` (
  `resource_license_id` int(11) NOT NULL auto_increment,
  `license_id` int(11) NULL,
  `resource_id` int(11) NOT NULL,
  `resource_name` varchar(256) NOT NULL,
  `resource_description` varchar(256) NOT NULL,
  `alternate_resource_description` varchar(256) NOT NULL,
  `resource_type` varchar(256) NOT NULL,
  `resource_option_parameter` varchar(128) NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_payments` (
  `payment_id` int(11) NOT NULL auto_increment,	
  `txn_id` varchar(190) NOT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_orders` (
  `order_id` int(11) NOT NULL auto_increment,	
  `param1` int(11),	
  `param2` int(11),	
  `param3` int(11),	
  `create_time` datetime NULL,	
  `param4` varchar(256) NULL,
   PRIMARY KEY  (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_debug` (
	`debug_id` int(11) NOT NULL auto_increment,	
	`debug_text` varchar(256),
	`debug_time` datetime NULL, 
   PRIMARY KEY  (`debug_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_last_time_check` (
  `last_time_check` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_user_balance` (
	`user_id` int(11),	
	`balance` decimal(11, 2) DEFAULT 0,
	`currency` varchar(50), 
   PRIMARY KEY  (`user_id`, `currency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_coupons` (
	`coupon_id` int(11) NOT NULL auto_increment,	
	`code` varchar(64),
	`expire_time` datetime NULL, 
	`discount` decimal(11, 2) DEFAULT 10,
   PRIMARY KEY  (`coupon_id`),
   KEY (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_users_licenses_discount` (
  `license_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `discount` decimal(11, 2) DEFAULT 10,
  `coupon_code` varchar(64) NULL,
  PRIMARY KEY  (`license_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_coupons_users` (
	`coupon_code` varchar(64) NOT NULL,	
	`user_id` int(11) NOT NULL,	
   PRIMARY KEY  (`coupon_code`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__payperdownloadplus_currencies` (
	`id` int(11) NOT NULL auto_increment,	
	`country` varchar(64),
	`currency` varchar(64),
	`iso` varchar(3),
	PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__payperdownloadplus_currencies` (`country`, `currency`, `iso`) VALUES
('Afghanistan', 'Afghan afghani', 'AFN'),
('Akrotiri and Dhekelia (UK)', 'European euro', 'EUR'),
('Aland Islands (Finland)', 'European euro', 'EUR'),
('Albania', 'Albanian lek', 'ALL'),
('Algeria', 'Algerian dinar', 'DZD'),
('American Samoa (USA)', 'United States dollar', 'USD'),
('Andorra', 'European euro', 'EUR'),
('Angola', 'Angolan kwanza', 'AOA'),
('Anguilla (UK)', 'East Caribbean dollar', 'XCD'),
('Antigua and Barbuda', 'East Caribbean dollar', 'XCD'),
('Argentina', 'Argentine peso', 'ARS'),
('Armenia', 'Armenian dram', 'AMD'),
('Aruba (Netherlands)', 'Aruban florin', 'AWG'),
('Ascension Island (UK)', 'Saint Helena pound', 'SHP'),
('Australia', 'Australian dollar', 'AUD'),
('Austria', 'European euro', 'EUR'),
('Azerbaijan', 'Azerbaijan manat', 'AZN'),
('Bahamas', 'Bahamian dollar', 'BSD'),
('Bahrain', 'Bahraini dinar', 'BHD'),
('Bangladesh', 'Bangladeshi taka', 'BDT'),
('Barbados', 'Barbadian dollar', 'BBD'),
('Belarus', 'Belarusian ruble', 'BYN'),
('Belgium', 'European euro', 'EUR'),
('Belize', 'Belize dollar', 'BZD'),
('Benin', 'West African CFA franc', 'XOF'),
('Bermuda (UK)', 'Bermudian dollar', 'BMD'),
('Bhutan', 'Bhutanese ngultrum', 'BTN'),
('Bolivia', 'Bolivian boliviano', 'BOB'),
('Bonaire (Netherlands)', 'United States dollar', 'USD'),
('Bosnia and Herzegovina', 'Bosnia and Herzegovina convertible mark', 'BAM'),
('Botswana', 'Botswana pula', 'BWP'),
('Brazil', 'Brazilian real', 'BRL'),
('British Indian Ocean Territory (UK)', 'United States dollar', 'USD'),
('British Virgin Islands (UK)', 'United States dollar', 'USD'),
('Brunei', 'Brunei dollar', 'BND'),
('Bulgaria', 'Bulgarian lev', 'BGN'),
('Burkina Faso', 'West African CFA franc', 'XOF'),
('Burundi', 'Burundi franc', 'BIF'),
('Cabo Verde', 'Cape Verdean escudo', 'CVE'),
('Cambodia', 'Cambodian riel', 'KHR'),
('Cameroon', 'Central African CFA franc', 'XAF'),
('Canada', 'Canadian dollar', 'CAD'),
('Caribbean Netherlands (Netherlands)', 'United States dollar', 'USD'),
('Cayman Islands (UK)', 'Cayman Islands dollar', 'KYD'),
('Central African Republic', 'Central African CFA franc', 'XAF'),
('Chad', 'Central African CFA franc', 'XAF'),
('Chatham Islands (New Zealand)', 'New Zealand dollar', 'NZD'),
('Chile', 'Chilean peso', 'CLP'),
('China', 'Chinese Yuan Renminbi', 'CNY'),
('Christmas Island (Australia)', 'Australian dollar', 'AUD'),
('Cocos (Keeling) Islands (Australia)', 'Australian dollar', 'AUD'),
('Colombia', 'Colombian peso', 'COP'),
('Comoros', 'Comorian franc', 'KMF'),
('Congo, Democratic Republic of the', 'Congolese franc', 'CDF'),
('Congo, Republic of the', 'Central African CFA franc', 'XAF'),
('Cook Islands (New Zealand)', 'Cook Islands dollar', '-'),
('Costa Rica', 'Costa Rican colon', 'CRC'),
('Cote d\'Ivoire', 'West African CFA franc', 'XOF'),
('Croatia', 'Croatian kuna', 'HRK'),
('Cuba', 'Cuban peso', 'CUP'),
('Curacao (Netherlands)', 'Netherlands Antillean guilder', 'ANG'),
('Cyprus', 'European euro', 'EUR'),
('Czechia', 'Czech koruna', 'CZK'),
('Denmark', 'Danish krone', 'DKK'),
('Djibouti', 'Djiboutian franc', 'DJF'),
('Dominica', 'East Caribbean dollar', 'XCD'),
('Dominican Republic', 'Dominican peso', 'DOP'),
('Ecuador', 'United States dollar', 'USD'),
('Egypt', 'Egyptian pound', 'EGP'),
('El Salvador', 'United States dollar', 'USD'),
('Equatorial Guinea', 'Central African CFA franc', 'XAF'),
('Eritrea', 'Eritrean nakfa', 'ERN'),
('Estonia', 'European euro', 'EUR'),
('Eswatini (formerly Swaziland)', 'Swazi lilangeni', 'SZL'),
('Ethiopia', 'Ethiopian birr', 'ETB'),
('Falkland Islands (UK)', 'Falkland Islands pound', 'FKP'),
('Faroe Islands (Denmark)', 'Faroese krona', '-'),
('Fiji', 'Fijian dollar', 'FJD'),
('Finland', 'European euro', 'EUR'),
('France', 'European euro', 'EUR'),
('French Guiana (France)', 'European euro', 'EUR'),
('French Polynesia (France)', 'CFP franc', 'XPF'),
('Gabon', 'Central African CFA franc', 'XAF'),
('Gambia', 'Gambian dalasi', 'GMD'),
('Georgia', 'Georgian lari', 'GEL'),
('Germany', 'European euro', 'EUR'),
('Ghana', 'Ghanaian cedi', 'GHS'),
('Gibraltar (UK)', 'Gibraltar pound', 'GIP'),
('Greece', 'European euro', 'EUR'),
('Greenland (Denmark)', 'Danish krone', 'DKK'),
('Grenada', 'East Caribbean dollar', 'XCD'),
('Guadeloupe (France)', 'European euro', 'EUR'),
('Guam (USA)', 'United States dollar', 'USD'),
('Guatemala', 'Guatemalan quetzal', 'GTQ'),
('Guernsey (UK)', 'Guernsey Pound', 'GGP'),
('Guinea', 'Guinean franc', 'GNF'),
('Guinea-Bissau', 'West African CFA franc', 'XOF'),
('Guyana', 'Guyanese dollar', 'GYD'),
('Haiti', 'Haitian gourde', 'HTG'),
('Honduras', 'Honduran lempira', 'HNL'),
('Hong Kong (China)', 'Hong Kong dollar', 'HKD'),
('Hungary', 'Hungarian forint', 'HUF'),
('Iceland', 'Icelandic krona', 'ISK'),
('India', 'Indian rupee', 'INR'),
('Indonesia', 'Indonesian rupiah', 'IDR'),
('International Monetary Fund (IMF)', 'SDR (Special Drawing Right)', 'XDR'),
('Iran', 'Iranian rial', 'IRR'),
('Iraq', 'Iraqi dinar', 'IQD'),
('Ireland', 'European euro', 'EUR'),
('Isle of Man (UK)', 'Manx pound', 'IMP'),
('Israel', 'Israeli new shekel', 'ILS'),
('Italy', 'European euro', 'EUR'),
('Jamaica', 'Jamaican dollar', 'JMD'),
('Japan', 'Japanese yen', 'JPY'),
('Jersey (UK)', 'Jersey pound', 'JEP'),
('Jordan', 'Jordanian dinar', 'JOD'),
('Kazakhstan', 'Kazakhstani tenge', 'KZT'),
('Kenya', 'Kenyan shilling', 'KES'),
('Kiribati', 'Australian dollar', 'AUD'),
('Kosovo', 'European euro', 'EUR'),
('Kuwait', 'Kuwaiti dinar', 'KWD'),
('Kyrgyzstan', 'Kyrgyzstani som', 'KGS'),
('Laos', 'Lao kip', 'LAK'),
('Latvia', 'European euro', 'EUR'),
('Lebanon', 'Lebanese pound', 'LBP'),
('Lesotho', 'Lesotho loti', 'LSL'),
('Liberia', 'Liberian dollar', 'LRD'),
('Libya', 'Libyan dinar', 'LYD'),
('Liechtenstein', 'Swiss franc', 'CHF'),
('Lithuania', 'European euro', 'EUR'),
('Luxembourg', 'European euro', 'EUR'),
('Macau (China)', 'Macanese pataca', 'MOP'),
('Madagascar', 'Malagasy ariary', 'MGA'),
('Malawi', 'Malawian kwacha', 'MWK'),
('Malaysia', 'Malaysian ringgit', 'MYR'),
('Maldives', 'Maldivian rufiyaa', 'MVR'),
('Mali', 'West African CFA franc', 'XOF'),
('Malta', 'European euro', 'EUR'),
('Marshall Islands', 'United States dollar', 'USD'),
('Martinique (France)', 'European euro', 'EUR'),
('Mauritania', 'Mauritanian ouguiya', 'MRU'),
('Mauritius', 'Mauritian rupee', 'MUR'),
('Mayotte (France)', 'European euro', 'EUR'),
('Mexico', 'Mexican peso', 'MXN'),
('Micronesia', 'United States dollar', 'USD'),
('Moldova', 'Moldovan leu', 'MDL'),
('Monaco', 'European euro', 'EUR'),
('Mongolia', 'Mongolian tugrik', 'MNT'),
('Montenegro', 'European euro', 'EUR'),
('Montserrat (UK)', 'East Caribbean dollar', 'XCD'),
('Morocco', 'Moroccan dirham', 'MAD'),
('Mozambique', 'Mozambican metical', 'MZN'),
('Myanmar (formerly Burma)', 'Myanmar kyat', 'MMK'),
('Namibia', 'Namibian dollar', 'NAD'),
('Nauru', 'Australian dollar', 'AUD'),
('Nepal', 'Nepalese rupee', 'NPR'),
('Netherlands', 'European euro', 'EUR'),
('New Caledonia (France)', 'CFP franc', 'XPF'),
('New Zealand', 'New Zealand dollar', 'NZD'),
('Nicaragua', 'Nicaraguan cordoba', 'NIO'),
('Niger', 'West African CFA franc', 'XOF'),
('Nigeria', 'Nigerian naira', 'NGN'),
('Niue (New Zealand)', 'New Zealand dollar', 'NZD'),
('Norfolk Island (Australia)', 'Australian dollar', 'AUD'),
('Northern Mariana Islands (USA)', 'United States dollar', 'USD'),
('North Korea', 'North Korean won', 'KPW'),
('North Macedonia (formerly Macedonia)', 'Macedonian denar', 'MKD'),
('Norway', 'Norwegian krone', 'NOK'),
('Oman', 'Omani rial', 'OMR'),
('Pakistan', 'Pakistani rupee', 'PKR'),
('Palau', 'United States dollar', 'USD'),
('Palestine', 'Israeli new shekel', 'ILS'),
('Panama', 'United States dollar', 'USD'),
('Papua New Guinea', 'Papua New Guinean kina', 'PGK'),
('Paraguay', 'Paraguayan guarani', 'PYG'),
('Peru', 'Peruvian sol', 'PEN'),
('Philippines', 'Philippine peso', 'PHP'),
('Pitcairn Islands (UK)', 'New Zealand dollar', 'NZD'),
('Poland', 'Polish zloty', 'PLN'),
('Portugal', 'European euro', 'EUR'),
('Puerto Rico (USA)', 'United States dollar', 'USD'),
('Qatar', 'Qatari riyal', 'QAR'),
('Reunion (France)', 'European euro', 'EUR'),
('Romania', 'Romanian leu', 'RON'),
('Russia', 'Russian ruble', 'RUB'),
('Rwanda', 'Rwandan franc', 'RWF'),
('Saba (Netherlands)', 'United States dollar', 'USD'),
('Saint Barthelemy (France)', 'European euro', 'EUR'),
('Saint Helena (UK)', 'Saint Helena pound', 'SHP'),
('Saint Kitts and Nevis', 'East Caribbean dollar', 'XCD'),
('Saint Lucia', 'East Caribbean dollar', 'XCD'),
('Saint Martin (France)', 'European euro', 'EUR'),
('Saint Pierre and Miquelon (France)', 'European euro', 'EUR'),
('Saint Vincent and the Grenadines', 'East Caribbean dollar', 'XCD'),
('Samoa', 'Samoan tala', 'WST'),
('San Marino', 'European euro', 'EUR'),
('Sao Tome and Principe', 'Sao Tome and Principe dobra', 'STN'),
('Saudi Arabia', 'Saudi Arabian riyal', 'SAR'),
('Senegal', 'West African CFA franc', 'XOF'),
('Serbia', 'Serbian dinar', 'RSD'),
('Seychelles', 'Seychellois rupee', 'SCR'),
('Sierra Leone', 'Sierra Leonean leone', 'SLL'),
('Singapore', 'Singapore dollar', 'SGD'),
('Sint Eustatius (Netherlands)', 'United States dollar', 'USD'),
('Sint Maarten (Netherlands)', 'Netherlands Antillean guilder', 'ANG'),
('Slovakia', 'European euro', 'EUR'),
('Slovenia', 'European euro', 'EUR'),
('Solomon Islands', 'Solomon Islands dollar', 'SBD'),
('Somalia', 'Somali shilling', 'SOS'),
('South Africa', 'South African rand', 'ZAR'),
('South Georgia Island (UK)', 'Pound sterling', 'GBP'),
('South Korea', 'South Korean won', 'KRW'),
('South Sudan', 'South Sudanese pound', 'SSP'),
('Spain', 'European euro', 'EUR'),
('Sri Lanka', 'Sri Lankan rupee', 'LKR'),
('Sudan', 'Sudanese pound', 'SDG'),
('Suriname', 'Surinamese dollar', 'SRD'),
('Svalbard and Jan Mayen (Norway)', 'Norwegian krone', 'NOK'),
('Sweden', 'Swedish krona', 'SEK'),
('Switzerland', 'Swiss franc', 'CHF'),
('Syria', 'Syrian pound', 'SYP'),
('Taiwan', 'New Taiwan dollar', 'TWD'),
('Tajikistan', 'Tajikistani somoni', 'TJS'),
('Tanzania', 'Tanzanian shilling', 'TZS'),
('Thailand', 'Thai baht', 'THB'),
('Timor-Leste', 'United States dollar', 'USD'),
('Togo', 'West African CFA franc', 'XOF'),
('Tokelau (New Zealand)', 'New Zealand dollar', 'NZD'),
('Tonga', 'Tongan pa\'anga', 'TOP'),
('Trinidad and Tobago', 'Trinidad and Tobago dollar', 'TTD'),
('Tristan da Cunha (UK)', 'Pound sterling', 'GBP'),
('Tunisia', 'Tunisian dinar', 'TND'),
('Turkey', 'Turkish lira', 'TRY'),
('Turkmenistan', 'Turkmen manat', 'TMT'),
('Turks and Caicos Islands (UK)', 'United States dollar', 'USD'),
('Tuvalu', 'Australian dollar', 'AUD'),
('Uganda', 'Ugandan shilling', 'UGX'),
('Ukraine', 'Ukrainian hryvnia', 'UAH'),
('United Arab Emirates', 'UAE dirham', 'AED'),
('United Kingdom', 'Pound sterling', 'GBP'),
('United States of America', 'United States dollar', 'USD'),
('Uruguay', 'Uruguayan peso', 'UYU'),
('US Virgin Islands (USA)', 'United States dollar', 'USD'),
('Uzbekistan', 'Uzbekistani som', 'UZS'),
('Vanuatu', 'Vanuatu vatu', 'VUV'),
('Vatican City (Holy See)', 'European euro', 'EUR'),
('Venezuela', 'Venezuelan bolivar', 'VES'),
('Vietnam', 'Vietnamese dong', 'VND'),
('Wake Island (USA)', 'United States dollar', 'USD'),
('Wallis and Futuna (France)', 'CFP franc', 'XPF'),
('Yemen', 'Yemeni rial', 'YER'),
('Zambia', 'Zambian kwacha', 'ZMW'),
('Zimbabwe', 'United States dollar', 'USD');

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
