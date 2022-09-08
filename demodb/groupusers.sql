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
