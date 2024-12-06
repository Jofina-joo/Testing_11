-- phpMyAdmin SQL Dump
-- version 4.4.15.10
-- https://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 14, 2023 at 10:01 AM
-- Server version: 8.0.27
-- PHP Version: 7.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mobile_marketing`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_log`
--

CREATE TABLE IF NOT EXISTS `api_log` (
  `api_log_id` int NOT NULL,
  `user_id` int NOT NULL,
  `api_url` varchar(50) NOT NULL,
  `ip_address` varchar(50) NOT NULL,
  `request_id` varchar(30) NOT NULL,
  `response_status` char(1) DEFAULT NULL,
  `response_comments` varchar(100) DEFAULT NULL,
  `api_log_status` char(1) NOT NULL,
  `api_log_entry_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `countries_master`
--

CREATE TABLE IF NOT EXISTS `countries_master` (
  `country_id` int NOT NULL,
  `country_code` varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `country_name` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `phone_code` int NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=249 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;

--
-- Dumping data for table `countries_master`
--

INSERT INTO `countries_master` (`country_id`, `country_code`, `country_name`, `phone_code`) VALUES
(1, 'AF', 'Afghanistan', 93),
(2, 'AL', 'Albania', 355),
(3, 'DZ', 'Algeria', 213),
(4, 'AS', 'American Samoa', 1684),
(5, 'AD', 'Andorra', 376),
(6, 'AO', 'Angola', 244),
(7, 'AI', 'Anguilla', 1264),
(8, 'AQ', 'Antarctica', 0),
(9, 'AG', 'Antigua And Barbuda', 1268),
(10, 'AR', 'Argentina', 54),
(11, 'AM', 'Armenia', 374),
(12, 'AW', 'Aruba', 297),
(13, 'AU', 'Australia', 61),
(14, 'AT', 'Austria', 43),
(15, 'AZ', 'Azerbaijan', 994),
(16, 'BS', 'Bahamas The', 1242),
(17, 'BH', 'Bahrain', 973),
(18, 'BD', 'Bangladesh', 880),
(19, 'BB', 'Barbados', 1246),
(20, 'BY', 'Belarus', 375),
(21, 'BE', 'Belgium', 32),
(22, 'BZ', 'Belize', 501),
(23, 'BJ', 'Benin', 229),
(24, 'BM', 'Bermuda', 1441),
(25, 'BT', 'Bhutan', 975),
(26, 'BO', 'Bolivia', 591),
(27, 'BA', 'Bosnia and Herzegovina', 387),
(28, 'BW', 'Botswana', 267),
(29, 'BV', 'Bouvet Island', 0),
(30, 'BR', 'Brazil', 55),
(31, 'IO', 'British Indian Ocean Territory', 246),
(32, 'BN', 'Brunei', 673),
(33, 'BG', 'Bulgaria', 359),
(34, 'BF', 'Burkina Faso', 226),
(35, 'BI', 'Burundi', 257),
(36, 'KH', 'Cambodia', 855),
(37, 'CM', 'Cameroon', 237),
(38, 'CA', 'Canada', 1),
(39, 'CV', 'Cape Verde', 238),
(40, 'KY', 'Cayman Islands', 1345),
(41, 'CF', 'Central African Republic', 236),
(42, 'TD', 'Chad', 235),
(43, 'CL', 'Chile', 56),
(44, 'CN', 'China', 86),
(45, 'CX', 'Christmas Island', 61),
(46, 'CC', 'Cocos (Keeling) Islands', 672),
(47, 'CO', 'Colombia', 57),
(48, 'KM', 'Comoros', 269),
(49, 'CG', 'Republic Of The Congo', 242),
(50, 'CD', 'Democratic Republic Of The Congo', 242),
(51, 'CK', 'Cook Islands', 682),
(52, 'CR', 'Costa Rica', 506),
(53, 'CI', 'Cote D''Ivoire (Ivory Coast)', 225),
(54, 'HR', 'Croatia (Hrvatska)', 385),
(55, 'CU', 'Cuba', 53),
(56, 'CY', 'Cyprus', 357),
(57, 'CZ', 'Czech Republic', 420),
(58, 'DK', 'Denmark', 45),
(59, 'DJ', 'Djibouti', 253),
(60, 'DM', 'Dominica', 1767),
(61, 'DO', 'Dominican Republic', 1809),
(62, 'TP', 'East Timor', 670),
(63, 'EC', 'Ecuador', 593),
(64, 'EG', 'Egypt', 20),
(65, 'SV', 'El Salvador', 503),
(66, 'GQ', 'Equatorial Guinea', 240),
(67, 'ER', 'Eritrea', 291),
(68, 'EE', 'Estonia', 372),
(69, 'ET', 'Ethiopia', 251),
(70, 'XA', 'External Territories of Australia', 61),
(71, 'FK', 'Falkland Islands', 500),
(72, 'FO', 'Faroe Islands', 298),
(73, 'FJ', 'Fiji Islands', 679),
(74, 'FI', 'Finland', 358),
(75, 'FR', 'France', 33),
(76, 'GF', 'French Guiana', 594),
(77, 'PF', 'French Polynesia', 689),
(78, 'TF', 'French Southern Territories', 0),
(79, 'GA', 'Gabon', 241),
(80, 'GM', 'Gambia The', 220),
(81, 'GE', 'Georgia', 995),
(82, 'DE', 'Germany', 49),
(83, 'GH', 'Ghana', 233),
(84, 'GI', 'Gibraltar', 350),
(85, 'GR', 'Greece', 30),
(86, 'GL', 'Greenland', 299),
(87, 'GD', 'Grenada', 1473),
(88, 'GP', 'Guadeloupe', 590),
(89, 'GU', 'Guam', 1671),
(90, 'GT', 'Guatemala', 502),
(91, 'XU', 'Guernsey and Alderney', 44),
(92, 'GN', 'Guinea', 224),
(93, 'GW', 'Guinea-Bissau', 245),
(94, 'GY', 'Guyana', 592),
(95, 'HT', 'Haiti', 509),
(96, 'HM', 'Heard and McDonald Islands', 0),
(97, 'HN', 'Honduras', 504),
(98, 'HK', 'Hong Kong S.A.R.', 852),
(99, 'HU', 'Hungary', 36),
(100, 'IS', 'Iceland', 354),
(101, 'IN', 'India', 91),
(102, 'ID', 'Indonesia', 62),
(103, 'IR', 'Iran', 98),
(104, 'IQ', 'Iraq', 964),
(105, 'IE', 'Ireland', 353),
(106, 'IL', 'Israel', 972),
(107, 'IT', 'Italy', 39),
(108, 'JM', 'Jamaica', 1876),
(109, 'JP', 'Japan', 81),
(110, 'XJ', 'Jersey', 44),
(111, 'JO', 'Jordan', 962),
(112, 'KZ', 'Kazakhstan', 7),
(113, 'KE', 'Kenya', 254),
(114, 'KI', 'Kiribati', 686),
(115, 'KP', 'Korea North', 850),
(116, 'KR', 'Korea South', 82),
(117, 'KW', 'Kuwait', 965),
(118, 'KG', 'Kyrgyzstan', 996),
(119, 'LA', 'Laos', 856),
(120, 'LV', 'Latvia', 371),
(121, 'LB', 'Lebanon', 961),
(122, 'LS', 'Lesotho', 266),
(123, 'LR', 'Liberia', 231),
(124, 'LY', 'Libya', 218),
(125, 'LI', 'Liechtenstein', 423),
(126, 'LT', 'Lithuania', 370),
(127, 'LU', 'Luxembourg', 352),
(128, 'MO', 'Macau S.A.R.', 853),
(129, 'MK', 'Macedonia', 389),
(130, 'MG', 'Madagascar', 261),
(131, 'MW', 'Malawi', 265),
(132, 'MY', 'Malaysia', 60),
(133, 'MV', 'Maldives', 960),
(134, 'ML', 'Mali', 223),
(135, 'MT', 'Malta', 356),
(136, 'XM', 'Man (Isle of)', 44),
(137, 'MH', 'Marshall Islands', 692),
(138, 'MQ', 'Martinique', 596),
(139, 'MR', 'Mauritania', 222),
(140, 'MU', 'Mauritius', 230),
(141, 'YT', 'Mayotte', 269),
(142, 'MX', 'Mexico', 52),
(143, 'FM', 'Micronesia', 691),
(144, 'MD', 'Moldova', 373),
(145, 'MC', 'Monaco', 377),
(146, 'MN', 'Mongolia', 976),
(147, 'MS', 'Montserrat', 1664),
(148, 'MA', 'Morocco', 212),
(149, 'MZ', 'Mozambique', 258),
(150, 'MM', 'Myanmar', 95),
(151, 'NA', 'Namibia', 264),
(152, 'NR', 'Nauru', 674),
(153, 'NP', 'Nepal', 977),
(154, 'AN', 'Netherlands Antilles', 599),
(155, 'NL', 'Netherlands The', 31),
(156, 'NC', 'New Caledonia', 687),
(157, 'NZ', 'New Zealand', 64),
(158, 'NI', 'Nicaragua', 505),
(159, 'NE', 'Niger', 227),
(160, 'NG', 'Nigeria', 234),
(161, 'NU', 'Niue', 683),
(162, 'NF', 'Norfolk Island', 672),
(163, 'MP', 'Northern Mariana Islands', 1670),
(164, 'NO', 'Norway', 47),
(165, 'OM', 'Oman', 968),
(166, 'PK', 'Pakistan', 92),
(167, 'PW', 'Palau', 680),
(168, 'PS', 'Palestinian Territory Occupied', 970),
(169, 'PA', 'Panama', 507),
(170, 'PG', 'Papua new Guinea', 675),
(171, 'PY', 'Paraguay', 595),
(172, 'PE', 'Peru', 51),
(173, 'PH', 'Philippines', 63),
(174, 'PN', 'Pitcairn Island', 0),
(175, 'PL', 'Poland', 48),
(176, 'PT', 'Portugal', 351),
(177, 'PR', 'Puerto Rico', 1787),
(178, 'QA', 'Qatar', 974),
(179, 'RE', 'Reunion', 262),
(180, 'RO', 'Romania', 40),
(181, 'RU', 'Russia', 70),
(182, 'RW', 'Rwanda', 250),
(183, 'SH', 'Saint Helena', 290),
(184, 'KN', 'Saint Kitts And Nevis', 1869),
(185, 'LC', 'Saint Lucia', 1758),
(186, 'PM', 'Saint Pierre and Miquelon', 508),
(187, 'VC', 'Saint Vincent And The Grenadines', 1784),
(188, 'WS', 'Samoa', 684),
(189, 'SM', 'San Marino', 378),
(190, 'ST', 'Sao Tome and Principe', 239),
(191, 'SA', 'Saudi Arabia', 966),
(192, 'SN', 'Senegal', 221),
(193, 'RS', 'Serbia', 381),
(194, 'SC', 'Seychelles', 248),
(195, 'SL', 'Sierra Leone', 232),
(196, 'SG', 'Singapore', 65),
(197, 'SK', 'Slovakia', 421),
(198, 'SI', 'Slovenia', 386),
(199, 'XG', 'Smaller Territories of the UK', 44),
(200, 'SB', 'Solomon Islands', 677),
(201, 'SO', 'Somalia', 252),
(202, 'ZA', 'South Africa', 27),
(203, 'GS', 'South Georgia', 0),
(204, 'SS', 'South Sudan', 211),
(205, 'ES', 'Spain', 34),
(206, 'LK', 'Sri Lanka', 94),
(207, 'SD', 'Sudan', 249),
(208, 'SR', 'Suriname', 597),
(209, 'SJ', 'Svalbard And Jan Mayen Islands', 47),
(210, 'SZ', 'Swaziland', 268),
(211, 'SE', 'Sweden', 46),
(212, 'CH', 'Switzerland', 41),
(213, 'SY', 'Syria', 963),
(214, 'TW', 'Taiwan', 886),
(215, 'TJ', 'Tajikistan', 992),
(216, 'TZ', 'Tanzania', 255),
(217, 'TH', 'Thailand', 66),
(218, 'TG', 'Togo', 228),
(219, 'TK', 'Tokelau', 690),
(220, 'TO', 'Tonga', 676),
(221, 'TT', 'Trinidad And Tobago', 1868),
(222, 'TN', 'Tunisia', 216),
(223, 'TR', 'Turkey', 90),
(224, 'TM', 'Turkmenistan', 7370),
(225, 'TC', 'Turks And Caicos Islands', 1649),
(226, 'TV', 'Tuvalu', 688),
(227, 'UG', 'Uganda', 256),
(228, 'UA', 'Ukraine', 380),
(229, 'AE', 'United Arab Emirates', 971),
(230, 'GB', 'United Kingdom', 44),
(231, 'US', 'United States', 1),
(232, 'UM', 'United States Minor Outlying Islands', 1),
(233, 'UY', 'Uruguay', 598),
(234, 'UZ', 'Uzbekistan', 998),
(235, 'VU', 'Vanuatu', 678),
(236, 'VA', 'Vatican City State (Holy See)', 39),
(237, 'VE', 'Venezuela', 58),
(238, 'VN', 'Vietnam', 84),
(239, 'VG', 'Virgin Islands (British)', 1284),
(240, 'VI', 'Virgin Islands (US)', 1340),
(241, 'WF', 'Wallis And Futuna Islands', 681),
(242, 'EH', 'Western Sahara', 212),
(243, 'YE', 'Yemen', 967),
(244, 'YU', 'Yugoslavia', 38),
(245, 'ZM', 'Zambia', 260),
(246, 'ZW', 'Zimbabwe', 263);

-- --------------------------------------------------------

--
-- Table structure for table `rights_master`
--

CREATE TABLE IF NOT EXISTS `rights_master` (
  `rights_id` int NOT NULL,
  `rights_name` varchar(20) NOT NULL,
  `rights_short_name` varchar(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `rights_status` char(1) NOT NULL,
  `rights_entry_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `rights_master`
--

INSERT INTO `rights_master` (`rights_id`, `rights_name`, `rights_short_name`, `rights_status`, `rights_entry_date`) VALUES
(1, 'WHASTAPP', 'WATSP', 'Y', '2023-09-08 09:07:56'),
(2, 'GSM SMS', 'GSSMS', 'Y', '2023-09-08 09:08:56'),
(3, 'TELEGRAM', 'TLGRM', 'Y', '2023-09-08 09:09:56');

-- --------------------------------------------------------

--
-- Table structure for table `sender_id_limits`
--

CREATE TABLE IF NOT EXISTS `sender_id_limits` (
  `sender_limit_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `user_rights_id` int NOT NULL,
  `available_credits` int NOT NULL,
  `daily_used_credits` int DEFAULT NULL,
  `total_used_credits` int DEFAULT NULL,
  `sender_limit_status` char(1) NOT NULL,
  `sender_limit_entry_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `sender_id_master`
--

CREATE TABLE IF NOT EXISTS `sender_id_master` (
  `sender_id` int NOT NULL,
  `user_id` int NOT NULL,
  `mobile_no` varchar(15) NOT NULL,
  `device_token` varchar(200) NOT NULL,
  `is_qr_code` char(1) NOT NULL,
  `sender_id_status` char(1) NOT NULL,
  `sender_id_entry_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `sender_id_master`
--

INSERT INTO `sender_id_master` (`sender_id`, `user_id`, `mobile_no`, `device_token`, `is_qr_code`, `sender_id_status`, `sender_id_entry_date`) VALUES
(1, 1, '919894606748', '919894606748', 'Y', 'Y', '2023-09-08 09:54:53');

-- --------------------------------------------------------

--
-- Table structure for table `user_credits`
--

CREATE TABLE IF NOT EXISTS `user_credits` (
  `user_credits_id` int NOT NULL,
  `user_id` int NOT NULL,
  `total_credits` bigint NOT NULL,
  `used_credits` bigint DEFAULT NULL,
  `available_credits` bigint DEFAULT NULL,
  `expiry_date` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `uc_status` char(1) NOT NULL,
  `uc_entry_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `user_credits`
--

INSERT INTO `user_credits` (`user_credits_id`, `user_id`, `total_credits`, `used_credits`, `available_credits`, `expiry_date`, `uc_status`, `uc_entry_date`) VALUES
(1, 1, 100000000000, 0, 0, '2024-03-31 07:13:17', 'Y', '2023-09-08 09:09:08'),
(2, 2, 1000, 0, 0, '2024-01-31 04:31:10', 'Y', '2023-09-08 09:11:08'),
(3, 3, 10, 0, 0, '2023-12-31 04:30:00', 'Y', '2023-09-08 09:15:08');

-- --------------------------------------------------------

--
-- Table structure for table `user_credits_log`
--

CREATE TABLE IF NOT EXISTS `user_credits_log` (
  `user_credits_log_id` int NOT NULL,
  `user_credits_id` int NOT NULL,
  `parent_id` int NOT NULL,
  `user_id` int NOT NULL,
  `provided_credits_count` bigint NOT NULL,
  `credit_comments` varchar(100) NOT NULL,
  `uc_log_status` char(1) NOT NULL,
  `uc_log_entry_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `user_credits_log`
--

INSERT INTO `user_credits_log` (`user_credits_log_id`, `user_credits_id`, `parent_id`, `user_id`, `provided_credits_count`, `credit_comments`, `uc_log_status`, `uc_log_entry_date`) VALUES
(1, 1, 1, 1, 100000000000, 'ADMIN HAS UNLIMITED MESSAGE CREDITS', 'Y', '2023-09-08 09:21:46'),
(2, 2, 1, 2, 1000, 'ADMIN 1000 MESSAGE CREDITS TO USER_1', 'Y', '2023-09-08 09:24:46'),
(3, 3, 1, 3, 10, 'ADMIN 10 MESSAGE CREDITS TO TEST_1', 'Y', '2023-09-08 09:28:46');

-- --------------------------------------------------------

--
-- Table structure for table `user_log`
--

CREATE TABLE IF NOT EXISTS `user_log` (
  `user_log_id` int NOT NULL,
  `user_id` int NOT NULL,
  `ip_address` varchar(50) NOT NULL,
  `login_date` date NOT NULL,
  `login_time` timestamp NOT NULL,
  `logout_time` timestamp NULL DEFAULT NULL,
  `user_log_status` char(1) NOT NULL,
  `user_log_entry_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `user_management`
--

CREATE TABLE IF NOT EXISTS `user_management` (
  `user_id` int NOT NULL,
  `user_master_id` int NOT NULL,
  `parent_id` int NOT NULL,
  `user_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `user_short_name` varchar(10) DEFAULT NULL,
  `api_key` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `login_id` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `login_password` varchar(100) NOT NULL,
  `user_email` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `user_mobile` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `user_bearer_token` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `user_status` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `user_entry_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;

--
-- Dumping data for table `user_management`
--

INSERT INTO `user_management` (`user_id`, `user_master_id`, `parent_id`, `user_name`, `user_short_name`, `api_key`, `login_id`, `login_password`, `user_email`, `user_mobile`, `user_bearer_token`, `user_status`, `user_entry_date`) VALUES
(1, 1, 1, 'admin', 'adm1', 'UOJ5HBT7IV1AXQ8', 'admin', 'e58a3754522a05c1ff4d231f8e8cc1bd', 'admin@admin.com', '9000090000', NULL, 'Y', '2023-09-08 07:58:13'),
(2, 2, 1, 'user_1', 'usr1', 'AJLHDFUQMR7WN9I', 'user_1', 'e58a3754522a05c1ff4d231f8e8cc1bd', 'user_1@admin.com', '9000090001', NULL, 'Y', '2023-09-08 07:58:13'),
(3, 3, 1, 'test_1', 'tst1', 'G5XSYHP60ENM91I', 'test_1', 'e58a3754522a05c1ff4d231f8e8cc1bd', 'test_1@admin.com', '9000090002', NULL, 'Y', '2023-09-08 07:58:13');

-- --------------------------------------------------------

--
-- Table structure for table `user_master`
--

CREATE TABLE IF NOT EXISTS `user_master` (
  `user_master_id` int NOT NULL,
  `user_type` varchar(20) NOT NULL,
  `user_details` varchar(50) NOT NULL,
  `user_master_status` char(1) NOT NULL,
  `um_entry_date` timestamp NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `user_master`
--

INSERT INTO `user_master` (`user_master_id`, `user_type`, `user_details`, `user_master_status`, `um_entry_date`) VALUES
(1, 'Admin', 'Admin User', 'Y', '2023-09-08 09:20:17'),
(2, 'User', 'User', 'Y', '2023-09-08 09:20:49'),
(3, 'Test', 'Test User', 'Y', '2023-09-08 09:20:54');

-- --------------------------------------------------------

--
-- Table structure for table `user_rights`
--

CREATE TABLE IF NOT EXISTS `user_rights` (
  `user_rights_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rights_id` int NOT NULL,
  `ur_status` char(1) NOT NULL,
  `ur_entry_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `user_rights`
--

INSERT INTO `user_rights` (`user_rights_id`, `user_id`, `rights_id`, `ur_status`, `ur_entry_date`) VALUES
(1, 1, 1, 'Y', '2023-09-08 12:42:29'),
(2, 1, 2, 'Y', '2023-09-08 12:43:29'),
(3, 1, 3, 'Y', '2023-09-08 12:44:29'),
(4, 2, 1, 'Y', '2023-09-08 12:45:29'),
(5, 2, 2, 'Y', '2023-09-08 12:46:29'),
(6, 3, 1, 'Y', '2023-09-08 12:47:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `api_log`
--
ALTER TABLE `api_log`
  ADD PRIMARY KEY (`api_log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `countries_master`
--
ALTER TABLE `countries_master`
  ADD PRIMARY KEY (`country_id`);

--
-- Indexes for table `rights_master`
--
ALTER TABLE `rights_master`
  ADD PRIMARY KEY (`rights_id`);

--
-- Indexes for table `sender_id_limits`
--
ALTER TABLE `sender_id_limits`
  ADD PRIMARY KEY (`sender_limit_id`),
  ADD KEY `sender_id` (`sender_id`,`user_rights_id`);

--
-- Indexes for table `sender_id_master`
--
ALTER TABLE `sender_id_master`
  ADD PRIMARY KEY (`sender_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_credits`
--
ALTER TABLE `user_credits`
  ADD PRIMARY KEY (`user_credits_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_credits_log`
--
ALTER TABLE `user_credits_log`
  ADD PRIMARY KEY (`user_credits_log_id`),
  ADD KEY `parent_id` (`parent_id`,`user_id`);

--
-- Indexes for table `user_log`
--
ALTER TABLE `user_log`
  ADD PRIMARY KEY (`user_log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_management`
--
ALTER TABLE `user_management`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `user_master_id` (`user_master_id`,`parent_id`),
  ADD KEY `user_master_id_2` (`user_master_id`,`parent_id`);

--
-- Indexes for table `user_master`
--
ALTER TABLE `user_master`
  ADD PRIMARY KEY (`user_master_id`);

--
-- Indexes for table `user_rights`
--
ALTER TABLE `user_rights`
  ADD PRIMARY KEY (`user_rights_id`),
  ADD KEY `user_id` (`user_id`,`rights_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `api_log`
--
ALTER TABLE `api_log`
  MODIFY `api_log_id` int NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `countries_master`
--
ALTER TABLE `countries_master`
  MODIFY `country_id` int NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=249;
--
-- AUTO_INCREMENT for table `rights_master`
--
ALTER TABLE `rights_master`
  MODIFY `rights_id` int NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `sender_id_limits`
--
ALTER TABLE `sender_id_limits`
  MODIFY `sender_limit_id` int NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sender_id_master`
--
ALTER TABLE `sender_id_master`
  MODIFY `sender_id` int NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `user_credits`
--
ALTER TABLE `user_credits`
  MODIFY `user_credits_id` int NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `user_credits_log`
--
ALTER TABLE `user_credits_log`
  MODIFY `user_credits_log_id` int NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `user_log`
--
ALTER TABLE `user_log`
  MODIFY `user_log_id` int NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_management`
--
ALTER TABLE `user_management`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `user_master`
--
ALTER TABLE `user_master`
  MODIFY `user_master_id` int NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `user_rights`
--
ALTER TABLE `user_rights`
  MODIFY `user_rights_id` int NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
