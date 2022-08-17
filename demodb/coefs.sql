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
