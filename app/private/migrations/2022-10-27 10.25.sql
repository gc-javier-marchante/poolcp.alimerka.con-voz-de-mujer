SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;

ALTER TABLE `navidad2022_participants`
ADD `barcode` varchar(100) NOT NULL AFTER `is_winner_email`,
ADD `barcode_scanned` tinyint(1) NOT NULL DEFAULT '0' AFTER `barcode`;