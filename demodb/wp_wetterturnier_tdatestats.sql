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
