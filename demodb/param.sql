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
