/*
SQLyog Ultimate v10.00 Beta1
MySQL - 5.5.20-log : Database - fabianagadano
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `admins` */

CREATE TABLE `admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(40) COLLATE utf8_bin NOT NULL,
  `password` varchar(32) COLLATE utf8_bin NOT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `email` varchar(150) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

/*Data for the table `admins` */

insert  into `admins`(`id`,`user`,`password`,`last_login`,`email`) values (1,'admin','a8f5f167f44f4964e6c998dee827110c',NULL,NULL);

/*Table structure for table `admins_permisos` */

CREATE TABLE `admins_permisos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idAdmin` int(11) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `admins_permisos` */

/*Table structure for table `clean_urls` */

CREATE TABLE `clean_urls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table` varchar(64) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `node_id` int(10) unsigned NOT NULL,
  `url` tinytext CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `language` varchar(2) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `table` (`table`,`node_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


/*Table structure for table `configuracion` */

CREATE TABLE `configuracion` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `configuracion` */

/*Table structure for table `files` */

CREATE TABLE `files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table` varchar(40) COLLATE utf8_bin DEFAULT NULL,
  `node_id` int(10) unsigned DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8_bin NOT NULL,
  `description` tinytext COLLATE utf8_bin,
  `flag` tinyint(10) NOT NULL DEFAULT '0',
  `weight` int(11) NOT NULL DEFAULT '0',
  `language` varchar(2) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


/*Table structure for table `files_thumbs` */

CREATE TABLE `files_thumbs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idFile` int(10) unsigned NOT NULL,
  `filename` varchar(100) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
