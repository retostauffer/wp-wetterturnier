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
