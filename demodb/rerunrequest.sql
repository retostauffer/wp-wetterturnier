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
