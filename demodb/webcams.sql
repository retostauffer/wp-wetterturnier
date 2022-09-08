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
