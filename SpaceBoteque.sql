-- MySQL dump 10.13  Distrib 5.7.30, for Linux (x86_64)
--
-- Host: u12543.mysql.masterhost.ru    Database: u12543_sbq
-- ------------------------------------------------------
-- Server version	5.7.30-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `agencies`
--

DROP TABLE IF EXISTS `agencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `agencies` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `abbrev` varchar(255) DEFAULT NULL,
  `countryCode` varchar(3) DEFAULT NULL,
  `description` varchar(2048) DEFAULT NULL,
  `infoURL` varchar(255) DEFAULT NULL,
  `wikiURL` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `countryCode` (`countryCode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `launchStatuses`
--

DROP TABLE IF EXISTS `launchStatuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `launchStatuses` (
  `id` tinyint(3) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `abbrev` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `launches`
--

DROP TABLE IF EXISTS `launches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `launches` (
  `uuid` varchar(36) NOT NULL,
  `name` varchar(1024) DEFAULT NULL,
  `launchStatus` tinyint(4) DEFAULT NULL,
  `rocket` int(11) DEFAULT NULL,
  `rocketConfiguration` int(11) DEFAULT NULL,
  `pad` int(11) DEFAULT NULL,
  `location` int(11) DEFAULT NULL,
  `net` datetime DEFAULT NULL,
  `windowStart` datetime DEFAULT NULL,
  `windowEnd` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`uuid`),
  KEY `net` (`net`),
  KEY `updated` (`updated`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locations` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `countryCode` varchar(3) DEFAULT NULL,
  `description` varchar(2048) DEFAULT NULL,
  `latitude` decimal(10,6) DEFAULT NULL,
  `longitude` decimal(10,6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `countryCode` (`countryCode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `missions`
--

DROP TABLE IF EXISTS `missions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `missions` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(1024) DEFAULT NULL,
  `type` tinyint(3) unsigned DEFAULT NULL,
  `description` varchar(2048) DEFAULT NULL,
  `launch` varchar(36) DEFAULT NULL,
  `orbit` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `launch` (`launch`),
  KEY `orbit` (`orbit`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `missions2agencies`
--

DROP TABLE IF EXISTS `missions2agencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `missions2agencies` (
  `mission` int(10) unsigned DEFAULT NULL,
  `agency` int(10) unsigned DEFAULT NULL,
  KEY `agency` (`agency`),
  KEY `mission` (`mission`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orbits`
--

DROP TABLE IF EXISTS `orbits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orbits` (
  `id` tinyint(3) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `abbrev` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pads`
--

DROP TABLE IF EXISTS `pads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pads` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `countryCode` varchar(3) DEFAULT NULL,
  `description` varchar(2048) DEFAULT NULL,
  `latitude` decimal(10,6) DEFAULT NULL,
  `longitude` decimal(10,6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `countryCode` (`countryCode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pads2agencies`
--

DROP TABLE IF EXISTS `pads2agencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pads2agencies` (
  `pad` int(10) unsigned DEFAULT NULL,
  `agency` int(10) unsigned DEFAULT NULL,
  KEY `pad` (`pad`),
  KEY `agency` (`agency`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rocketConfigurations`
--

DROP TABLE IF EXISTS `rocketConfigurations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rocketConfigurations` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `variant` varchar(255) DEFAULT NULL,
  `fullName` varchar(255) DEFAULT NULL,
  `infoURL` varchar(1024) DEFAULT NULL,
  `wikiURL` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-03 13:17:04
