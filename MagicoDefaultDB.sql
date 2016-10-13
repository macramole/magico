-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 12, 2016 at 11:22 PM
-- Server version: 5.5.50-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `asd`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(40) COLLATE utf8_bin NOT NULL,
  `password` varchar(32) COLLATE utf8_bin NOT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `email` varchar(150) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=3 ;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `user`, `password`, `last_login`, `email`) VALUES
(1, 'admin', 'a8f5f167f44f4964e6c998dee827110c', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admins_permisos`
--

CREATE TABLE IF NOT EXISTS `admins_permisos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idAdmin` int(11) DEFAULT NULL,
  `title` varchar(150) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `clean_urls`
--

CREATE TABLE IF NOT EXISTS `clean_urls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table` varchar(64) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `node_id` int(10) unsigned NOT NULL,
  `url` tinytext CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `language` varchar(2) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `table` (`table`,`node_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `configuracion`
--

CREATE TABLE IF NOT EXISTS `configuracion` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table` varchar(40) COLLATE utf8_bin DEFAULT NULL,
  `node_id` int(10) unsigned DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8_bin NOT NULL,
  `description` tinytext COLLATE utf8_bin,
  `flag` tinyint(10) NOT NULL DEFAULT '0',
  `weight` int(11) NOT NULL DEFAULT '0',
  `language` varchar(2) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `files_thumbs`
--

CREATE TABLE IF NOT EXISTS `files_thumbs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idFile` int(10) unsigned NOT NULL,
  `filename` varchar(100) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=4 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
