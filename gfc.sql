-- MySQL dump 10.13  Distrib 5.5.40, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: gfc
-- ------------------------------------------------------
-- Server version	5.5.40-0ubuntu0.12.04.1

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
-- Table structure for table `borrower`
--

DROP TABLE IF EXISTS `borrower`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `borrower` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_email` varchar(45) NOT NULL,
  `lastname` varchar(45) DEFAULT NULL,
  `firstname` varchar(45) DEFAULT NULL,
  `middlename` varchar(45) DEFAULT NULL,
  `address` text,
  `contact_no` varchar(45) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `no_of_dependencies` int(11) DEFAULT '0',
  `year_in_address` int(11) DEFAULT '0',
  `marital_status` enum('Single','Married','Separated','Widow') DEFAULT 'Single',
  `citizenship` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_email_UNIQUE` (`user_email`),
  CONSTRAINT `fk_borrower_1` FOREIGN KEY (`user_email`) REFERENCES `user` (`email`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `brand`
--

DROP TABLE IF EXISTS `brand`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brand` (
  `brand_code` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`brand_code`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `car`
--

DROP TABLE IF EXISTS `car`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `car` (
  `car_id` varchar(45) NOT NULL,
  `type_no` int(10) unsigned DEFAULT NULL,
  `brand_code` int(10) unsigned DEFAULT NULL,
  `year_model` varchar(45) DEFAULT NULL,
  `accessories` varchar(45) DEFAULT NULL,
  `dealer` varchar(45) DEFAULT NULL,
  `address` text,
  `status_id` int(10) unsigned DEFAULT NULL,
  `ci_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `ci_amount` int(11) DEFAULT '0',
  PRIMARY KEY (`car_id`),
  UNIQUE KEY `car_id_UNIQUE` (`car_id`),
  KEY `fk_car_1_idx` (`type_no`),
  KEY `fk_car_2_idx` (`brand_code`),
  KEY `fk_car_3_idx` (`status_id`),
  CONSTRAINT `fk_car_1` FOREIGN KEY (`type_no`) REFERENCES `type` (`type_no`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_car_2` FOREIGN KEY (`brand_code`) REFERENCES `brand` (`brand_code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_car_3` FOREIGN KEY (`status_id`) REFERENCES `car_status` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `car_status`
--

DROP TABLE IF EXISTS `car_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `car_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `income`
--

DROP TABLE IF EXISTS `income`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `income` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `spouse_id` int(10) unsigned DEFAULT NULL,
  `borrower_id` int(10) unsigned DEFAULT NULL,
  `source_income` enum('Business','Employment') DEFAULT 'Employment',
  `length_of_employment` int(11) DEFAULT '0',
  `name_of_firm` varchar(45) DEFAULT NULL,
  `position` varchar(45) DEFAULT NULL,
  `firm_address` varchar(256) DEFAULT NULL,
  `previous_employment` varchar(45) DEFAULT NULL,
  `previous_employment_address` varchar(256) DEFAULT NULL,
  `previous_employment_tel_no` varchar(45) DEFAULT NULL,
  `monthly_income` int(11) DEFAULT '0',
  `other_income_source` varchar(45) DEFAULT '',
  `other_income` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `spouse_id_UNIQUE` (`spouse_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `loan`
--

DROP TABLE IF EXISTS `loan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loan` (
  `loan_no` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `borrower_id` int(10) unsigned NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `amount_pay` int(11) DEFAULT '0',
  `amount_financed` int(11) DEFAULT '0',
  `term` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`loan_no`),
  KEY `fk_loan_1_idx` (`borrower_id`),
  CONSTRAINT `fk_loan_1` FOREIGN KEY (`borrower_id`) REFERENCES `borrower` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `loan_details`
--

DROP TABLE IF EXISTS `loan_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loan_details` (
  `loan_no` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `car_id` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `car_id_UNIQUE` (`car_id`),
  KEY `fk_loan_details_2_idx` (`loan_no`),
  CONSTRAINT `fk_loan_details_2` FOREIGN KEY (`loan_no`) REFERENCES `loan` (`loan_no`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_loan_details_1` FOREIGN KEY (`car_id`) REFERENCES `car` (`car_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `spouse`
--

DROP TABLE IF EXISTS `spouse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spouse` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `borrower_id` int(10) unsigned DEFAULT NULL,
  `spousename` varchar(45) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_spouse_1_idx` (`borrower_id`),
  CONSTRAINT `fk_spouse_1` FOREIGN KEY (`borrower_id`) REFERENCES `borrower` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `spouse_income`
--

DROP TABLE IF EXISTS `spouse_income`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spouse_income` (
  `income_code` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `spouse_id` int(10) unsigned NOT NULL,
  `source_of_income` enum('Business','Employment') DEFAULT 'Employment',
  `name_of_firm` varchar(45) DEFAULT NULL,
  `monthly_income` int(11) DEFAULT '0',
  `other_income` int(11) DEFAULT '0',
  `position` varchar(45) DEFAULT NULL,
  `spouse_address` varchar(256) DEFAULT NULL,
  `spouse_tel_no` varchar(45) DEFAULT NULL,
  `length_of_employment` int(11) DEFAULT '0',
  `other_source_of_income` varchar(45) DEFAULT '',
  PRIMARY KEY (`income_code`),
  KEY `fk_spouse_income_1_idx` (`spouse_id`),
  CONSTRAINT `fk_spouse_income_1` FOREIGN KEY (`spouse_id`) REFERENCES `spouse` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `type`
--

DROP TABLE IF EXISTS `type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `type` (
  `type_no` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`type_no`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `email` varchar(45) NOT NULL,
  `username` varchar(45) NOT NULL,
  `password` varchar(45) NOT NULL,
  `user_type_code` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`email`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  KEY `fk_user_1_idx` (`user_type_code`),
  CONSTRAINT `fk_user_1` FOREIGN KEY (`user_type_code`) REFERENCES `user_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_type`
--

DROP TABLE IF EXISTS `user_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-11-03 23:31:09
