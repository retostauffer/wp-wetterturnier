-- phpMyAdmin SQL Dump
-- version 4.0.10
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 01, 2020 at 01:36 AM
-- Server version: 5.1.73-log
-- PHP Version: 7.1.23

--
-- demo databases
--
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `wpwt`
--

-- --------------------------------------------------------

--
-- Table structure for table `wp_wetterturnier_api`
--
-- Creation: Oct 24, 2018 at 06:33 AM
--

CREATE TABLE IF NOT EXISTS `wp_wetterturnier_api` (
  `ID` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `APIKEY` varchar(20) NOT NULL,
  `APITYPE` enum('obslive','obsarchive','bets') NOT NULL,
  `APICONFIG` varchar(100) NOT NULL,
  `ISPUBLIC` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `description` varchar(200) NOT NULL,
  `since` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `until` int(11) DEFAULT NULL,
  `active` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `APIKEY` (`APIKEY`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=34 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wetterturnier_bets`
--

CREATE TABLE IF NOT EXISTS `wp_wetterturnier_bets` (
  `userID` bigint(20) unsigned NOT NULL,
  `cityID` smallint(5) unsigned NOT NULL,
  `paramID` smallint(5) unsigned NOT NULL,
  `tdate` smallint(5) unsigned NOT NULL,
  `betdate` smallint(5) unsigned NOT NULL,
  `value` smallint(6) NOT NULL,
  `points` float DEFAULT NULL,
  `placed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `placedby` bigint(20) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `userID` (`userID`,`cityID`,`paramID`,`tdate`,`betdate`),
  KEY `wp_wetterturnier_bets_idx_tournamentdate` (`tdate`),
  KEY `wp_wetterturnier_bets_idx_betdate` (`betdate`),
  KEY `wp_wetterturnier_bets_idx_cityID` (`cityID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
/*!50100 PARTITION BY RANGE ( tdate)
(PARTITION part2000 VALUES LESS THAN (10957) ENGINE = InnoDB,
 PARTITION part2001 VALUES LESS THAN (11323) ENGINE = InnoDB,
 PARTITION part2002 VALUES LESS THAN (11688) ENGINE = InnoDB,
 PARTITION part2003 VALUES LESS THAN (12053) ENGINE = InnoDB,
 PARTITION part2004 VALUES LESS THAN (12418) ENGINE = InnoDB,
 PARTITION part2005 VALUES LESS THAN (12784) ENGINE = InnoDB,
 PARTITION part2006 VALUES LESS THAN (13149) ENGINE = InnoDB,
 PARTITION part2007 VALUES LESS THAN (13514) ENGINE = InnoDB,
 PARTITION part2008 VALUES LESS THAN (13879) ENGINE = InnoDB,
 PARTITION part2009 VALUES LESS THAN (14245) ENGINE = InnoDB,
 PARTITION part2010 VALUES LESS THAN (14610) ENGINE = InnoDB,
 PARTITION part2011 VALUES LESS THAN (14975) ENGINE = InnoDB,
 PARTITION part2012 VALUES LESS THAN (15340) ENGINE = InnoDB,
 PARTITION part2013 VALUES LESS THAN (15706) ENGINE = InnoDB,
 PARTITION part2014 VALUES LESS THAN (16071) ENGINE = InnoDB,
 PARTITION part2015 VALUES LESS THAN (16436) ENGINE = InnoDB,
 PARTITION part2016 VALUES LESS THAN (16801) ENGINE = InnoDB,
 PARTITION part2017 VALUES LESS THAN (17167) ENGINE = InnoDB,
 PARTITION part2018 VALUES LESS THAN (17532) ENGINE = InnoDB,
 PARTITION part2019 VALUES LESS THAN (17897) ENGINE = InnoDB,
 PARTITION part2020 VALUES LESS THAN (18262) ENGINE = InnoDB,
 PARTITION part2021 VALUES LESS THAN (18628) ENGINE = InnoDB,
 PARTITION part2022 VALUES LESS THAN (18993) ENGINE = InnoDB,
 PARTITION part2023 VALUES LESS THAN (19358) ENGINE = InnoDB,
 PARTITION part2024 VALUES LESS THAN (19723) ENGINE = InnoDB,
 PARTITION part2025 VALUES LESS THAN (20089) ENGINE = InnoDB,
 PARTITION part2026 VALUES LESS THAN (20454) ENGINE = InnoDB,
 PARTITION part2027 VALUES LESS THAN (20819) ENGINE = InnoDB,
 PARTITION part2028 VALUES LESS THAN (21184) ENGINE = InnoDB,
 PARTITION part2029 VALUES LESS THAN (21550) ENGINE = InnoDB,
 PARTITION part2030 VALUES LESS THAN (21915) ENGINE = InnoDB) */;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wetterturnier_betstat`
--

CREATE TABLE IF NOT EXISTS `wp_wetterturnier_betstat` (
  `userID` int(11) NOT NULL,
  `cityID` smallint(5) unsigned NOT NULL,
  `tdate` smallint(6) NOT NULL,
  `points_d1` float DEFAULT NULL,
  `points_d2` float DEFAULT NULL,
  `points` float DEFAULT NULL,
  `rank` smallint(5) unsigned DEFAULT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `submitted` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `data` (`userID`,`cityID`,`tdate`),
  KEY `wp_wetterturnier_betstat_tdate` (`tdate`),
  KEY `wp_wetterturnier_betstat_cityID` (`cityID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
/*!50100 PARTITION BY RANGE ( tdate)
(PARTITION part2000 VALUES LESS THAN (10957) ENGINE = InnoDB,
 PARTITION part2001 VALUES LESS THAN (11323) ENGINE = InnoDB,
 PARTITION part2002 VALUES LESS THAN (11688) ENGINE = InnoDB,
 PARTITION part2003 VALUES LESS THAN (12053) ENGINE = InnoDB,
 PARTITION part2004 VALUES LESS THAN (12418) ENGINE = InnoDB,
 PARTITION part2005 VALUES LESS THAN (12784) ENGINE = InnoDB,
 PARTITION part2006 VALUES LESS THAN (13149) ENGINE = InnoDB,
 PARTITION part2007 VALUES LESS THAN (13514) ENGINE = InnoDB,
 PARTITION part2008 VALUES LESS THAN (13879) ENGINE = InnoDB,
 PARTITION part2009 VALUES LESS THAN (14245) ENGINE = InnoDB,
 PARTITION part2010 VALUES LESS THAN (14610) ENGINE = InnoDB,
 PARTITION part2011 VALUES LESS THAN (14975) ENGINE = InnoDB,
 PARTITION part2012 VALUES LESS THAN (15340) ENGINE = InnoDB,
 PARTITION part2013 VALUES LESS THAN (15706) ENGINE = InnoDB,
 PARTITION part2014 VALUES LESS THAN (16071) ENGINE = InnoDB,
 PARTITION part2015 VALUES LESS THAN (16436) ENGINE = InnoDB,
 PARTITION part2016 VALUES LESS THAN (16801) ENGINE = InnoDB,
 PARTITION part2017 VALUES LESS THAN (17167) ENGINE = InnoDB,
 PARTITION part2018 VALUES LESS THAN (17532) ENGINE = InnoDB,
 PARTITION part2019 VALUES LESS THAN (17897) ENGINE = InnoDB,
 PARTITION part2020 VALUES LESS THAN (18262) ENGINE = InnoDB,
 PARTITION part2021 VALUES LESS THAN (18628) ENGINE = InnoDB,
 PARTITION part2022 VALUES LESS THAN (18993) ENGINE = InnoDB,
 PARTITION part2023 VALUES LESS THAN (19358) ENGINE = InnoDB,
 PARTITION part2024 VALUES LESS THAN (19723) ENGINE = InnoDB,
 PARTITION part2025 VALUES LESS THAN (20089) ENGINE = InnoDB,
 PARTITION part2026 VALUES LESS THAN (20454) ENGINE = InnoDB,
 PARTITION part2027 VALUES LESS THAN (20819) ENGINE = InnoDB,
 PARTITION part2028 VALUES LESS THAN (21184) ENGINE = InnoDB,
 PARTITION part2029 VALUES LESS THAN (21550) ENGINE = InnoDB,
 PARTITION part2030 VALUES LESS THAN (21915) ENGINE = InnoDB) */;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wetterturnier_cities`
--
-- Creation: Oct 24, 2018 at 06:35 AM
--

CREATE TABLE IF NOT EXISTS `wp_wetterturnier_cities` (
  `ID` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `hash` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `paramconfig` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort` tinyint(4) DEFAULT NULL,
  `since` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `until` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wetterturnier_citystats`
--
-- Creation: Dec 23, 2019 at 10:26 AM
-- Last update: Jan 30, 2020 at 11:00 PM
--

CREATE TABLE IF NOT EXISTS `wp_wetterturnier_citystats` (
  `cityID` int(11) NOT NULL DEFAULT '0',
  `T` double DEFAULT NULL,
  `U` double DEFAULT NULL,
  `V` double DEFAULT NULL,
  `m` double DEFAULT NULL,
  `n` double DEFAULT NULL,
  `A` double DEFAULT NULL,
  `B` double DEFAULT NULL,
  `C` double DEFAULT NULL,
  `p` double DEFAULT NULL,
  `q` double DEFAULT NULL,
  `r` double DEFAULT NULL,
  `s` double DEFAULT NULL,
  `tdates` int(11) DEFAULT NULL,
  `mean` double DEFAULT NULL,
  `sd` double DEFAULT NULL,
  `median` float DEFAULT NULL,
  `Qupp` double DEFAULT NULL,
  `Qlow` double DEFAULT NULL,
  `max` float DEFAULT NULL,
  `min` float DEFAULT NULL,
  `max_part` int(11) DEFAULT NULL,
  `min_part` int(11) DEFAULT NULL,
  `mean_part` double DEFAULT NULL,
  PRIMARY KEY (`cityID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wetterturnier_coefs`
--
-- Creation: Sep 01, 2019 at 10:49 AM
-- Last update: Nov 07, 2019 at 11:33 PM
--

CREATE TABLE IF NOT EXISTS `wp_wetterturnier_coefs` (
  `userID` int(11) NOT NULL,
  `cityID` smallint(5) NOT NULL,
  `paramID` smallint(5) NOT NULL,
  `tdate` smallint(5) NOT NULL,
  `coef` float DEFAULT '0',
  UNIQUE KEY `tdate` (`tdate`,`userID`,`cityID`,`paramID`),
  KEY `wp_wetterturnier_coefs_idx_paramID` (`paramID`),
  KEY `wp_wetterturnier_coefs_idx_cityID` (`cityID`),
  KEY `wp_wetterturnier_coefs_idx_userID` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wetterturnier_dates`
--
-- Creation: Oct 24, 2018 at 06:35 AM
--

CREATE TABLE IF NOT EXISTS `wp_wetterturnier_dates` (
  `tdate` smallint(11) unsigned NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`tdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wetterturnier_groups`
--
-- Creation: Oct 24, 2018 at 06:35 AM
--

CREATE TABLE IF NOT EXISTS `wp_wetterturnier_groups` (
  `groupID` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `groupName` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `groupDesc` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `since` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `until` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`groupID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=55 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wetterturnier_groupusers`
--
-- Creation: Aug 14, 2019 at 11:45 PM
--

CREATE TABLE IF NOT EXISTS `wp_wetterturnier_groupusers` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userID` bigint(5) unsigned NOT NULL,
  `groupID` smallint(5) unsigned NOT NULL,
  `application` text COLLATE utf8_unicode_ci NOT NULL,
  `since` timestamp NULL DEFAULT NULL,
  `until` timestamp NULL DEFAULT NULL,
  `active` tinyint(4) DEFAULT '1',
  `sort` smallint(5) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=749 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wetterturnier_obs`
--

CREATE TABLE IF NOT EXISTS `wp_wetterturnier_obs` (
  `station` smallint(5) unsigned NOT NULL,
  `paramID` smallint(5) unsigned NOT NULL,
  `betdate` smallint(5) unsigned NOT NULL,
  `placed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `placedby` bigint(20) unsigned NOT NULL DEFAULT '0',
  `value` smallint(6) DEFAULT NULL,
  UNIQUE KEY `station` (`station`,`paramID`,`betdate`),
  KEY `wp_wetterturnier_obs_idx_betdate` (`betdate`),
  KEY `wp_wetterturnier_obs_idx_station` (`station`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
/*!50100 PARTITION BY RANGE ( betdate)
(PARTITION part2000 VALUES LESS THAN (10957) ENGINE = InnoDB,
 PARTITION part2001 VALUES LESS THAN (11323) ENGINE = InnoDB,
 PARTITION part2002 VALUES LESS THAN (11688) ENGINE = InnoDB,
 PARTITION part2003 VALUES LESS THAN (12053) ENGINE = InnoDB,
 PARTITION part2004 VALUES LESS THAN (12418) ENGINE = InnoDB,
 PARTITION part2005 VALUES LESS THAN (12784) ENGINE = InnoDB,
 PARTITION part2006 VALUES LESS THAN (13149) ENGINE = InnoDB,
 PARTITION part2007 VALUES LESS THAN (13514) ENGINE = InnoDB,
 PARTITION part2008 VALUES LESS THAN (13879) ENGINE = InnoDB,
 PARTITION part2009 VALUES LESS THAN (14245) ENGINE = InnoDB,
 PARTITION part2010 VALUES LESS THAN (14610) ENGINE = InnoDB,
 PARTITION part2011 VALUES LESS THAN (14975) ENGINE = InnoDB,
 PARTITION part2012 VALUES LESS THAN (15340) ENGINE = InnoDB,
 PARTITION part2013 VALUES LESS THAN (15706) ENGINE = InnoDB,
 PARTITION part2014 VALUES LESS THAN (16071) ENGINE = InnoDB,
 PARTITION part2015 VALUES LESS THAN (16436) ENGINE = InnoDB,
 PARTITION part2016 VALUES LESS THAN (16801) ENGINE = InnoDB,
 PARTITION part2017 VALUES LESS THAN (17167) ENGINE = InnoDB,
 PARTITION part2018 VALUES LESS THAN (17532) ENGINE = InnoDB,
 PARTITION part2019 VALUES LESS THAN (17897) ENGINE = InnoDB,
 PARTITION part2020 VALUES LESS THAN (18262) ENGINE = InnoDB,
 PARTITION part2021 VALUES LESS THAN (18628) ENGINE = InnoDB,
 PARTITION part2022 VALUES LESS THAN (18993) ENGINE = InnoDB,
 PARTITION part2023 VALUES LESS THAN (19358) ENGINE = InnoDB,
 PARTITION part2024 VALUES LESS THAN (19723) ENGINE = InnoDB,
 PARTITION part2025 VALUES LESS THAN (20089) ENGINE = InnoDB,
 PARTITION part2026 VALUES LESS THAN (20454) ENGINE = InnoDB,
 PARTITION part2027 VALUES LESS THAN (20819) ENGINE = InnoDB,
 PARTITION part2028 VALUES LESS THAN (21184) ENGINE = InnoDB,
 PARTITION part2029 VALUES LESS THAN (21550) ENGINE = InnoDB,
 PARTITION part2030 VALUES LESS THAN (21915) ENGINE = InnoDB) */;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wetterturnier_param`
--
-- Creation: Sep 23, 2019 at 05:25 PM
--

CREATE TABLE IF NOT EXISTS `wp_wetterturnier_param` (
  `paramID` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `sort` int(10) unsigned DEFAULT NULL,
  `paramName` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `EN` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `DE` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `helpEN` text COLLATE utf8_unicode_ci NOT NULL,
  `helpDE` text COLLATE utf8_unicode_ci NOT NULL,
  `valformat` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vallength` smallint(6) DEFAULT '5',
  `valmin` smallint(6) NOT NULL,
  `valmax` smallint(6) NOT NULL,
  `valext` smallint(6) DEFAULT NULL,
  `valpre` smallint(6) NOT NULL DEFAULT '1',
  `format` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `decimals` tinyint(3) unsigned DEFAULT '1',
  `unit` varchar(5) COLLATE utf8_unicode_ci DEFAULT '',
  PRIMARY KEY (`paramID`,`paramName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wetterturnier_rerunrequest`
--
-- Creation: Oct 24, 2018 at 06:35 AM
--

CREATE TABLE IF NOT EXISTS `wp_wetterturnier_rerunrequest` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cityID` smallint(5) unsigned NOT NULL,
  `tdate` smallint(5) unsigned NOT NULL,
  `userID` bigint(20) unsigned NOT NULL,
  `placed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `done` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2833 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wetterturnier_stationparams`
--
-- Creation: Oct 24, 2018 at 06:35 AM
--

CREATE TABLE IF NOT EXISTS `wp_wetterturnier_stationparams` (
  `ID` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `stationID` smallint(5) unsigned NOT NULL,
  `paramID` smallint(5) unsigned NOT NULL,
  `since` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `until` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=127 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wetterturnier_stations`
--
-- Creation: Oct 11, 2019 at 04:46 PM
--

CREATE TABLE IF NOT EXISTS `wp_wetterturnier_stations` (
  `ID` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `cityID` int(10) unsigned NOT NULL,
  `wmo` smallint(5) unsigned NOT NULL,
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `since` int(11) NOT NULL DEFAULT '0',
  `until` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `wmo` (`wmo`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wetterturnier_tdatestats`
--
-- Creation: Nov 29, 2019 at 04:00 PM
-- Last update: Jan 30, 2020 at 07:16 PM
-- Last check: Nov 29, 2019 at 04:00 PM
--

CREATE TABLE IF NOT EXISTS `wp_wetterturnier_tdatestats` (
  `cityID` smallint(5) NOT NULL,
  `tdate` smallint(5) NOT NULL,
  `mean` float DEFAULT NULL,
  `median` float DEFAULT NULL,
  `Qlow` float DEFAULT NULL,
  `Qupp` float DEFAULT NULL,
  `max` float DEFAULT NULL,
  `min` float DEFAULT NULL,
  `sd` float DEFAULT NULL,
  `sd_upp` double DEFAULT NULL,
  `sd_upp_d1` double DEFAULT NULL,
  `sd_upp_d2` double DEFAULT NULL,
  `mean_d1` float DEFAULT NULL,
  `median_d1` float DEFAULT NULL,
  `max_d1` float DEFAULT NULL,
  `min_d1` float DEFAULT NULL,
  `sd_d1` float DEFAULT NULL,
  `Qlow_d1` float DEFAULT NULL,
  `Qupp_d1` float DEFAULT NULL,
  `mean_d2` float DEFAULT NULL,
  `median_d2` float DEFAULT NULL,
  `max_d2` float DEFAULT NULL,
  `min_d2` float DEFAULT NULL,
  `sd_d2` float DEFAULT NULL,
  `Qlow_d2` float DEFAULT NULL,
  `Qupp_d2` int(11) DEFAULT NULL,
  `part` int(11) DEFAULT NULL,
  UNIQUE KEY `tdate` (`tdate`,`cityID`),
  KEY `wp_wetterturnier_tdatestats_idx_cityID` (`cityID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wetterturnier_userstats`
--
-- Creation: Jan 30, 2020 at 07:20 PM
-- Last update: Feb 01, 2020 at 12:39 AM
-- Last check: Jan 30, 2020 at 07:20 PM
--

CREATE TABLE IF NOT EXISTS `wp_wetterturnier_userstats` (
  `userID` int(11) NOT NULL,
  `cityID` smallint(5) NOT NULL,
  `part` int(11) DEFAULT NULL,
  `points_adj` double DEFAULT '0',
  `points` float DEFAULT NULL,
  `points_adj1` double DEFAULT NULL,
  `points_adj2` double DEFAULT NULL,
  `points_adjX` double DEFAULT NULL,
  `mean` float DEFAULT NULL,
  `median` float DEFAULT NULL,
  `Qlow` float DEFAULT NULL,
  `Qupp` float DEFAULT NULL,
  `max` float DEFAULT NULL,
  `min` float DEFAULT NULL,
  `sd` float DEFAULT NULL,
  `sd_ind` double DEFAULT NULL,
  `sd_ind1` double DEFAULT NULL,
  `sd_ind2` double DEFAULT NULL,
  `sd_indX` double DEFAULT NULL,
  `points_d1` int(11) DEFAULT NULL,
  `mean_d1` float DEFAULT NULL,
  `median_d1` float DEFAULT NULL,
  `max_d1` float DEFAULT NULL,
  `min_d1` float DEFAULT NULL,
  `sd_d1` float DEFAULT NULL,
  `Qlow_d1` float DEFAULT NULL,
  `Qupp_d1` float DEFAULT NULL,
  `points_d2` int(11) DEFAULT NULL,
  `mean_d2` float DEFAULT NULL,
  `median_d2` float DEFAULT NULL,
  `max_d2` float DEFAULT NULL,
  `min_d2` float DEFAULT NULL,
  `sd_d2` float DEFAULT NULL,
  `Qlow_d2` float DEFAULT NULL,
  `Qupp_d2` float DEFAULT NULL,
  UNIQUE KEY `userID` (`userID`,`cityID`),
  KEY `wp_wetterturnier_bets_idx_cityID` (`cityID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wetterturnier_webcams`
--
-- Creation: Jun 18, 2019 at 01:07 AM
--

CREATE TABLE IF NOT EXISTS `wp_wetterturnier_webcams` (
  `ID` smallint(6) NOT NULL AUTO_INCREMENT,
  `cityID` smallint(11) NOT NULL,
  `uri` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `source` varchar(400) COLLATE utf8_unicode_ci NOT NULL,
  `desc` text COLLATE utf8_unicode_ci NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=12 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
