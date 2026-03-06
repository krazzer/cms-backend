-- ----------------------------
-- Table structure for cms_analytics_day
-- ----------------------------
DROP TABLE IF EXISTS `cms_analytics_day`;
CREATE TABLE `cms_analytics_day` (
  `date` date NOT NULL,
  `visits` int(11) NOT NULL DEFAULT 0,
  `unique_visits` int(11) NOT NULL,
  PRIMARY KEY (`date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Table structure for cms_analytics_metric
-- ----------------------------
DROP TABLE IF EXISTS `cms_analytics_metric`;
CREATE TABLE `cms_analytics_metric` (
  `date` date NOT NULL,
  `type` enum('source','os','page','browser','location','resolutionDesktop','resolutionTablet','resolutionMobile') NOT NULL DEFAULT 'source',
  `value` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `visits` int(11) NOT NULL,
  PRIMARY KEY (`date`,`type`,`value`) USING BTREE,
  KEY `date` (`date`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `value` (`value`) USING BTREE,
  KEY `visits` (`visits`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;