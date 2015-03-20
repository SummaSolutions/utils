-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 20-03-2015 a las 16:09:03
-- Versión del servidor: 5.5.41
-- Versión de PHP: 5.4.38-1+deb.sury.org~precise+2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `elburgues_uat`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `directory_country_region`
--

CREATE TABLE IF NOT EXISTS `directory_country_region` (
  `region_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Region Id',
  `country_id` varchar(4) NOT NULL DEFAULT '0' COMMENT 'Country Id in ISO-2',
  `code` varchar(32) DEFAULT NULL COMMENT 'Region code',
  `default_name` varchar(255) DEFAULT NULL COMMENT 'Region Name',
  PRIMARY KEY (`region_id`),
  KEY `IDX_DIRECTORY_COUNTRY_REGION_COUNTRY_ID` (`country_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Directory Country Region' AUTO_INCREMENT=510 ;

--
-- Volcado de datos para la tabla `directory_country_region`
--

INSERT INTO `directory_country_region` (`region_id`, `country_id`, `code`, `default_name`) VALUES
(485, 'AR', 'Ciudad Autónoma de Buenos Aires', 'Ciudad Autónoma de Buenos Aires'),
(486, 'AR', 'Buenos Aires - GBA', 'Buenos Aires - GBA'),
(487, 'AR', 'Buenos Aires - Interior', 'Buenos Aires - Interior'),
(488, 'AR', 'Catamarca', 'Catamarca'),
(489, 'AR', 'Chaco', 'Chaco'),
(490, 'AR', 'Chubut', 'Chubut'),
(491, 'AR', 'Córdoba', 'Córdoba'),
(492, 'AR', 'Corrientes', 'Corrientes'),
(493, 'AR', 'Entre Ríos', 'Entre Ríos'),
(494, 'AR', 'Formosa', 'Formosa'),
(495, 'AR', 'Jujuy', 'Jujuy'),
(496, 'AR', 'La Pampa', 'La Pampa'),
(497, 'AR', 'La Rioja', 'La Rioja'),
(498, 'AR', 'Mendoza', 'Mendoza'),
(499, 'AR', 'Misiones', 'Misiones'),
(500, 'AR', 'Neuquén', 'Neuquén'),
(501, 'AR', 'Río Negro', 'Río Negro'),
(502, 'AR', 'Salta', 'Salta'),
(503, 'AR', 'San Juan', 'San Juan'),
(504, 'AR', 'San Luis', 'San Luis'),
(505, 'AR', 'Santa Cruz', 'Santa Cruz'),
(506, 'AR', 'Santa Fe', 'Santa Fe'),
(507, 'AR', 'Santiago del Estero', 'Santiago del Estero'),
(508, 'AR', 'Tucumán', 'Tucumán'),
(509, 'AR', 'Tierra del Fuego', 'Tierra del Fuego');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
