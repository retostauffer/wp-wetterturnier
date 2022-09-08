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
