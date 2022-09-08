-- phpMyAdmin SQL Dump
-- version 4.0.10
-- https://www.phpmyadmin.net
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
