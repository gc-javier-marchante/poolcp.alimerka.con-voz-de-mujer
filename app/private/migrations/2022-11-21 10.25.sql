SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;

ALTER TABLE `navidad2022_participants`
ADD `is_confirmed` tinyint(1) NOT NULL DEFAULT 0 AFTER `is_winner_email`;
ALTER TABLE `navidad2022_participants` ADD `confirmed_at` DATETIME NULL AFTER `is_confirmed`;

ALTER TABLE `navidad2022_participants` ADD `is_checked_in` tinyint(1) NOT NULL DEFAULT 0 AFTER `confirmed_at`;
ALTER TABLE `navidad2022_participants` ADD `checked_in_at` DATETIME NULL AFTER `is_checked_in`;
