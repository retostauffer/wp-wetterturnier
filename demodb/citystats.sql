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
