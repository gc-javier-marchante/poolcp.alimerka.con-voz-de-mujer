SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;

DROP TABLE IF EXISTS `navidad2022_participant_email_clicks`;
CREATE TABLE `navidad2022_participant_email_clicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `participant_id` int(11) NOT NULL COMMENT 'Participante',
  `url` text NOT NULL COMMENT 'URL',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario que provocó la creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de última modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario que provocó la última modificación del registro',
  PRIMARY KEY (`id`),
  KEY `participant_id` (`participant_id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  CONSTRAINT `navidad2022_participant_email_clicks_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_participant_email_clicks_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_participant_email_clicks_ibfk_3` FOREIGN KEY (`participant_id`) REFERENCES `navidad2022_participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Clicks de participantes en enlces';
