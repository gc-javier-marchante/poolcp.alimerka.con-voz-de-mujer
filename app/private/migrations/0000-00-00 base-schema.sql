-- Adminer 4.8.0 MySQL 5.7.37 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `navidad2022_acl_cache_user_permissions`;
CREATE TABLE `navidad2022_acl_cache_user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'Usuario',
  `acl_permission_id` int(11) NOT NULL COMMENT 'Permiso de usuario',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario que provocó la creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de última modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario que provocó la última modificación del registro',
  PRIMARY KEY (`id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  KEY `acl_permission_id` (`acl_permission_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `navidad2022_acl_cache_user_permissions_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_acl_cache_user_permissions_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_acl_cache_user_permissions_ibfk_3` FOREIGN KEY (`acl_permission_id`) REFERENCES `navidad2022_acl_permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_acl_cache_user_permissions_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Cache: Permisos por usuario';


DROP TABLE IF EXISTS `navidad2022_acl_permissions`;
CREATE TABLE `navidad2022_acl_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acl_section_id` int(11) NOT NULL COMMENT 'Sección app',
  `name` varchar(50) NOT NULL COMMENT 'Nombre del permiso completo (inglés)',
  `name_short` varchar(50) NOT NULL COMMENT 'Nombre del permiso corto (inglés)',
  `alias` varchar(100) NOT NULL COMMENT 'Nombre del permiso para leer (español)',
  `is_full_access` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Si es de acceso completo a la sección',
  `description` text COMMENT 'Descripción legible del permiso (español)',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario que provocó la creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de última modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario que provocó la última modificación del registro',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  KEY `acl_section_id` (`acl_section_id`),
  KEY `is_full_access` (`is_full_access`),
  KEY `name_short` (`name_short`),
  CONSTRAINT `navidad2022_acl_permissions_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_acl_permissions_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_acl_permissions_ibfk_4` FOREIGN KEY (`acl_section_id`) REFERENCES `navidad2022_acl_sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Permisos de seguridad';

INSERT INTO `navidad2022_acl_permissions` (`id`, `acl_section_id`, `name`, `name_short`, `alias`, `is_full_access`, `description`, `created`, `created_by_user_id`, `modified`, `modified_by_user_id`) VALUES
(1,	1,	'USER_FULL_ACCESS',	'Full access',	'Acceso completo usuarios',	1,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(2,	1,	'USER_MENU',	'Menu',	'Menú usuarios',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(3,	1,	'USER_LIST',	'List',	'Listar usuarios',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(4,	1,	'USER_VIEW',	'View',	'Ver usuarios',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(5,	1,	'USER_CREATE',	'Create',	'Crear usuarios',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(6,	1,	'USER_UPDATE',	'Update',	'Editar usuarios',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(7,	1,	'USER_DELETE',	'Delete',	'Eliminar usuarios',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(8,	1,	'USER_EXPORT',	'Export',	'Exportar usuarios',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(9,	2,	'PICTURE_FULL_ACCESS',	'Full access',	'Acceso completo imágenes',	1,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(10,	2,	'PICTURE_MENU',	'Menu',	'Menú imágenes',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(11,	2,	'PICTURE_LIST',	'List',	'Listar imágenes',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(12,	2,	'PICTURE_VIEW',	'View',	'Ver imágenes',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(13,	2,	'PICTURE_CREATE',	'Create',	'Crear imágenes',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(14,	2,	'PICTURE_UPDATE',	'Update',	'Editar imágenes',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(15,	2,	'PICTURE_DELETE',	'Delete',	'Eliminar imágenes',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(16,	2,	'PICTURE_EXPORT',	'Export',	'Exportar imágenes',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(17,	3,	'FILE_FULL_ACCESS',	'Full access',	'Acceso completo archivos',	1,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(18,	3,	'FILE_MENU',	'Menu',	'Menú archivos',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(19,	3,	'FILE_LIST',	'List',	'Listar archivos',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(20,	3,	'FILE_VIEW',	'View',	'Ver archivos',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(21,	3,	'FILE_CREATE',	'Create',	'Crear archivos',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(22,	3,	'FILE_UPDATE',	'Update',	'Editar archivos',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(23,	3,	'FILE_DELETE',	'Delete',	'Eliminar archivos',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(24,	3,	'FILE_EXPORT',	'Export',	'Exportar archivos',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(25,	4,	'ACL_PROFILE_FULL_ACCESS',	'Full access',	'Acceso completo perfiles',	1,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(26,	4,	'ACL_PROFILE_MENU',	'Menu',	'Menú perfiles',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(27,	4,	'ACL_PROFILE_LIST',	'List',	'Listar perfiles',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(28,	4,	'ACL_PROFILE_VIEW',	'View',	'Ver perfiles',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(29,	4,	'ACL_PROFILE_CREATE',	'Create',	'Crear perfiles',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(30,	4,	'ACL_PROFILE_UPDATE',	'Update',	'Editar perfiles',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(31,	4,	'ACL_PROFILE_DELETE',	'Delete',	'Eliminar perfiles',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(32,	4,	'ACL_PROFILE_EXPORT',	'Export',	'Exportar perfiles',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(33,	5,	'ACL_PERMISSION_FULL_ACCESS',	'Full access',	'Acceso completo permisos',	1,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(34,	5,	'ACL_PERMISSION_MENU',	'Menu',	'Menú permisos',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(35,	5,	'ACL_PERMISSION_LIST',	'List',	'Listar permisos',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(36,	5,	'ACL_PERMISSION_VIEW',	'View',	'Ver permisos',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(38,	5,	'ACL_PERMISSION_UPDATE',	'Update',	'Editar permisos',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(40,	5,	'ACL_PERMISSION_EXPORT',	'Export',	'Exportar permisos',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(41,	1,	'USER_RESET_TFA',	'ResetTFA',	'Restablecer configuración de doble autenticación',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(42,	1,	'USER_CHANGE_PASSWORD',	'ChangePassword',	'Cambiar la contraseña',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(43,	1,	'USER_SETTINGS',	'Settings',	'Cambiar su configuración',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(44,	6,	'WEBHOOK_SEND',	'Send',	'Enviar webhooks manualmente',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(45,	6,	'WEBHOOK_DELETE',	'Delete',	'Eliminar webhooks',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(46,	6,	'WEBHOOK_UPDATE',	'Update',	'Editar webhooks',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(47,	6,	'WEBHOOK_CREATE',	'Create',	'Crear webhooks',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(48,	6,	'WEBHOOK_VIEW',	'View',	'Ver webhooks',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(49,	6,	'WEBHOOK_LIST',	'List',	'Listar webhooks',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(50,	6,	'WEBHOOK_MENU',	'Menu',	'Menú webhooks',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(51,	6,	'WEBHOOK_FULL_ACCESS',	'Full access',	'Acceso completo webhooks',	1,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(93,	7,	'CANONICAL_URL_FULL_ACCESS',	'Full access',	'Acceso completo urls',	1,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(94,	7,	'CANONICAL_URL_MENU',	'Menu',	'Menú urls',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(95,	7,	'CANONICAL_URL_LIST',	'List',	'Listar urls',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(96,	7,	'CANONICAL_URL_VIEW',	'View',	'Ver urls',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(97,	7,	'CANONICAL_URL_CREATE',	'Create',	'Crear urls',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(98,	7,	'CANONICAL_URL_UPDATE',	'Update',	'Editar urls',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(99,	7,	'CANONICAL_URL_DELETE',	'Delete',	'Eliminar urls',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(100,	7,	'CANONICAL_URL_EXPORT',	'Export',	'Exportar urls',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(125,	11,	'PARTICIPANT_FULL_ACCESS',	'Full access',	'Acceso completo participantes',	1,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(126,	11,	'PARTICIPANT_MENU',	'Menu',	'Menú participantes',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(127,	11,	'PARTICIPANT_LIST',	'List',	'Listar participantes',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(128,	11,	'PARTICIPANT_VIEW',	'View',	'Ver participantes',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(130,	11,	'PARTICIPANT_UPDATE',	'Update',	'Editar participantes',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(132,	11,	'PARTICIPANT_EXPORT',	'Export',	'Exportar participantes',	0,	NULL,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(133,	12,	'WINNER_PAGE_SECTION_FULL_ACCESS',	'Full access',	'Acceso completo secciones de página de ganadores',	1,	NULL,	'2022-03-03 10:17:16',	1,	'2022-03-03 10:17:16',	1),
(134,	12,	'WINNER_PAGE_SECTION_MENU',	'Menu',	'Menú secciones de página de ganadores',	0,	NULL,	'2022-03-03 10:17:16',	1,	'2022-03-03 10:17:16',	1),
(135,	12,	'WINNER_PAGE_SECTION_LIST',	'List',	'Listar secciones de página de ganadores',	0,	NULL,	'2022-03-03 10:17:16',	1,	'2022-03-03 10:17:16',	1),
(136,	12,	'WINNER_PAGE_SECTION_VIEW',	'View',	'Ver secciones de página de ganadores',	0,	NULL,	'2022-03-03 10:17:16',	1,	'2022-03-03 10:17:16',	1),
(137,	12,	'WINNER_PAGE_SECTION_CREATE',	'Create',	'Crear secciones de página de ganadores',	0,	NULL,	'2022-03-03 10:17:16',	1,	'2022-03-03 10:17:16',	1),
(138,	12,	'WINNER_PAGE_SECTION_UPDATE',	'Update',	'Editar secciones de página de ganadores',	0,	NULL,	'2022-03-03 10:17:16',	1,	'2022-03-03 10:17:16',	1),
(139,	12,	'WINNER_PAGE_SECTION_DELETE',	'Delete',	'Eliminar secciones de página de ganadores',	0,	NULL,	'2022-03-03 10:17:16',	1,	'2022-03-03 10:17:16',	1),
(140,	12,	'WINNER_PAGE_SECTION_EXPORT',	'Export',	'Exportar secciones de página de ganadores',	0,	NULL,	'2022-03-03 10:17:16',	1,	'2022-03-03 10:17:16',	1);

DROP TABLE IF EXISTS `navidad2022_acl_profiles`;
CREATE TABLE `navidad2022_acl_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'Nombre',
  `description` text COMMENT 'Descripción del perfil',
  `is_full_access` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Si es acceso completo, ignorando permisos',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario que provocó la creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de última modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario que provocó la última modificación del registro',
  PRIMARY KEY (`id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  CONSTRAINT `navidad2022_acl_profiles_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_acl_profiles_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Perfil de usuario';

INSERT INTO `navidad2022_acl_profiles` (`id`, `name`, `description`, `is_full_access`, `created`, `created_by_user_id`, `modified`, `modified_by_user_id`) VALUES
(1,	'Administrador',	NULL,	1,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	1),
(2,	'Gestión',	NULL,	0,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(3,	'Agencia',	NULL,	0,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL);

DROP TABLE IF EXISTS `navidad2022_acl_profile_permissions`;
CREATE TABLE `navidad2022_acl_profile_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acl_profile_id` int(11) NOT NULL COMMENT 'Perfil de usuario',
  `acl_permission_id` int(11) NOT NULL COMMENT 'Permiso de usuario',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario que provocó la creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de última modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario que provocó la última modificación del registro',
  PRIMARY KEY (`id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  KEY `acl_profile_id` (`acl_profile_id`),
  KEY `acl_permission_id` (`acl_permission_id`),
  CONSTRAINT `navidad2022_acl_profile_permissions_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_acl_profile_permissions_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_acl_profile_permissions_ibfk_3` FOREIGN KEY (`acl_profile_id`) REFERENCES `navidad2022_acl_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_acl_profile_permissions_ibfk_4` FOREIGN KEY (`acl_permission_id`) REFERENCES `navidad2022_acl_permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='N-N: Permisos de perfil de usuario';

INSERT INTO `navidad2022_acl_profile_permissions` (`id`, `acl_profile_id`, `acl_permission_id`, `created`, `created_by_user_id`, `modified`, `modified_by_user_id`) VALUES
(4,	2,	125,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(6,	2,	12,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(7,	2,	11,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(8,	2,	27,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(9,	3,	1,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(10,	3,	109,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(11,	3,	117,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(12,	3,	125,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(13,	3,	28,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(14,	3,	12,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(15,	3,	11,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(16,	3,	27,	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(25,	2,	112,	'2022-01-13 14:43:29',	1,	'2022-01-13 14:43:29',	1),
(26,	2,	120,	'2022-01-13 14:43:29',	1,	'2022-01-13 14:43:29',	1),
(27,	2,	111,	'2022-01-13 14:43:29',	1,	'2022-01-13 14:43:29',	1),
(28,	2,	119,	'2022-01-13 14:43:29',	1,	'2022-01-13 14:43:29',	1),
(29,	2,	133,	'2022-03-03 10:18:21',	1,	'2022-03-03 10:18:21',	1),
(30,	3,	133,	'2022-03-03 10:18:34',	1,	'2022-03-03 10:18:34',	1);

DROP TABLE IF EXISTS `navidad2022_acl_sections`;
CREATE TABLE `navidad2022_acl_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_type_id` int(11) DEFAULT NULL COMMENT 'Entidad',
  `name` varchar(50) NOT NULL COMMENT 'Nombre',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario que provocó la creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de última modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario que provocó la última modificación del registro',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `content_type_id` (`content_type_id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  CONSTRAINT `navidad2022_acl_sections_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_acl_sections_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_acl_sections_ibfk_3` FOREIGN KEY (`content_type_id`) REFERENCES `navidad2022_content_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Diccionario: Secciones de seguridad';

INSERT INTO `navidad2022_acl_sections` (`id`, `content_type_id`, `name`, `created`, `created_by_user_id`, `modified`, `modified_by_user_id`) VALUES
(1,	1,	'Usuarios',	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(2,	2,	'Imágenes',	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(3,	3,	'Archivos',	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(4,	4,	'Perfiles',	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(5,	5,	'Permisos',	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(6,	6,	'Webhooks',	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(7,	7,	'URLs',	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(9,	9,	'Premios',	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(10,	10,	'Temporadas',	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(11,	11,	'Participantes',	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(12,	12,	'Secciones de página de ganadores',	'2022-03-03 10:17:16',	1,	'2022-03-03 10:17:16',	1);

DROP TABLE IF EXISTS `navidad2022_canonical_urls`;
CREATE TABLE `navidad2022_canonical_urls` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID interno',
  `url` varchar(150) NOT NULL COMMENT 'URL',
  `seo_title` text COMMENT 'Título SEO',
  `seo_description` text COMMENT 'Descripción SEO',
  `picture_id` int(11) DEFAULT NULL COMMENT 'Imagen',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de modificación del registro',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`),
  KEY `picture_id` (`picture_id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  CONSTRAINT `navidad2022_canonical_urls_ibfk_1` FOREIGN KEY (`picture_id`) REFERENCES `navidad2022_pictures` (`id`) ON DELETE SET NULL,
  CONSTRAINT `navidad2022_canonical_urls_ibfk_2` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_canonical_urls_ibfk_3` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Configuración SEO de URLs';


DROP TABLE IF EXISTS `navidad2022_content_types`;
CREATE TABLE `navidad2022_content_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'Nombre',
  `alias` varchar(50) NOT NULL COMMENT 'Alias',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario que provocó la creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de última modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario que provocó la última modificación del registro',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  CONSTRAINT `navidad2022_content_types_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_content_types_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tipos para generic foreign keys';

INSERT INTO `navidad2022_content_types` (`id`, `name`, `alias`, `created`, `created_by_user_id`, `modified`, `modified_by_user_id`) VALUES
(1,	'User',	'User',	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(2,	'Picture',	'Picture',	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(3,	'File',	'File',	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(4,	'AclProfile',	'AclProfile',	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(5,	'AclPermission',	'AclPermission',	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(6,	'Webhook',	'Webhook',	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(7,	'CanonicalUrl',	'CanonicalUrl',	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(11,	'Participant',	'Participant',	'2022-01-05 10:43:12',	NULL,	'2022-01-05 10:43:12',	NULL),
(12,	'WinnerPageSection',	'WinnerPageSection',	'2022-03-03 10:17:16',	1,	'2022-03-03 10:17:16',	1);

DROP TABLE IF EXISTS `navidad2022_db_migrations`;
CREATE TABLE `navidad2022_db_migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID interno',
  `file` varchar(100) NOT NULL COMMENT 'Nombre del archivo',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de modificación del registro',
  PRIMARY KEY (`id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  CONSTRAINT `navidad2022_db_migrations_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_db_migrations_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Migraciones';


DROP TABLE IF EXISTS `navidad2022_files`;
CREATE TABLE `navidad2022_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID interno',
  `file_category_id` int(11) NOT NULL DEFAULT '1' COMMENT 'Categoría de archivo',
  `original_basename` varchar(150) DEFAULT NULL COMMENT 'Nombre de archivo original',
  `src` varchar(150) DEFAULT NULL COMMENT 'URL',
  `alt` varchar(150) DEFAULT NULL COMMENT 'Nombre alternativo',
  `storage` varchar(10) NOT NULL DEFAULT 'local' COMMENT 'Modo de almacenamiento',
  `remote_path` varchar(150) DEFAULT NULL COMMENT 'Ruta remota de almacenamiento',
  `path` varchar(150) DEFAULT NULL COMMENT 'Ruta',
  `mime_type` varchar(100) NOT NULL COMMENT 'Tipo de archivo',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de modificación del registro',
  PRIMARY KEY (`id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  KEY `file_category_id` (`file_category_id`),
  CONSTRAINT `navidad2022_files_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_files_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_files_ibfk_3` FOREIGN KEY (`file_category_id`) REFERENCES `navidad2022_file_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Archivos subidos';


DROP TABLE IF EXISTS `navidad2022_file_categories`;
CREATE TABLE `navidad2022_file_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID interno',
  `name` varchar(150) NOT NULL COMMENT 'Nombre',
  `file_category_id` int(11) DEFAULT NULL COMMENT 'Categoría padre',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de modificación del registro',
  PRIMARY KEY (`id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  KEY `file_category_id` (`file_category_id`),
  CONSTRAINT `navidad2022_file_categories_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_file_categories_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_file_categories_ibfk_3` FOREIGN KEY (`file_category_id`) REFERENCES `navidad2022_file_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Categorías de archivo';

INSERT INTO `navidad2022_file_categories` (`id`, `name`, `file_category_id`, `created`, `created_by_user_id`, `modified`, `modified_by_user_id`) VALUES
(1,	'General',	NULL,	'2022-01-05 09:11:48',	NULL,	'2022-01-05 09:11:48',	NULL);

DROP TABLE IF EXISTS `navidad2022_layout_texts`;
CREATE TABLE `navidad2022_layout_texts` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID interno',
  `locale` varchar(2) DEFAULT NULL COMMENT 'Idioma',
  `code` varchar(150) NOT NULL COMMENT 'Código',
  `text` text COMMENT 'Texto',
  `picture_id` int(11) DEFAULT NULL COMMENT 'Imagen',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de modificación del registro',
  PRIMARY KEY (`id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  CONSTRAINT `navidad2022_layout_texts_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_layout_texts_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Textos del layout';


DROP TABLE IF EXISTS `navidad2022_logs`;
CREATE TABLE `navidad2022_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID interno',
  `email` varchar(150) DEFAULT NULL COMMENT 'Email',
  `model` varchar(150) DEFAULT NULL COMMENT 'Modelo',
  `model_id` int(11) DEFAULT NULL COMMENT 'ID del registro',
  `field` varchar(150) DEFAULT NULL COMMENT 'Nombre del campo',
  `value` text COMMENT 'Valor',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de modificación del registro',
  PRIMARY KEY (`id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  CONSTRAINT `navidad2022_logs_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_logs_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='navidad2022_Logs de cambio';


DROP TABLE IF EXISTS `navidad2022_participants`;
CREATE TABLE `navidad2022_participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(18) NOT NULL COMMENT 'Código',
  `code_supermarket` varchar(3) NOT NULL COMMENT 'Código de supermercado',
  `code_promotion` varchar(2) NOT NULL COMMENT 'Código de promoción extraído del código',
  `name` varchar(100) DEFAULT NULL COMMENT 'Nombre y apellidos',
  `address` varchar(250) DEFAULT NULL COMMENT 'Dirección',
  `city` varchar(150) DEFAULT NULL COMMENT 'Población',
  `postal_code` varchar(50) DEFAULT NULL COMMENT 'Código postal',
  `province` varchar(150) DEFAULT NULL COMMENT 'Provincia',
  `telephone` varchar(50) DEFAULT NULL COMMENT 'Teléfono',
  `email` varchar(250) DEFAULT NULL COMMENT 'Email',
  `document` varchar(50) DEFAULT NULL COMMENT 'DNI',
  `accepts_legal` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Ha leído y acepta las Bases Legales',
  `accepts_info` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Acepta recibir informaciones u ofertas promocionales de Alimerka',
  `requires_address` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Si el premio ganado requiere dirección',
  `is_completed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Si ha completado los datos',
  `is_sent` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Enviado',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario que provocó la creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de última modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario que provocó la última modificación del registro',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  CONSTRAINT `navidad2022_participants_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_participants_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Participantes de la promoción';


DROP TABLE IF EXISTS `navidad2022_pictures`;
CREATE TABLE `navidad2022_pictures` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID interno',
  `picture_category_id` int(11) NOT NULL COMMENT 'Categorías de imagen',
  `original_basename` varchar(150) NOT NULL COMMENT 'Nombre de archivo original',
  `src` varchar(150) DEFAULT NULL COMMENT 'URL',
  `alt` varchar(150) DEFAULT NULL COMMENT 'Nombre alternativo',
  `storage` varchar(10) NOT NULL DEFAULT 'local' COMMENT 'Modo de almacenamiento',
  `remote_path` varchar(150) NOT NULL COMMENT 'Ruta remota de almacenamiento',
  `path` varchar(150) DEFAULT NULL COMMENT 'Ruta',
  `mime_type` varchar(100) NOT NULL COMMENT 'Tipo de archivo',
  `width` int(11) NOT NULL COMMENT 'Ancho',
  `height` int(11) NOT NULL COMMENT 'Height',
  `320_token` varchar(150) NOT NULL COMMENT 'Código de visualización en 320px',
  `640_token` varchar(150) NOT NULL COMMENT 'Código de visualización en 640px',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de modificación del registro',
  PRIMARY KEY (`id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  CONSTRAINT `navidad2022_pictures_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_pictures_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Imágenes';

DROP TABLE IF EXISTS `navidad2022_picture_categories`;
CREATE TABLE `navidad2022_picture_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID interno',
  `name` varchar(150) NOT NULL COMMENT 'Nombre',
  `picture_category_id` int(11) DEFAULT NULL COMMENT 'Categoría padre',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de modificación del registro',
  PRIMARY KEY (`id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  KEY `picture_category_id` (`picture_category_id`),
  CONSTRAINT `navidad2022_picture_categories_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_picture_categories_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_picture_categories_ibfk_3` FOREIGN KEY (`picture_category_id`) REFERENCES `navidad2022_picture_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Categorías de imagen';

INSERT INTO `navidad2022_picture_categories` (`id`, `name`, `picture_category_id`, `created`, `created_by_user_id`, `modified`, `modified_by_user_id`) VALUES
(1,	'General',	NULL,	'2022-01-05 09:11:48',	NULL,	'2022-01-05 09:11:48',	NULL);

DROP TABLE IF EXISTS `navidad2022_queued_tasks`;
CREATE TABLE `navidad2022_queued_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id interno',
  `gestymvc_request_identifier` varchar(100) DEFAULT NULL COMMENT 'Id de la petición HTTP que desencadenó la acción',
  `code` varchar(50) NOT NULL COMMENT 'Código de la tarea',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de modificación del registro',
  PRIMARY KEY (`id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  KEY `code` (`code`),
  CONSTRAINT `navidad2022_queued_tasks_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `navidad2022_queued_tasks_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tareas programadas';

DROP TABLE IF EXISTS `navidad2022_users`;
CREATE TABLE `navidad2022_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID interno',
  `acl_profile_id` int(11) DEFAULT NULL COMMENT 'Perfil ACL',
  `first_name` varchar(150) NOT NULL COMMENT 'Nombre',
  `last_name` varchar(150) NOT NULL COMMENT 'Apellidos',
  `email` varchar(150) NOT NULL COMMENT 'Email',
  `avatar_url` varchar(150) NOT NULL COMMENT 'URL de avatar',
  `avatar_picture_id` int(11) DEFAULT NULL COMMENT 'Imagen de avatar',
  `level` int(11) NOT NULL DEFAULT '0' COMMENT 'Nivel de usuario',
  `password_digest` varchar(32) NOT NULL COMMENT 'Hash de contraseña',
  `salt` varchar(10) NOT NULL COMMENT 'Salt de contraseña',
  `reset_password_token` varchar(150) DEFAULT NULL COMMENT 'Código de restablecimiento de contraseña',
  `failed_attempts` int(11) DEFAULT NULL COMMENT 'Intentos fallidos',
  `banned` datetime DEFAULT NULL COMMENT 'Expulsado',
  `banned_reason` varchar(150) DEFAULT NULL COMMENT 'Razón de expulsión',
  `last_login` datetime DEFAULT NULL COMMENT 'Último inicio de sesión',
  `last_password_change` datetime DEFAULT NULL COMMENT 'Último cambio de contraseña',
  `last_reset_request` datetime DEFAULT NULL COMMENT 'Última solicitud de restablecimiento',
  `otp_seed` varchar(100) DEFAULT NULL COMMENT 'Semilla de OTP',
  `api_secret` text COMMENT 'Clave de API',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de modificación del registro',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  KEY `avatar_picture_id` (`avatar_picture_id`),
  KEY `acl_profile_id` (`acl_profile_id`),
  CONSTRAINT `navidad2022_users_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_users_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_users_ibfk_3` FOREIGN KEY (`avatar_picture_id`) REFERENCES `navidad2022_pictures` (`id`) ON DELETE SET NULL,
  CONSTRAINT `navidad2022_users_ibfk_4` FOREIGN KEY (`acl_profile_id`) REFERENCES `navidad2022_acl_profiles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Usuarios';

INSERT INTO `navidad2022_users` (`id`, `acl_profile_id`, `first_name`, `last_name`, `email`, `avatar_url`, `avatar_picture_id`, `level`, `password_digest`, `salt`, `reset_password_token`, `failed_attempts`, `banned`, `banned_reason`, `last_login`, `last_password_change`, `last_reset_request`, `otp_seed`, `api_secret`, `created`, `created_by_user_id`, `modified`, `modified_by_user_id`) VALUES
(1,	1,	'Federico Luis',	'Lescano Carroll',	'federico.lescano@gestycontrol.com',	'https://35aniversarioalimerka.es/static/img/avatars/blank.png',	NULL,	0,	'0d9f26f6bcb2a83e7ddf7d610b5e7ade',	'heSpvC',	NULL,	0,	NULL,	NULL,	'2022-03-18 10:42:19',	'2022-01-07 09:29:38',	NULL,	NULL,	NULL,	'2022-01-05 10:15:12',	NULL,	'2022-03-18 10:42:19',	NULL),
(2,	3,	'Macarena',	'Llosa',	'macarena.llosa@poolcp.com',	'https://35aniversarioalimerka.es/static/img/avatars/blank.png',	NULL,	0,	'1881ab64ba53c1bffbe12ef2ef6a5fbf',	'ZUo$$e',	NULL,	0,	NULL,	NULL,	'2022-04-18 09:12:19',	'2022-01-07 09:32:23',	NULL,	NULL,	NULL,	'2022-01-07 09:32:23',	1,	'2022-04-18 09:12:19',	NULL),
(3,	3,	'José',	'Rodríguez Cazorla',	'jose.rodriguez@poolcp.com',	'https://35aniversarioalimerka.es/static/img/avatars/blank.png',	NULL,	0,	'862d39da482b5120f8254232edc91b51',	'ux?WDQO',	NULL,	0,	NULL,	NULL,	'2022-04-18 10:55:13',	'2022-01-12 13:31:33',	NULL,	NULL,	NULL,	'2022-01-07 09:32:23',	1,	'2022-04-18 10:55:13',	NULL),
(4,	3,	'Mavi',	'de Pablo',	'mavi.depablo@poolcp.com',	'https://35aniversarioalimerka.es/static/img/avatars/blank.png',	NULL,	0,	'1881ab64ba53c1bffbe12ef2ef6a5fbf',	'ZUo$$e',	NULL,	0,	NULL,	NULL,	NULL,	'2022-01-07 09:32:23',	NULL,	NULL,	NULL,	'2022-01-07 09:32:23',	1,	'2022-01-07 09:32:23',	1),
(5,	3,	'Elena',	'Pellon',	'elena.pellon@poolcp.com',	'https://35aniversarioalimerka.es/static/img/avatars/blank.png',	NULL,	0,	'1881ab64ba53c1bffbe12ef2ef6a5fbf',	'ZUo$$e',	NULL,	0,	NULL,	NULL,	NULL,	'2022-01-07 09:32:23',	NULL,	NULL,	NULL,	'2022-01-07 09:32:23',	1,	'2022-01-07 09:32:23',	1),
(6,	3,	'Thais',	'Soroa',	'thais.soroa@poolcp.com',	'https://35aniversarioalimerka.es/static/img/avatars/blank.png',	NULL,	0,	'1881ab64ba53c1bffbe12ef2ef6a5fbf',	'ZUo$$e',	NULL,	0,	NULL,	NULL,	NULL,	'2022-01-07 09:32:23',	NULL,	NULL,	NULL,	'2022-01-07 09:32:23',	1,	'2022-01-07 09:32:23',	1),
(8,	2,	'Cliente',	'Cliente',	'cliente@cliente.com',	'https://35aniversarioalimerka.es/static/img/avatars/blank.png',	NULL,	0,	'787b934d0be36fd140762f29ba253a0d',	'0LkZVv70K*',	NULL,	0,	NULL,	NULL,	'2022-01-23 18:48:53',	'2022-01-13 14:44:26',	NULL,	NULL,	NULL,	'2022-01-13 14:44:26',	1,	'2022-01-23 18:48:53',	NULL),
(9,	2,	'María',	'Barrera',	'mariabarreraf@alimerka.es',	'https://35aniversarioalimerka.es/static/img/avatars/blank.png',	NULL,	0,	'5e824d74887779baba1499514b1900a9',	'T5sFGW?6+n',	NULL,	0,	NULL,	NULL,	'2022-04-18 16:33:45',	'2022-01-14 09:52:32',	NULL,	NULL,	NULL,	'2022-01-14 09:52:32',	2,	'2022-04-18 16:33:45',	NULL),
(10,	2,	'Ia',	'de diego',	'iadediego@alimerka.es',	'https://35aniversarioalimerka.es/static/img/avatars/blank.png',	NULL,	0,	'a38f502cf613b8dabc328e156a9ab21f',	'sj$I7VR4xY',	NULL,	0,	NULL,	NULL,	'2022-01-14 11:09:06',	'2022-01-14 10:00:06',	NULL,	NULL,	NULL,	'2022-01-14 10:00:06',	2,	'2022-01-14 11:09:06',	NULL),
(11,	2,	'David',	'Martín Vázquez',	'davidmartin@alimerka.es',	'https://35aniversarioalimerka.es/static/img/avatars/blank.png',	NULL,	0,	'edc4ef7403960586250f802bc65e0408',	'Rt{lJ7Q',	NULL,	0,	NULL,	NULL,	'2022-03-23 11:58:09',	'2022-01-18 13:49:50',	NULL,	NULL,	NULL,	'2022-01-14 12:13:21',	2,	'2022-03-23 11:58:09',	NULL),
(12,	1,	'Javier',	'Marchante',	'javier.marchante@gestycontrol.com',	'https://35aniversarioalimerka.es/static/img/avatars/blank.png',	NULL,	0,	'8cc9a30d969c5d22029567d91386e679',	'JPnAGY7qOZ',	'xcN5HqkAHoJ2OE0aSXI4CIrDsZ2TWHzyykbH1HktLClIvEqS3Ai4mBtxOw1V4voSWfY9AfQ7ouEzMGObk1ov2lxGONaj9NkH85Z6aUw6dfP2WJuTm9fYCLOgXHKbXsGuSrIXGPbN',	0,	NULL,	NULL,	NULL,	'2022-02-09 14:59:22',	'2022-02-09 14:59:33',	NULL,	NULL,	'2022-02-09 14:59:22',	1,	'2022-02-09 14:59:33',	NULL);

DROP TABLE IF EXISTS `navidad2022_user_logins`;
CREATE TABLE `navidad2022_user_logins` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID interno',
  `user_id` int(11) NOT NULL COMMENT 'Usuario',
  `ip` varchar(150) NOT NULL COMMENT 'IP',
  `user_agent` varchar(150) NOT NULL COMMENT 'Agente de usuario',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de modificación del registro',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  CONSTRAINT `navidad2022_user_logins_ibfk_2` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_user_logins_ibfk_3` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_user_logins_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Inicios de sesión';

DROP TABLE IF EXISTS `navidad2022_user_password_histories`;
CREATE TABLE `navidad2022_user_password_histories` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID interno',
  `user_id` int(11) NOT NULL COMMENT 'Usuario',
  `password_digest` varchar(150) NOT NULL COMMENT 'Hash de contraseña',
  `salt` varchar(10) NOT NULL COMMENT 'Salt de contraseña',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de modificación del registro',
  PRIMARY KEY (`id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `navidad2022_user_password_histories_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_user_password_histories_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_user_password_histories_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Historiales de contraseña';

DROP TABLE IF EXISTS `navidad2022_user_tfa_tokens`;
CREATE TABLE `navidad2022_user_tfa_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID interno',
  `user_id` int(11) NOT NULL COMMENT 'Usuario',
  `prefix` varchar(5) NOT NULL COMMENT 'Prefijo de token',
  `token` varchar(150) NOT NULL COMMENT 'Token',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de modificación del registro',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  KEY `prefix` (`prefix`),
  CONSTRAINT `navidad2022_user_tfa_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `navidad2022_users` (`id`),
  CONSTRAINT `navidad2022_user_tfa_tokens_ibfk_2` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_user_tfa_tokens_ibfk_3` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Inicios de sesión';


DROP TABLE IF EXISTS `navidad2022_webhooks`;
CREATE TABLE `navidad2022_webhooks` (
  `id` int(1) NOT NULL AUTO_INCREMENT COMMENT 'ID interno',
  `name` varchar(150) CHARACTER SET utf8 NOT NULL COMMENT 'Nombre',
  `method` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT 'POST' COMMENT 'Método HTTP de envío',
  `url` text CHARACTER SET utf8 NOT NULL COMMENT 'URL de destino',
  `is_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Activo',
  `is_data` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Enviar datos o no',
  `actions` text NOT NULL COMMENT 'Acciones que desencadenan el webhook',
  `models` text NOT NULL COMMENT 'Modelos que desencadenan el webhook',
  `secret` text NOT NULL COMMENT 'Secreto de firma',
  `created` datetime DEFAULT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de creación del registro',
  `modified` datetime DEFAULT NULL COMMENT 'Fecha de modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario de modificación del registro',
  PRIMARY KEY (`id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  CONSTRAINT `navidad2022_webhooks_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_webhooks_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `navidad2022_winner_page_sections`;
CREATE TABLE `navidad2022_winner_page_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'Título',
  `content` text COMMENT 'Contenido',
  `is_full_width` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Ancho completo',
  `created` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario que provocó la creación del registro',
  `modified` datetime NOT NULL COMMENT 'Fecha de última modificación del registro',
  `modified_by_user_id` int(11) DEFAULT NULL COMMENT 'Usuario que provocó la última modificación del registro',
  PRIMARY KEY (`id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  CONSTRAINT `navidad2022_winner_page_sections_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `navidad2022_winner_page_sections_ibfk_2` FOREIGN KEY (`modified_by_user_id`) REFERENCES `navidad2022_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Secciones de la página de ganadores';

ALTER TABLE `navidad2022_participants`
CHANGE `code` `code` varchar(18) COLLATE 'utf8mb4_general_ci' NOT NULL COMMENT 'Código' AFTER `id`,
CHANGE `code_promotion` `code_promotion` varchar(4) COLLATE 'utf8mb4_general_ci' NOT NULL COMMENT 'Código de promoción extraído del código' AFTER `code_supermarket`;