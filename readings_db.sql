-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Server-Version: 10.1.41-MariaDB-0+deb9u1
-- PHP-Version: 7.0.33-0+deb9u3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `admin_readings`
--
CREATE DATABASE IF NOT EXISTS `readings` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `readings`;

-- --------------------------------------------------------

--
-- Table structure for table `KY`
--

CREATE TABLE IF NOT EXISTS `KY` (
  `KyCode` varchar(10) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Password` varchar(15) NOT NULL,
  `Flats` int(11) NOT NULL,
  `Kitchen` tinyint(1) NOT NULL DEFAULT '0',
  `KitchenHot` tinyint(1) NOT NULL DEFAULT '0',
  `Bath` tinyint(1) NOT NULL DEFAULT '0',
  `BathHot` tinyint(1) NOT NULL DEFAULT '0',
  `Gas` tinyint(1) NOT NULL DEFAULT '0',
  `Electricity` tinyint(1) NOT NULL DEFAULT '0',
  `People` tinyint(1) NOT NULL DEFAULT '0',
  `Risers` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`KyCode`)
) ENGINE=INNODB DEFAULT CHARACTER SET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Period`
--

CREATE TABLE IF NOT EXISTS `Period` (
  `PeriodId` int(11) NOT NULL AUTO_INCREMENT,
  `KyCode` varchar(10) NOT NULL,
  `Year` int(11) NOT NULL,
  `Month` int(11) NOT NULL,
  `Locked` tinyint(1) NOT NULL DEFAULT '0',
  `Finished` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'User reports that all readings are entered',
  PRIMARY KEY (`PeriodId`),
  UNIQUE KEY `UX_Period` (`KyCode`,`Year`,`Month`),
  CONSTRAINT `FK_Period_KY` FOREIGN KEY (`KyCode`)
    REFERENCES `KY` (`KyCode`)
) ENGINE=INNODB DEFAULT CHARACTER SET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `HouseReading`
--

CREATE TABLE IF NOT EXISTS `HouseReading` (
  `KyCode` varchar(10) NOT NULL,
  `PeriodId` int(11) NOT NULL,
  `Start` decimal(7,2) NOT NULL,
  `End` decimal(7,2) NOT NULL,
  UNIQUE KEY `UX_KyPeriod` (`KyCode`,`PeriodId`),
  CONSTRAINT `FK_HouseReading_KY` FOREIGN KEY (`KyCode`)
    REFERENCES `KY` (`KyCode`),
  CONSTRAINT `FK_HouseReading_Period` FOREIGN KEY (`PeriodId`)
    REFERENCES `Period` (`PeriodId`)
) ENGINE=INNODB DEFAULT CHARACTER SET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Reading`
--

CREATE TABLE IF NOT EXISTS `Reading` (
  `PeriodId` int(11) NOT NULL,
  `FlatId` int(11) NOT NULL,
  `ColdKitchStart` decimal(7,2) DEFAULT NULL,
  `ColdKitchEnd` decimal(7,2) DEFAULT NULL,
  `HotKitchStart` decimal(7,2) DEFAULT NULL,
  `HotKitchEnd` decimal(7,2) DEFAULT NULL,
  `ColdBathStart` decimal(7,2) DEFAULT NULL,
  `ColdBathEnd` decimal(7,2) DEFAULT NULL,
  `HotBathStart` decimal(7,2) DEFAULT NULL,
  `HotBathEnd` decimal(7,2) DEFAULT NULL,
  `GasStart` decimal(7,2) DEFAULT NULL,
  `GasEnd` decimal(7,2) DEFAULT NULL,
  `ElectrStart` decimal(7,2) DEFAULT NULL,
  `ElectrEnd` decimal(7,2) DEFAULT NULL,
  `People` int(11) DEFAULT NULL,
  UNIQUE KEY `UX_PeriodFlat` (`PeriodId`,`FlatId`),
  CONSTRAINT `FK_Reading_Period` FOREIGN KEY (`PeriodId`)
    REFERENCES `Period` (`PeriodId`)
) ENGINE=INNODB DEFAULT CHARACTER SET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Riser`
--

CREATE TABLE IF NOT EXISTS `Riser` (
  `RiserId` int(11) NOT NULL,
  `KyCode` varchar(10) NOT NULL,
  `Name` varchar(50) NOT NULL,
  PRIMARY KEY (`RiserId`),
  CONSTRAINT `FK_Riser_KY` FOREIGN KEY (`KyCode`)
    REFERENCES `KY` (`KyCode`)
) ENGINE=INNODB DEFAULT CHARACTER SET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `RiserReading`
--

CREATE TABLE IF NOT EXISTS `RiserReading` (
  `PeriodId` int(11) NOT NULL,
  `RiserId` int(11) NOT NULL,
  `Start` decimal(7,2) NOT NULL,
  `End` decimal(7,2) NOT NULL,
  UNIQUE KEY `UX_PeriodRiser` (`PeriodId`,`RiserId`),
  CONSTRAINT `FK_RiserReading_Riser` FOREIGN KEY (`RiserId`)
    REFERENCES `Riser` (`RiserId`)
) ENGINE=INNODB DEFAULT CHARACTER SET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
