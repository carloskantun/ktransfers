-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 08-05-2026 a las 02:21:43
-- Versión del servidor: 11.8.6-MariaDB-log
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u372499129_express`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `airlines`
--

CREATE TABLE `airlines` (
  `id` bigint(20) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `airlines`
--

INSERT INTO `airlines` (`id`, `code`, `name`, `is_active`, `created_at`) VALUES
(1, 'AF', 'Air France', 1, '2026-04-09 18:57:55'),
(2, 'AR', 'Aerolíneas Argentinas', 1, '2026-04-09 18:57:55'),
(3, 'AS', 'Alaska Airlines', 1, '2026-04-09 18:57:55'),
(4, 'AV', 'Avianca', 1, '2026-04-09 18:57:55'),
(5, 'BA', 'British Airways', 1, '2026-04-09 18:57:55'),
(6, 'B6', 'JetBlue Airways', 1, '2026-04-09 18:57:55'),
(7, 'CM', 'Copa Airlines', 1, '2026-04-09 18:57:55'),
(8, 'DE', 'Condor', 1, '2026-04-09 18:57:55'),
(9, 'EK', 'Emirates', 1, '2026-04-09 18:57:55'),
(10, 'EW', 'Eurowings', 1, '2026-04-09 18:57:55'),
(11, 'EY', 'Etihad Airways', 1, '2026-04-09 18:57:55'),
(12, 'G3', 'GOL Linhas Aéreas', 1, '2026-04-09 18:57:55'),
(13, 'G4', 'Allegiant Air', 1, '2026-04-09 18:57:55'),
(14, 'IB', 'Iberia', 1, '2026-04-09 18:57:55'),
(15, 'JL', 'Japan Airlines', 1, '2026-04-09 18:57:55'),
(16, 'KL', 'KLM', 1, '2026-04-09 18:57:55'),
(17, 'LH', 'Lufthansa', 1, '2026-04-09 18:57:55'),
(18, 'LX', 'Swiss International Air Lines', 1, '2026-04-09 18:57:55'),
(19, 'NH', 'ANA', 1, '2026-04-09 18:57:55'),
(20, 'NZ', 'Air New Zealand', 1, '2026-04-09 18:57:55'),
(21, 'QF', 'Qantas', 1, '2026-04-09 18:57:55'),
(22, 'SY', 'Sun Country Airlines', 1, '2026-04-09 18:57:55'),
(23, 'TK', 'Turkish Airlines', 1, '2026-04-09 18:57:55'),
(24, 'TS', 'Air Transat', 1, '2026-04-09 18:57:55'),
(25, 'UX', 'Air Europa', 1, '2026-04-09 18:57:55'),
(26, 'VA', 'Virgin Australia', 1, '2026-04-09 18:57:55'),
(27, 'VS', 'Virgin Atlantic', 1, '2026-04-09 18:57:55'),
(28, 'WN', 'Southwest Airlines', 1, '2026-04-09 18:57:55'),
(29, '4C', 'LATAM Colombia', 1, '2026-04-09 18:57:55'),
(30, '4M', 'LATAM Argentina', 1, '2026-04-09 18:57:55'),
(31, '4O', 'Interjet', 1, '2026-04-09 18:57:55'),
(32, '7M', 'MAYAir', 1, '2026-04-09 18:57:55'),
(33, '9J', 'Dana Airlines', 1, '2026-04-09 18:57:55'),
(34, '9N', 'Tropic Air', 1, '2026-04-09 18:57:55'),
(35, 'AA', 'American Airlines', 1, '2026-04-09 18:57:55'),
(36, 'AB', 'Air Berlin', 1, '2026-04-09 18:57:55'),
(37, 'AC', 'Air Canada', 1, '2026-04-09 18:57:55'),
(38, 'AM', 'Aeroméxico', 1, '2026-04-09 18:57:55'),
(39, 'BV', 'Blue Panorama Airlines', 1, '2026-04-09 18:57:55'),
(40, 'CU', 'Cubana de Aviación', 1, '2026-04-09 18:57:55'),
(41, 'DL', 'Delta Air Lines', 1, '2026-04-09 18:57:55'),
(42, 'E9', 'Evelop Airlines', 1, '2026-04-09 18:57:55'),
(43, 'EB', 'Wamos Air / Pullmantur Air', 1, '2026-04-09 18:57:55'),
(44, 'F9', 'Frontier Airlines', 1, '2026-04-09 18:57:55'),
(45, 'JJ', 'LATAM Brasil', 1, '2026-04-09 18:57:55'),
(46, 'KM', 'KM Malta Airlines', 1, '2026-04-09 18:57:55'),
(47, 'LA', 'LATAM Airlines', 1, '2026-04-09 18:57:55'),
(48, 'LP', 'LATAM Perú', 1, '2026-04-09 18:57:55'),
(49, 'MY', 'Maya Island Air', 1, '2026-04-09 18:57:55'),
(50, 'NK', 'Spirit Airlines', 1, '2026-04-09 18:57:55'),
(51, 'NO', 'Neos', 1, '2026-04-09 18:57:55'),
(52, 'P5', 'Wingo', 1, '2026-04-09 18:57:55'),
(53, 'PZ', 'LATAM Paraguay', 1, '2026-04-09 18:57:55'),
(54, 'Q6', 'Skytrans', 1, '2026-04-09 18:57:55'),
(55, 'SE', 'XL Airways', 1, '2026-04-09 18:57:55'),
(56, 'SU', 'Aeroflot', 1, '2026-04-09 18:57:55'),
(57, 'SWG', 'Sunwing Airlines', 1, '2026-04-09 18:57:55'),
(58, 'SWQ', 'Swift Air', 1, '2026-04-09 18:57:55'),
(59, 'TB', 'Jetairfly / TUI fly Belgium', 1, '2026-04-09 18:57:55'),
(60, 'TCX', 'Thomas Cook Airlines', 1, '2026-04-09 18:57:55'),
(61, 'TOM', 'TUI Airways', 1, '2026-04-09 18:57:55'),
(62, 'UA', 'United Airlines', 1, '2026-04-09 18:57:55'),
(63, 'UN', 'Transaero', 1, '2026-04-09 18:57:55'),
(64, 'VB', 'VivaAerobus', 1, '2026-04-09 18:57:55'),
(65, 'VW', 'Aeromar', 1, '2026-04-09 18:57:55'),
(66, 'VX', 'Virgin America', 1, '2026-04-09 18:57:55'),
(67, 'WK', 'Edelweiss Air', 1, '2026-04-09 18:57:55'),
(68, 'WS', 'WestJet', 1, '2026-04-09 18:57:55'),
(69, 'Y4', 'Volaris', 1, '2026-04-09 18:57:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `assignments`
--

CREATE TABLE `assignments` (
  `id` bigint(20) NOT NULL,
  `booking_id` bigint(20) NOT NULL,
  `mode` enum('INTERNAL','PROVIDER') NOT NULL DEFAULT 'INTERNAL',
  `provider_id` bigint(20) DEFAULT NULL,
  `vehicle_id` bigint(20) DEFAULT NULL,
  `operator_user_id` bigint(20) DEFAULT NULL,
  `service_status` enum('PENDING','ASSIGNED','IN_PROGRESS','DONE','NO_SHOW') NOT NULL DEFAULT 'PENDING',
  `assigned_at` datetime DEFAULT NULL,
  `done_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `assignments`
--

INSERT INTO `assignments` (`id`, `booking_id`, `mode`, `provider_id`, `vehicle_id`, `operator_user_id`, `service_status`, `assigned_at`, `done_at`) VALUES
(1, 1, 'INTERNAL', NULL, NULL, NULL, 'PENDING', NULL, NULL),
(2, 2, 'INTERNAL', NULL, 2, 2, 'PENDING', '2026-05-07 00:38:39', NULL),
(3, 3, 'INTERNAL', NULL, 3, 2, 'ASSIGNED', '2026-05-07 20:31:53', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_log`
--

CREATE TABLE `audit_log` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `entity` varchar(80) NOT NULL,
  `entity_id` bigint(20) DEFAULT NULL,
  `meta_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_json`)),
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) NOT NULL,
  `booking_code` varchar(30) NOT NULL,
  `trip_type` enum('ONE_WAY','ROUND_TRIP') NOT NULL,
  `operation_type` enum('AIRPORT','INTERHOTEL') NOT NULL DEFAULT 'AIRPORT',
  `direction` enum('AIRPORT_TO_DESTINATION','DESTINATION_TO_AIRPORT') NOT NULL,
  `service_type_id` bigint(20) NOT NULL,
  `zone_id` bigint(20) NOT NULL,
  `place_id` bigint(20) NOT NULL,
  `origin_name` varchar(190) DEFAULT NULL,
  `destination_name` varchar(190) DEFAULT NULL,
  `currency_code` char(3) NOT NULL,
  `price_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `agency_collected_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `agency_collected_at` datetime DEFAULT NULL,
  `status` enum('PENDING','CONFIRMED','CANCELLED','NO_SHOW','COMPLETED') NOT NULL DEFAULT 'PENDING',
  `payment_status` enum('UNPAID','PARTIAL','PAID','REFUNDED') NOT NULL DEFAULT 'UNPAID',
  `arrival_datetime` datetime DEFAULT NULL,
  `departure_datetime` datetime DEFAULT NULL,
  `airline` varchar(120) DEFAULT NULL,
  `flight_number` varchar(40) DEFAULT NULL,
  `terminal` varchar(60) DEFAULT NULL,
  `pickup_notes` varchar(255) DEFAULT NULL,
  `customer_name` varchar(120) NOT NULL,
  `customer_last_name` varchar(120) DEFAULT NULL,
  `customer_email` varchar(190) NOT NULL,
  `customer_phone` varchar(60) DEFAULT NULL,
  `agency_name` varchar(190) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_by_user_id` bigint(20) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `bookings`
--

INSERT INTO `bookings` (`id`, `booking_code`, `trip_type`, `operation_type`, `direction`, `service_type_id`, `zone_id`, `place_id`, `origin_name`, `destination_name`, `currency_code`, `price_total`, `agency_collected_total`, `agency_collected_at`, `status`, `payment_status`, `arrival_datetime`, `departure_datetime`, `airline`, `flight_number`, `terminal`, `pickup_notes`, `customer_name`, `customer_last_name`, `customer_email`, `customer_phone`, `agency_name`, `comments`, `created_by_user_id`, `created_at`, `updated_at`) VALUES
(1, 'KTR-20260410-2114', 'ONE_WAY', 'AIRPORT', 'AIRPORT_TO_DESTINATION', 1, 1, 17, 'Aeropuerto de Cancun', 'Riu Cancun', 'MXN', 699.00, 0.00, NULL, 'CONFIRMED', 'UNPAID', '2026-04-11 15:50:00', NULL, 'Viva Aerobús', 'VB1370', '2', '1 asiento bebe', 'Lucia', 'Zapata', 'info@expresstransfercancun.com', '9982422584', 'NA', NULL, NULL, '2026-04-10 20:51:43', '2026-05-06 23:41:05'),
(2, 'KTR-20260507-324F', 'ONE_WAY', 'INTERHOTEL', 'AIRPORT_TO_DESTINATION', 1, 6, 81, 'Grand Oasis Cancun', 'Villa del Palmar Playa Mujeres', 'USD', 85.00, 0.00, NULL, 'PENDING', 'UNPAID', '2026-05-21 19:35:00', NULL, NULL, NULL, '4', 'tester', 'Carlos', 'Kantun', 'carloskantun@live.com', '9983562096', 'Chistopher Guitierrez', NULL, 3, '2026-05-07 00:36:28', '2026-05-07 00:38:39'),
(3, 'KTR-20260507-A22F', 'ONE_WAY', 'AIRPORT', 'AIRPORT_TO_DESTINATION', 1, 4, 7, NULL, 'Hilton Playa del Carmen', 'MXN', 2000.00, 0.00, NULL, 'PENDING', 'UNPAID', '2026-05-07 15:26:00', NULL, 'volaris', 'A-12365', '4', NULL, 'CHRISTOPHER JONATHAN', 'GUTIERREZ CAJUM', 'chrisgc190123@gmail.com', '9984804079', NULL, NULL, 1, '2026-05-07 20:31:53', NULL),
(4, 'KTR-20260508-7ED9', 'ROUND_TRIP', 'INTERHOTEL', 'AIRPORT_TO_DESTINATION', 1, 2, 3, 'Aeropuerto Internacional de Cancún (CUN)', 'Smart Cancun by Oasis', 'MXN', 0.00, 0.00, NULL, 'PENDING', 'UNPAID', NULL, NULL, 'volaris', 'A-12365', '4', NULL, 'CHRISTOPHER JONATHAN', 'GUTIERREZ CAJUM', 'chrisgc190123@gmail.com', '9984804079', NULL, NULL, 1, '2026-05-08 00:22:21', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `booking_passengers`
--

CREATE TABLE `booking_passengers` (
  `booking_id` bigint(20) NOT NULL,
  `adults` int(11) NOT NULL DEFAULT 1,
  `children` int(11) NOT NULL DEFAULT 0,
  `total_pax` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `booking_passengers`
--

INSERT INTO `booking_passengers` (`booking_id`, `adults`, `children`, `total_pax`) VALUES
(1, 1, 0, 1),
(2, 1, 0, 1),
(3, 6, 0, 6),
(4, 10, 0, 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `booking_payments`
--

CREATE TABLE `booking_payments` (
  `id` bigint(20) NOT NULL,
  `booking_id` bigint(20) NOT NULL,
  `method` enum('PAYPAL','CARD','BANK','CASH','MANUAL') NOT NULL,
  `status` enum('PENDING','PAID','FAILED','REFUNDED') NOT NULL DEFAULT 'PENDING',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency_code` char(3) NOT NULL,
  `reference` varchar(190) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `booking_status_history`
--

CREATE TABLE `booking_status_history` (
  `id` bigint(20) NOT NULL,
  `booking_id` bigint(20) NOT NULL,
  `old_status` varchar(30) DEFAULT NULL,
  `new_status` varchar(30) NOT NULL,
  `changed_by` bigint(20) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `countries`
--

CREATE TABLE `countries` (
  `id` bigint(20) NOT NULL,
  `iso2` char(2) NOT NULL,
  `iso3` char(3) NOT NULL,
  `phone_code` varchar(10) DEFAULT NULL,
  `name_en` varchar(120) NOT NULL,
  `name_es` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `countries`
--

INSERT INTO `countries` (`id`, `iso2`, `iso3`, `phone_code`, `name_en`, `name_es`, `is_active`, `created_at`) VALUES
(1, 'MX', 'MEX', '52', 'Mexico', 'México', 1, '2026-04-09 18:57:55'),
(2, 'US', 'USA', '1', 'United States', 'Estados Unidos', 1, '2026-04-09 18:57:55'),
(3, 'CA', 'CAN', '1', 'Canada', 'Canadá', 1, '2026-04-09 18:57:55'),
(4, 'GB', 'GBR', '44', 'United Kingdom', 'Reino Unido', 1, '2026-04-09 18:57:55'),
(5, 'ES', 'ESP', '34', 'Spain', 'España', 1, '2026-04-09 18:57:55'),
(6, 'FR', 'FRA', '33', 'France', 'Francia', 1, '2026-04-09 18:57:55'),
(7, 'DE', 'DEU', '49', 'Germany', 'Alemania', 1, '2026-04-09 18:57:55'),
(8, 'IT', 'ITA', '39', 'Italy', 'Italia', 1, '2026-04-09 18:57:55'),
(9, 'NL', 'NLD', '31', 'Netherlands', 'Países Bajos', 1, '2026-04-09 18:57:55'),
(10, 'BE', 'BEL', '32', 'Belgium', 'Bélgica', 1, '2026-04-09 18:57:55'),
(11, 'CH', 'CHE', '41', 'Switzerland', 'Suiza', 1, '2026-04-09 18:57:55'),
(12, 'AT', 'AUT', '43', 'Austria', 'Austria', 1, '2026-04-09 18:57:55'),
(13, 'IE', 'IRL', '353', 'Ireland', 'Irlanda', 1, '2026-04-09 18:57:55'),
(14, 'PT', 'PRT', '351', 'Portugal', 'Portugal', 1, '2026-04-09 18:57:55'),
(15, 'SE', 'SWE', '46', 'Sweden', 'Suecia', 1, '2026-04-09 18:57:55'),
(16, 'NO', 'NOR', '47', 'Norway', 'Noruega', 1, '2026-04-09 18:57:55'),
(17, 'DK', 'DNK', '45', 'Denmark', 'Dinamarca', 1, '2026-04-09 18:57:55'),
(18, 'FI', 'FIN', '358', 'Finland', 'Finlandia', 1, '2026-04-09 18:57:55'),
(19, 'IS', 'ISL', '354', 'Iceland', 'Islandia', 1, '2026-04-09 18:57:55'),
(20, 'PL', 'POL', '48', 'Poland', 'Polonia', 1, '2026-04-09 18:57:55'),
(21, 'CZ', 'CZE', '420', 'Czechia', 'Chequia', 1, '2026-04-09 18:57:55'),
(22, 'HU', 'HUN', '36', 'Hungary', 'Hungría', 1, '2026-04-09 18:57:55'),
(23, 'RO', 'ROU', '40', 'Romania', 'Rumania', 1, '2026-04-09 18:57:55'),
(24, 'RU', 'RUS', '7', 'Russia', 'Rusia', 1, '2026-04-09 18:57:55'),
(25, 'TR', 'TUR', '90', 'Turkey', 'Turquía', 1, '2026-04-09 18:57:55'),
(26, 'BR', 'BRA', '55', 'Brazil', 'Brasil', 1, '2026-04-09 18:57:55'),
(27, 'AR', 'ARG', '54', 'Argentina', 'Argentina', 1, '2026-04-09 18:57:55'),
(28, 'CL', 'CHL', '56', 'Chile', 'Chile', 1, '2026-04-09 18:57:55'),
(29, 'CO', 'COL', '57', 'Colombia', 'Colombia', 1, '2026-04-09 18:57:55'),
(30, 'PE', 'PER', '51', 'Peru', 'Perú', 1, '2026-04-09 18:57:55'),
(31, 'EC', 'ECU', '593', 'Ecuador', 'Ecuador', 1, '2026-04-09 18:57:55'),
(32, 'VE', 'VEN', '58', 'Venezuela', 'Venezuela', 1, '2026-04-09 18:57:55'),
(33, 'UY', 'URY', '598', 'Uruguay', 'Uruguay', 1, '2026-04-09 18:57:55'),
(34, 'PY', 'PRY', '595', 'Paraguay', 'Paraguay', 1, '2026-04-09 18:57:55'),
(35, 'BO', 'BOL', '591', 'Bolivia', 'Bolivia', 1, '2026-04-09 18:57:55'),
(36, 'CR', 'CRI', '506', 'Costa Rica', 'Costa Rica', 1, '2026-04-09 18:57:55'),
(37, 'PA', 'PAN', '507', 'Panama', 'Panamá', 1, '2026-04-09 18:57:55'),
(38, 'GT', 'GTM', '502', 'Guatemala', 'Guatemala', 1, '2026-04-09 18:57:55'),
(39, 'SV', 'SLV', '503', 'El Salvador', 'El Salvador', 1, '2026-04-09 18:57:55'),
(40, 'HN', 'HND', '504', 'Honduras', 'Honduras', 1, '2026-04-09 18:57:55'),
(41, 'NI', 'NIC', '505', 'Nicaragua', 'Nicaragua', 1, '2026-04-09 18:57:55'),
(42, 'DO', 'DOM', '1', 'Dominican Republic', 'República Dominicana', 1, '2026-04-09 18:57:55'),
(43, 'CU', 'CUB', '53', 'Cuba', 'Cuba', 1, '2026-04-09 18:57:55'),
(44, 'JM', 'JAM', '1', 'Jamaica', 'Jamaica', 1, '2026-04-09 18:57:55'),
(45, 'BS', 'BHS', '1', 'Bahamas', 'Bahamas', 1, '2026-04-09 18:57:55'),
(46, 'AU', 'AUS', '61', 'Australia', 'Australia', 1, '2026-04-09 18:57:55'),
(47, 'NZ', 'NZL', '64', 'New Zealand', 'Nueva Zelanda', 1, '2026-04-09 18:57:55'),
(48, 'JP', 'JPN', '81', 'Japan', 'Japón', 1, '2026-04-09 18:57:55'),
(49, 'KR', 'KOR', '82', 'South Korea', 'Corea del Sur', 1, '2026-04-09 18:57:55'),
(50, 'CN', 'CHN', '86', 'China', 'China', 1, '2026-04-09 18:57:55'),
(51, 'IN', 'IND', '91', 'India', 'India', 1, '2026-04-09 18:57:55'),
(52, 'AE', 'ARE', '971', 'United Arab Emirates', 'Emiratos Árabes Unidos', 1, '2026-04-09 18:57:55'),
(53, 'SA', 'SAU', '966', 'Saudi Arabia', 'Arabia Saudita', 1, '2026-04-09 18:57:55'),
(54, 'IL', 'ISR', '972', 'Israel', 'Israel', 1, '2026-04-09 18:57:55'),
(55, 'ZA', 'ZAF', '27', 'South Africa', 'Sudáfrica', 1, '2026-04-09 18:57:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `currencies`
--

CREATE TABLE `currencies` (
  `code` char(3) NOT NULL,
  `name` varchar(60) NOT NULL,
  `symbol` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `currencies`
--

INSERT INTO `currencies` (`code`, `name`, `symbol`, `is_active`) VALUES
('CAD', 'Canadian Dollar', '$', 1),
('EUR', 'Euro', '€', 1),
('MXN', 'Mexican Peso', '$', 1),
('USD', 'US Dollar', '$', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(11) NOT NULL,
  `filename` varchar(190) NOT NULL,
  `executed_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `filename`, `executed_at`) VALUES
(1, '008_seed_cancun_catalog.sql', '2026-04-09 18:57:55'),
(2, '009_seed_cancun_legacy_expansion.sql', '2026-04-09 18:57:55'),
(3, '010_seed_airlines_catalog_full.sql', '2026-04-09 18:57:55'),
(4, '011_seed_places_cancun_expanded.sql', '2026-04-09 18:57:55'),
(5, '012_seed_countries_catalog.sql', '2026-04-09 18:57:55'),
(6, '013_seed_rate_rules_luxury_and_gaps.sql', '2026-04-09 18:57:55'),
(7, '014_import_places_from_fzn3_hotels.sql', '2026-04-09 18:57:55'),
(8, '015_roles_content_operations.sql', '2026-04-09 18:57:55'),
(9, '001_init.sql', '2026-05-06 23:56:50'),
(10, '002_rbac.sql', '2026-05-06 23:56:58'),
(11, '003_catalog.sql', '2026-05-07 00:16:26'),
(12, '004_pricing.sql', '2026-05-07 00:16:26'),
(13, '005_bookings.sql', '2026-05-07 00:16:26'),
(14, '006_operations_accounting.sql', '2026-05-07 00:16:26'),
(15, '007_airlines.sql', '2026-05-07 00:16:26'),
(16, '016_booking_operation_sheet_fields.sql', '2026-05-07 00:16:26'),
(17, '017_booking_operation_type.sql', '2026-05-07 00:16:26'),
(18, '018_agency_role_booking_ownership.sql', '2026-05-07 00:33:52'),
(19, '019_partner_roles_labels.sql', '2026-05-07 00:33:52'),
(20, '020_providers_contact_name.sql', '2026-05-08 01:02:03'),
(21, '021_agency_manual_collection.sql', '2026-05-08 01:52:54'),
(22, '021_superadmin_home_settings.sql', '2026-05-08 02:06:29'),
(23, '022_superadmin_home_settings.sql', '2026-05-08 02:06:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pax_ranges`
--

CREATE TABLE `pax_ranges` (
  `id` bigint(20) NOT NULL,
  `label` varchar(30) NOT NULL,
  `min_pax` int(11) NOT NULL,
  `max_pax` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pax_ranges`
--

INSERT INTO `pax_ranges` (`id`, `label`, `min_pax`, `max_pax`, `sort_order`) VALUES
(1, '1-3', 1, 3, 10),
(2, '4-5', 4, 5, 20),
(3, '6-8', 6, 8, 30),
(4, '9-12', 9, 12, 40),
(5, '13-16', 13, 16, 50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) NOT NULL,
  `code` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permissions`
--

INSERT INTO `permissions` (`id`, `code`, `description`) VALUES
(1, 'dashboard.view', 'Ver dashboard administrativo'),
(2, 'bookings.view', 'Ver listado de reservas'),
(3, 'bookings.manage', 'Crear y editar reservas'),
(4, 'catalog.manage', 'Administrar catalogos, unidades y proveedores'),
(5, 'pricing.manage', 'Administrar reglas de precios'),
(6, 'operations.view', 'Ver y actualizar orden del dia'),
(7, 'accounting.view', 'Ver contabilidad'),
(8, 'kpis.view', 'Ver indicadores'),
(9, 'users.manage', 'Administrar usuarios y roles'),
(10, 'content.manage', 'Editar contenido del sitio'),
(11, 'bookings.create', 'Crear reservas propias sin administrar precio ni operacion'),
(23, 'home.manage', 'Administrar configuracion de home/branding');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `places`
--

CREATE TABLE `places` (
  `id` bigint(20) NOT NULL,
  `zone_id` bigint(20) NOT NULL,
  `type` enum('HOTEL','AIRBNB','POINT') NOT NULL DEFAULT 'HOTEL',
  `name` varchar(190) NOT NULL,
  `city` varchar(120) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `places`
--

INSERT INTO `places` (`id`, `zone_id`, `type`, `name`, `city`, `is_active`, `created_at`) VALUES
(1, 1, 'HOTEL', 'Grand Oasis Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(2, 1, 'HOTEL', 'JW Marriott Cancun Resort & Spa', 'Cancún', 1, '2026-04-09 18:57:55'),
(3, 2, 'HOTEL', 'Smart Cancun by Oasis', 'Cancún', 1, '2026-04-09 18:57:55'),
(4, 2, 'POINT', 'ADO Cancún Centro', 'Cancún', 1, '2026-04-09 18:57:55'),
(5, 3, 'HOTEL', 'Dreams Sapphire Resort & Spa', 'Puerto Morelos', 1, '2026-04-09 18:57:55'),
(6, 3, 'HOTEL', 'Hyatt Ziva Riviera Cancun', 'Puerto Morelos', 1, '2026-04-09 18:57:55'),
(7, 4, 'HOTEL', 'Hilton Playa del Carmen', 'Playa del Carmen', 1, '2026-04-09 18:57:55'),
(8, 4, 'POINT', 'Quinta Avenida', 'Playa del Carmen', 1, '2026-04-09 18:57:55'),
(9, 5, 'HOTEL', 'Hilton Tulum Riviera Maya', 'Tulum', 1, '2026-04-09 18:57:55'),
(10, 5, 'POINT', 'Zona Arqueológica de Tulum', 'Tulum', 1, '2026-04-09 18:57:55'),
(11, 8, 'HOTEL', 'Bahia Principe Tulum', 'Akumal', 1, '2026-04-09 18:57:55'),
(12, 8, 'HOTEL', 'Bahia Principe Akumal', 'Akumal', 1, '2026-04-09 18:57:55'),
(13, 8, 'HOTEL', 'Secrets Akumal Riviera Maya', 'Akumal', 1, '2026-04-09 18:57:55'),
(14, 2, 'HOTEL', 'Courtyard by Marriott Cancun Airport', 'Cancún', 1, '2026-04-09 18:57:55'),
(15, 2, 'HOTEL', 'Ibis Cancun Centro', 'Cancún', 1, '2026-04-09 18:57:55'),
(16, 2, 'HOTEL', 'Four Points by Sheraton Cancun Centro', 'Cancún', 1, '2026-04-09 18:57:55'),
(17, 1, 'HOTEL', 'Riu Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(18, 1, 'HOTEL', 'Iberostar Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(19, 1, 'HOTEL', 'Moon Palace Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(20, 1, 'HOTEL', 'Le Blanc Spa Resort Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(21, 1, 'HOTEL', 'Hyatt Zilara Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(22, 1, 'HOTEL', 'Hyatt Ziva Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(23, 9, 'HOTEL', 'Belmond Maroma Resort & Spa', 'Maroma', 1, '2026-04-09 18:57:55'),
(24, 9, 'HOTEL', 'Catalonia Playa Maroma', 'Maroma', 1, '2026-04-09 18:57:55'),
(25, 9, 'HOTEL', 'Secrets Maroma Beach Riviera Cancun', 'Maroma', 1, '2026-04-09 18:57:55'),
(26, 4, 'POINT', 'Xcaret', 'Playa del Carmen', 1, '2026-04-09 18:57:55'),
(27, 4, 'HOTEL', 'Playacar Palace', 'Playa del Carmen', 1, '2026-04-09 18:57:55'),
(28, 4, 'HOTEL', 'Paradisus Playa del Carmen', 'Playa del Carmen', 1, '2026-04-09 18:57:55'),
(29, 4, 'HOTEL', 'Grand Hyatt Playa del Carmen Resort', 'Playa del Carmen', 1, '2026-04-09 18:57:55'),
(30, 6, 'HOTEL', 'Finest Playa Mujeres', 'Cancún', 1, '2026-04-09 18:57:55'),
(31, 6, 'HOTEL', 'Excellence Playa Mujeres', 'Cancún', 1, '2026-04-09 18:57:55'),
(32, 6, 'HOTEL', 'Beloved Playa Mujeres', 'Cancún', 1, '2026-04-09 18:57:55'),
(33, 7, 'HOTEL', 'Catalonia Riviera Maya', 'Puerto Aventuras', 1, '2026-04-09 18:57:55'),
(34, 7, 'HOTEL', 'Dreams Puerto Aventuras Resort & Spa', 'Puerto Aventuras', 1, '2026-04-09 18:57:55'),
(35, 3, 'HOTEL', 'Ocean Coral & Turquesa', 'Puerto Morelos', 1, '2026-04-09 18:57:55'),
(36, 3, 'HOTEL', 'Excellence Riviera Cancun', 'Puerto Morelos', 1, '2026-04-09 18:57:55'),
(37, 3, 'HOTEL', 'Dreams Riviera Cancun Resort & Spa', 'Puerto Morelos', 1, '2026-04-09 18:57:55'),
(38, 5, 'HOTEL', 'Azulik Tulum', 'Tulum', 1, '2026-04-09 18:57:55'),
(39, 5, 'HOTEL', 'Be Tulum Hotel', 'Tulum', 1, '2026-04-09 18:57:55'),
(40, 5, 'HOTEL', 'Dreams Tulum Resort & Spa', 'Tulum', 1, '2026-04-09 18:57:55'),
(42, 8, 'HOTEL', 'Grand Sirenis Riviera Maya', 'Akumal', 1, '2026-04-09 18:57:55'),
(43, 8, 'HOTEL', 'Bahia Principe Sian Kaan', 'Akumal', 1, '2026-04-09 18:57:55'),
(44, 8, 'HOTEL', 'Bahia Principe Coba', 'Akumal', 1, '2026-04-09 18:57:55'),
(45, 8, 'HOTEL', 'Akumal Bay Beach & Wellness Resort', 'Akumal', 1, '2026-04-09 18:57:55'),
(46, 2, 'POINT', 'Puerto Juárez Ultramar', 'Cancún', 1, '2026-04-09 18:57:55'),
(47, 2, 'POINT', 'Aeropuerto Internacional de Cancún (CUN)', 'Cancún', 1, '2026-04-09 18:57:55'),
(48, 2, 'HOTEL', 'One Cancun Centro', 'Cancún', 1, '2026-04-09 18:57:55'),
(49, 2, 'HOTEL', 'Krystal Urban Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(50, 2, 'HOTEL', 'Fiesta Inn Cancun Las Americas', 'Cancún', 1, '2026-04-09 18:57:55'),
(51, 2, 'HOTEL', 'City Express Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(52, 2, 'HOTEL', 'Ambiance Suites', 'Cancún', 1, '2026-04-09 18:57:55'),
(53, 2, 'HOTEL', 'Adhara Hacienda Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(54, 1, 'HOTEL', 'Riu Palace Peninsula', 'Cancún', 1, '2026-04-09 18:57:55'),
(55, 1, 'HOTEL', 'Riu Palace Las Americas', 'Cancún', 1, '2026-04-09 18:57:55'),
(56, 1, 'HOTEL', 'Sun Palace', 'Cancún', 1, '2026-04-09 18:57:55'),
(57, 1, 'HOTEL', 'The Westin Resort & Spa Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(58, 1, 'HOTEL', 'The Ritz-Carlton Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(59, 1, 'HOTEL', 'Secrets The Vine Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(60, 1, 'HOTEL', 'Paradisus Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(61, 1, 'HOTEL', 'Nizuc Resort and Spa', 'Cancún', 1, '2026-04-09 18:57:55'),
(62, 1, 'HOTEL', 'Live Aqua Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(63, 1, 'HOTEL', 'Krystal Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(64, 1, 'HOTEL', 'Hyatt Regency Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(65, 1, 'HOTEL', 'Hard Rock Hotel Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(66, 1, 'HOTEL', 'Fiesta Americana Condesa Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(67, 1, 'HOTEL', 'Dreams Sands Cancun Resort & Spa', 'Cancún', 1, '2026-04-09 18:57:55'),
(68, 1, 'HOTEL', 'Beach Palace', 'Cancún', 1, '2026-04-09 18:57:55'),
(69, 1, 'HOTEL', 'Aloft Cancun', 'Cancún', 1, '2026-04-09 18:57:55'),
(70, 9, 'HOTEL', 'El Dorado Maroma', 'Maroma', 1, '2026-04-09 18:57:55'),
(71, 9, 'HOTEL', 'Catalonia Privileged Maroma', 'Maroma', 1, '2026-04-09 18:57:55'),
(72, 4, 'POINT', 'Quinta Avenida Playa del Carmen', 'Playa del Carmen', 1, '2026-04-09 18:57:55'),
(73, 4, 'HOTEL', 'The Reef Playacar', 'Playa del Carmen', 1, '2026-04-09 18:57:55'),
(74, 4, 'HOTEL', 'Sandos Playacar Beach Resort', 'Playa del Carmen', 1, '2026-04-09 18:57:55'),
(75, 4, 'HOTEL', 'Riu Yucatan', 'Playa del Carmen', 1, '2026-04-09 18:57:55'),
(76, 4, 'HOTEL', 'Riu Palace Mexico', 'Playa del Carmen', 1, '2026-04-09 18:57:55'),
(77, 4, 'HOTEL', 'Riu Palace Riviera Maya', 'Playa del Carmen', 1, '2026-04-09 18:57:55'),
(78, 4, 'HOTEL', 'Mahekal Beach Resort', 'Playa del Carmen', 1, '2026-04-09 18:57:55'),
(79, 4, 'HOTEL', 'Grand Velas Riviera Maya', 'Playa del Carmen', 1, '2026-04-09 18:57:55'),
(80, 4, 'HOTEL', 'Fairmont Mayakoba', 'Playa del Carmen', 1, '2026-04-09 18:57:55'),
(81, 6, 'HOTEL', 'Villa del Palmar Playa Mujeres', 'Cancún', 1, '2026-04-09 18:57:55'),
(82, 6, 'HOTEL', 'Secrets Playa Mujeres Golf & Spa Resort', 'Cancún', 1, '2026-04-09 18:57:55'),
(83, 7, 'HOTEL', 'Hard Rock Hotel Riviera Maya', 'Puerto Aventuras', 1, '2026-04-09 18:57:55'),
(84, 7, 'HOTEL', 'Catalonia Yucatan Beach', 'Puerto Aventuras', 1, '2026-04-09 18:57:55'),
(85, 3, 'HOTEL', 'Zoetry Paraiso de la Bonita', 'Puerto Morelos', 1, '2026-04-09 18:57:55'),
(86, 3, 'HOTEL', 'Royalton Riviera Cancun Resort & Spa', 'Puerto Morelos', 1, '2026-04-09 18:57:55'),
(87, 3, 'HOTEL', 'Now Sapphire Riviera Cancun', 'Puerto Morelos', 1, '2026-04-09 18:57:55'),
(88, 3, 'HOTEL', 'Now Jade Riviera Cancun', 'Puerto Morelos', 1, '2026-04-09 18:57:55'),
(89, 5, 'HOTEL', 'Zamas Hotel', 'Tulum', 1, '2026-04-09 18:57:55'),
(90, 5, 'HOTEL', 'The Beach Tulum', 'Tulum', 1, '2026-04-09 18:57:55'),
(91, 5, 'HOTEL', 'Sanara Tulum', 'Tulum', 1, '2026-04-09 18:57:55'),
(92, 5, 'HOTEL', 'Papaya Playa Project', 'Tulum', 1, '2026-04-09 18:57:55'),
(93, 5, 'HOTEL', 'Nomade Tulum', 'Tulum', 1, '2026-04-09 18:57:55'),
(94, 5, 'HOTEL', 'Casa Malca', 'Tulum', 1, '2026-04-09 18:57:55'),
(95, 5, 'HOTEL', 'Ana y Jose Charming Hotel & Spa', 'Tulum', 1, '2026-04-09 18:57:55'),
(96, 5, 'HOTEL', 'Ahau Tulum', 'Tulum', 1, '2026-04-09 18:57:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `providers`
--

CREATE TABLE `providers` (
  `id` bigint(20) NOT NULL,
  `name` varchar(190) NOT NULL,
  `contact_name` varchar(190) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `phone` varchar(60) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `providers`
--

INSERT INTO `providers` (`id`, `name`, `contact_name`, `email`, `phone`, `is_active`, `created_at`) VALUES
(1, 'ltransfer', 'pedrito', 'chrisgc190123@gmail.com', '9982446717', 1, '2026-05-07 00:37:39'),
(2, 'vtransfer', 'juanito', 'chrisgc190123@gmail.com', '9984804079', 1, '2026-05-08 00:53:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `provider_transactions`
--

CREATE TABLE `provider_transactions` (
  `id` bigint(20) NOT NULL,
  `provider_id` bigint(20) NOT NULL,
  `booking_id` bigint(20) DEFAULT NULL,
  `type` enum('PAYABLE','RECEIVABLE','PAYMENT','CHARGE','ADJUSTMENT') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency_code` char(3) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rate_rules`
--

CREATE TABLE `rate_rules` (
  `id` bigint(20) NOT NULL,
  `zone_id` bigint(20) NOT NULL,
  `service_type_id` bigint(20) NOT NULL,
  `pax_range_id` bigint(20) NOT NULL,
  `vehicle_id` bigint(20) DEFAULT NULL,
  `currency_code` char(3) NOT NULL,
  `one_way_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `round_trip_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rate_rules`
--

INSERT INTO `rate_rules` (`id`, `zone_id`, `service_type_id`, `pax_range_id`, `vehicle_id`, `currency_code`, `one_way_price`, `round_trip_price`, `is_active`, `created_at`) VALUES
(1, 2, 1, 1, NULL, 'USD', 55.00, 101.75, 1, '2026-04-09 18:57:55'),
(2, 2, 2, 1, NULL, 'USD', 74.25, 137.36, 1, '2026-04-09 18:57:55'),
(3, 1, 1, 1, NULL, 'USD', 45.00, 83.25, 1, '2026-04-09 18:57:55'),
(4, 1, 2, 1, NULL, 'USD', 60.75, 112.39, 1, '2026-04-09 18:57:55'),
(5, 4, 1, 1, NULL, 'USD', 95.00, 175.75, 1, '2026-04-09 18:57:55'),
(6, 4, 2, 1, NULL, 'USD', 128.25, 237.26, 1, '2026-04-09 18:57:55'),
(7, 3, 1, 1, NULL, 'USD', 75.00, 138.75, 1, '2026-04-09 18:57:55'),
(8, 3, 2, 1, NULL, 'USD', 101.25, 187.31, 1, '2026-04-09 18:57:55'),
(9, 5, 1, 1, NULL, 'USD', 150.00, 277.50, 1, '2026-04-09 18:57:55'),
(10, 5, 2, 1, NULL, 'USD', 202.50, 374.63, 1, '2026-04-09 18:57:55'),
(11, 2, 1, 2, NULL, 'USD', 70.00, 129.50, 1, '2026-04-09 18:57:55'),
(12, 2, 2, 2, NULL, 'USD', 94.50, 174.83, 1, '2026-04-09 18:57:55'),
(13, 1, 1, 2, NULL, 'USD', 60.00, 111.00, 1, '2026-04-09 18:57:55'),
(14, 1, 2, 2, NULL, 'USD', 81.00, 149.85, 1, '2026-04-09 18:57:55'),
(15, 4, 1, 2, NULL, 'USD', 110.00, 203.50, 1, '2026-04-09 18:57:55'),
(16, 4, 2, 2, NULL, 'USD', 148.50, 274.73, 1, '2026-04-09 18:57:55'),
(17, 3, 1, 2, NULL, 'USD', 90.00, 166.50, 1, '2026-04-09 18:57:55'),
(18, 3, 2, 2, NULL, 'USD', 121.50, 224.78, 1, '2026-04-09 18:57:55'),
(19, 5, 1, 2, NULL, 'USD', 165.00, 305.25, 1, '2026-04-09 18:57:55'),
(20, 5, 2, 2, NULL, 'USD', 222.75, 412.09, 1, '2026-04-09 18:57:55'),
(21, 2, 1, 3, NULL, 'USD', 85.00, 157.25, 1, '2026-04-09 18:57:55'),
(22, 2, 2, 3, NULL, 'USD', 114.75, 212.29, 1, '2026-04-09 18:57:55'),
(23, 1, 1, 3, NULL, 'USD', 75.00, 138.75, 1, '2026-04-09 18:57:55'),
(24, 1, 2, 3, NULL, 'USD', 101.25, 187.31, 1, '2026-04-09 18:57:55'),
(25, 4, 1, 3, NULL, 'USD', 125.00, 231.25, 1, '2026-04-09 18:57:55'),
(26, 4, 2, 3, NULL, 'USD', 168.75, 312.19, 1, '2026-04-09 18:57:55'),
(27, 3, 1, 3, NULL, 'USD', 105.00, 194.25, 1, '2026-04-09 18:57:55'),
(28, 3, 2, 3, NULL, 'USD', 141.75, 262.24, 1, '2026-04-09 18:57:55'),
(29, 5, 1, 3, NULL, 'USD', 180.00, 333.00, 1, '2026-04-09 18:57:55'),
(30, 5, 2, 3, NULL, 'USD', 243.00, 449.55, 1, '2026-04-09 18:57:55'),
(31, 2, 1, 4, NULL, 'USD', 110.00, 203.50, 1, '2026-04-09 18:57:55'),
(32, 2, 2, 4, NULL, 'USD', 148.50, 274.73, 1, '2026-04-09 18:57:55'),
(33, 1, 1, 4, NULL, 'USD', 100.00, 185.00, 1, '2026-04-09 18:57:55'),
(34, 1, 2, 4, NULL, 'USD', 135.00, 249.75, 1, '2026-04-09 18:57:55'),
(35, 4, 1, 4, NULL, 'USD', 150.00, 277.50, 1, '2026-04-09 18:57:55'),
(36, 4, 2, 4, NULL, 'USD', 202.50, 374.63, 1, '2026-04-09 18:57:55'),
(37, 3, 1, 4, NULL, 'USD', 130.00, 240.50, 1, '2026-04-09 18:57:55'),
(38, 3, 2, 4, NULL, 'USD', 175.50, 324.68, 1, '2026-04-09 18:57:55'),
(39, 5, 1, 4, NULL, 'USD', 205.00, 379.25, 1, '2026-04-09 18:57:55'),
(40, 5, 2, 4, NULL, 'USD', 276.75, 511.99, 1, '2026-04-09 18:57:55'),
(41, 2, 1, 5, NULL, 'USD', 145.00, 268.25, 1, '2026-04-09 18:57:55'),
(42, 2, 2, 5, NULL, 'USD', 195.75, 362.14, 1, '2026-04-09 18:57:55'),
(43, 1, 1, 5, NULL, 'USD', 135.00, 249.75, 1, '2026-04-09 18:57:55'),
(44, 1, 2, 5, NULL, 'USD', 182.25, 337.16, 1, '2026-04-09 18:57:55'),
(45, 4, 1, 5, NULL, 'USD', 185.00, 342.25, 1, '2026-04-09 18:57:55'),
(46, 4, 2, 5, NULL, 'USD', 249.75, 462.04, 1, '2026-04-09 18:57:55'),
(47, 3, 1, 5, NULL, 'USD', 165.00, 305.25, 1, '2026-04-09 18:57:55'),
(48, 3, 2, 5, NULL, 'USD', 222.75, 412.09, 1, '2026-04-09 18:57:55'),
(49, 5, 1, 5, NULL, 'USD', 240.00, 444.00, 1, '2026-04-09 18:57:55'),
(50, 5, 2, 5, NULL, 'USD', 324.00, 599.40, 1, '2026-04-09 18:57:55'),
(64, 2, 1, 1, NULL, 'MXN', 935.00, 1729.75, 1, '2026-04-09 18:57:55'),
(65, 2, 2, 1, NULL, 'MXN', 1262.25, 2335.12, 1, '2026-04-09 18:57:55'),
(66, 1, 1, 1, NULL, 'MXN', 765.00, 1415.25, 1, '2026-04-09 18:57:55'),
(67, 1, 2, 1, NULL, 'MXN', 1032.75, 1910.63, 1, '2026-04-09 18:57:55'),
(68, 4, 1, 1, NULL, 'MXN', 1615.00, 2987.75, 1, '2026-04-09 18:57:55'),
(69, 4, 2, 1, NULL, 'MXN', 2180.25, 4033.42, 1, '2026-04-09 18:57:55'),
(70, 3, 1, 1, NULL, 'MXN', 1275.00, 2358.75, 1, '2026-04-09 18:57:55'),
(71, 3, 2, 1, NULL, 'MXN', 1721.25, 3184.27, 1, '2026-04-09 18:57:55'),
(72, 5, 1, 1, NULL, 'MXN', 2550.00, 4717.50, 1, '2026-04-09 18:57:55'),
(73, 5, 2, 1, NULL, 'MXN', 3442.50, 6368.71, 1, '2026-04-09 18:57:55'),
(74, 2, 1, 2, NULL, 'MXN', 1190.00, 2201.50, 1, '2026-04-09 18:57:55'),
(75, 2, 2, 2, NULL, 'MXN', 1606.50, 2972.11, 1, '2026-04-09 18:57:55'),
(76, 1, 1, 2, NULL, 'MXN', 1020.00, 1887.00, 1, '2026-04-09 18:57:55'),
(77, 1, 2, 2, NULL, 'MXN', 1377.00, 2547.45, 1, '2026-04-09 18:57:55'),
(78, 4, 1, 2, NULL, 'MXN', 1870.00, 3459.50, 1, '2026-04-09 18:57:55'),
(79, 4, 2, 2, NULL, 'MXN', 2524.50, 4670.41, 1, '2026-04-09 18:57:55'),
(80, 3, 1, 2, NULL, 'MXN', 1530.00, 2830.50, 1, '2026-04-09 18:57:55'),
(81, 3, 2, 2, NULL, 'MXN', 2065.50, 3821.26, 1, '2026-04-09 18:57:55'),
(82, 5, 1, 2, NULL, 'MXN', 2805.00, 5189.25, 1, '2026-04-09 18:57:55'),
(83, 5, 2, 2, NULL, 'MXN', 3786.75, 7005.53, 1, '2026-04-09 18:57:55'),
(84, 2, 1, 3, NULL, 'MXN', 1445.00, 2673.25, 1, '2026-04-09 18:57:55'),
(85, 2, 2, 3, NULL, 'MXN', 1950.75, 3608.93, 1, '2026-04-09 18:57:55'),
(86, 1, 1, 3, NULL, 'MXN', 1275.00, 2358.75, 1, '2026-04-09 18:57:55'),
(87, 1, 2, 3, NULL, 'MXN', 1721.25, 3184.27, 1, '2026-04-09 18:57:55'),
(88, 4, 1, 3, NULL, 'MXN', 2125.00, 3931.25, 1, '2026-04-09 18:57:55'),
(89, 4, 2, 3, NULL, 'MXN', 2868.75, 5307.23, 1, '2026-04-09 18:57:55'),
(90, 3, 1, 3, NULL, 'MXN', 1785.00, 3302.25, 1, '2026-04-09 18:57:55'),
(91, 3, 2, 3, NULL, 'MXN', 2409.75, 4458.08, 1, '2026-04-09 18:57:55'),
(92, 5, 1, 3, NULL, 'MXN', 3060.00, 5661.00, 1, '2026-04-09 18:57:55'),
(93, 5, 2, 3, NULL, 'MXN', 4131.00, 7642.35, 1, '2026-04-09 18:57:55'),
(94, 2, 1, 4, NULL, 'MXN', 1870.00, 3459.50, 1, '2026-04-09 18:57:55'),
(95, 2, 2, 4, NULL, 'MXN', 2524.50, 4670.41, 1, '2026-04-09 18:57:55'),
(96, 1, 1, 4, NULL, 'MXN', 1700.00, 3145.00, 1, '2026-04-09 18:57:55'),
(97, 1, 2, 4, NULL, 'MXN', 2295.00, 4245.75, 1, '2026-04-09 18:57:55'),
(98, 4, 1, 4, NULL, 'MXN', 2550.00, 4717.50, 1, '2026-04-09 18:57:55'),
(99, 4, 2, 4, NULL, 'MXN', 3442.50, 6368.71, 1, '2026-04-09 18:57:55'),
(100, 3, 1, 4, NULL, 'MXN', 2210.00, 4088.50, 1, '2026-04-09 18:57:55'),
(101, 3, 2, 4, NULL, 'MXN', 2983.50, 5519.56, 1, '2026-04-09 18:57:55'),
(102, 5, 1, 4, NULL, 'MXN', 3485.00, 6447.25, 1, '2026-04-09 18:57:55'),
(103, 5, 2, 4, NULL, 'MXN', 4704.75, 8703.83, 1, '2026-04-09 18:57:55'),
(104, 2, 1, 5, NULL, 'MXN', 2465.00, 4560.25, 1, '2026-04-09 18:57:55'),
(105, 2, 2, 5, NULL, 'MXN', 3327.75, 6156.38, 1, '2026-04-09 18:57:55'),
(106, 1, 1, 5, NULL, 'MXN', 2295.00, 4245.75, 1, '2026-04-09 18:57:55'),
(107, 1, 2, 5, NULL, 'MXN', 3098.25, 5731.72, 1, '2026-04-09 18:57:55'),
(108, 4, 1, 5, NULL, 'MXN', 3145.00, 5818.25, 1, '2026-04-09 18:57:55'),
(109, 4, 2, 5, NULL, 'MXN', 4245.75, 7854.68, 1, '2026-04-09 18:57:55'),
(110, 3, 1, 5, NULL, 'MXN', 2805.00, 5189.25, 1, '2026-04-09 18:57:55'),
(111, 3, 2, 5, NULL, 'MXN', 3786.75, 7005.53, 1, '2026-04-09 18:57:55'),
(112, 5, 1, 5, NULL, 'MXN', 4080.00, 7548.00, 1, '2026-04-09 18:57:55'),
(113, 5, 2, 5, NULL, 'MXN', 5508.00, 10189.80, 1, '2026-04-09 18:57:55'),
(127, 8, 1, 1, NULL, 'USD', 120.00, 222.00, 1, '2026-04-09 18:57:55'),
(128, 8, 2, 1, NULL, 'USD', 162.00, 299.70, 1, '2026-04-09 18:57:55'),
(129, 9, 1, 1, NULL, 'USD', 100.00, 185.00, 1, '2026-04-09 18:57:55'),
(130, 9, 2, 1, NULL, 'USD', 135.00, 249.75, 1, '2026-04-09 18:57:55'),
(131, 6, 1, 1, NULL, 'USD', 85.00, 157.25, 1, '2026-04-09 18:57:55'),
(132, 6, 2, 1, NULL, 'USD', 114.75, 212.29, 1, '2026-04-09 18:57:55'),
(133, 7, 1, 1, NULL, 'USD', 110.00, 203.50, 1, '2026-04-09 18:57:55'),
(134, 7, 2, 1, NULL, 'USD', 148.50, 274.73, 1, '2026-04-09 18:57:55'),
(135, 8, 1, 2, NULL, 'USD', 135.00, 249.75, 1, '2026-04-09 18:57:55'),
(136, 8, 2, 2, NULL, 'USD', 182.25, 337.16, 1, '2026-04-09 18:57:55'),
(137, 9, 1, 2, NULL, 'USD', 115.00, 212.75, 1, '2026-04-09 18:57:55'),
(138, 9, 2, 2, NULL, 'USD', 155.25, 287.21, 1, '2026-04-09 18:57:55'),
(139, 6, 1, 2, NULL, 'USD', 100.00, 185.00, 1, '2026-04-09 18:57:55'),
(140, 6, 2, 2, NULL, 'USD', 135.00, 249.75, 1, '2026-04-09 18:57:55'),
(141, 7, 1, 2, NULL, 'USD', 125.00, 231.25, 1, '2026-04-09 18:57:55'),
(142, 7, 2, 2, NULL, 'USD', 168.75, 312.19, 1, '2026-04-09 18:57:55'),
(143, 8, 1, 3, NULL, 'USD', 150.00, 277.50, 1, '2026-04-09 18:57:55'),
(144, 8, 2, 3, NULL, 'USD', 202.50, 374.63, 1, '2026-04-09 18:57:55'),
(145, 9, 1, 3, NULL, 'USD', 130.00, 240.50, 1, '2026-04-09 18:57:55'),
(146, 9, 2, 3, NULL, 'USD', 175.50, 324.68, 1, '2026-04-09 18:57:55'),
(147, 6, 1, 3, NULL, 'USD', 115.00, 212.75, 1, '2026-04-09 18:57:55'),
(148, 6, 2, 3, NULL, 'USD', 155.25, 287.21, 1, '2026-04-09 18:57:55'),
(149, 7, 1, 3, NULL, 'USD', 140.00, 259.00, 1, '2026-04-09 18:57:55'),
(150, 7, 2, 3, NULL, 'USD', 189.00, 349.65, 1, '2026-04-09 18:57:55'),
(151, 8, 1, 4, NULL, 'USD', 175.00, 323.75, 1, '2026-04-09 18:57:55'),
(152, 8, 2, 4, NULL, 'USD', 236.25, 437.06, 1, '2026-04-09 18:57:55'),
(153, 9, 1, 4, NULL, 'USD', 155.00, 286.75, 1, '2026-04-09 18:57:55'),
(154, 9, 2, 4, NULL, 'USD', 209.25, 387.11, 1, '2026-04-09 18:57:55'),
(155, 6, 1, 4, NULL, 'USD', 140.00, 259.00, 1, '2026-04-09 18:57:55'),
(156, 6, 2, 4, NULL, 'USD', 189.00, 349.65, 1, '2026-04-09 18:57:55'),
(157, 7, 1, 4, NULL, 'USD', 165.00, 305.25, 1, '2026-04-09 18:57:55'),
(158, 7, 2, 4, NULL, 'USD', 222.75, 412.09, 1, '2026-04-09 18:57:55'),
(159, 8, 1, 5, NULL, 'USD', 210.00, 388.50, 1, '2026-04-09 18:57:55'),
(160, 8, 2, 5, NULL, 'USD', 283.50, 524.48, 1, '2026-04-09 18:57:55'),
(161, 9, 1, 5, NULL, 'USD', 190.00, 351.50, 1, '2026-04-09 18:57:55'),
(162, 9, 2, 5, NULL, 'USD', 256.50, 474.53, 1, '2026-04-09 18:57:55'),
(163, 6, 1, 5, NULL, 'USD', 175.00, 323.75, 1, '2026-04-09 18:57:55'),
(164, 6, 2, 5, NULL, 'USD', 236.25, 437.06, 1, '2026-04-09 18:57:55'),
(165, 7, 1, 5, NULL, 'USD', 200.00, 370.00, 1, '2026-04-09 18:57:55'),
(166, 7, 2, 5, NULL, 'USD', 270.00, 499.50, 1, '2026-04-09 18:57:55'),
(190, 8, 1, 1, NULL, 'MXN', 2040.00, 3774.00, 1, '2026-04-09 18:57:55'),
(191, 8, 2, 1, NULL, 'MXN', 2754.00, 5094.90, 1, '2026-04-09 18:57:55'),
(192, 8, 1, 2, NULL, 'MXN', 2295.00, 4245.75, 1, '2026-04-09 18:57:55'),
(193, 8, 2, 2, NULL, 'MXN', 3098.25, 5731.72, 1, '2026-04-09 18:57:55'),
(194, 8, 1, 3, NULL, 'MXN', 2550.00, 4717.50, 1, '2026-04-09 18:57:55'),
(195, 8, 2, 3, NULL, 'MXN', 3442.50, 6368.71, 1, '2026-04-09 18:57:55'),
(196, 8, 1, 4, NULL, 'MXN', 2975.00, 5503.75, 1, '2026-04-09 18:57:55'),
(197, 8, 2, 4, NULL, 'MXN', 4016.25, 7430.02, 1, '2026-04-09 18:57:55'),
(198, 8, 1, 5, NULL, 'MXN', 3570.00, 6604.50, 1, '2026-04-09 18:57:55'),
(199, 8, 2, 5, NULL, 'MXN', 4819.50, 8916.16, 1, '2026-04-09 18:57:55'),
(200, 9, 1, 1, NULL, 'MXN', 1700.00, 3145.00, 1, '2026-04-09 18:57:55'),
(201, 9, 2, 1, NULL, 'MXN', 2295.00, 4245.75, 1, '2026-04-09 18:57:55'),
(202, 9, 1, 2, NULL, 'MXN', 1955.00, 3616.75, 1, '2026-04-09 18:57:55'),
(203, 9, 2, 2, NULL, 'MXN', 2639.25, 4882.57, 1, '2026-04-09 18:57:55'),
(204, 9, 1, 3, NULL, 'MXN', 2210.00, 4088.50, 1, '2026-04-09 18:57:55'),
(205, 9, 2, 3, NULL, 'MXN', 2983.50, 5519.56, 1, '2026-04-09 18:57:55'),
(206, 9, 1, 4, NULL, 'MXN', 2635.00, 4874.75, 1, '2026-04-09 18:57:55'),
(207, 9, 2, 4, NULL, 'MXN', 3557.25, 6580.87, 1, '2026-04-09 18:57:55'),
(208, 9, 1, 5, NULL, 'MXN', 3230.00, 5975.50, 1, '2026-04-09 18:57:55'),
(209, 9, 2, 5, NULL, 'MXN', 4360.50, 8067.01, 1, '2026-04-09 18:57:55'),
(210, 6, 1, 1, NULL, 'MXN', 1445.00, 2673.25, 1, '2026-04-09 18:57:55'),
(211, 6, 2, 1, NULL, 'MXN', 1950.75, 3608.93, 1, '2026-04-09 18:57:55'),
(212, 6, 1, 2, NULL, 'MXN', 1700.00, 3145.00, 1, '2026-04-09 18:57:55'),
(213, 6, 2, 2, NULL, 'MXN', 2295.00, 4245.75, 1, '2026-04-09 18:57:55'),
(214, 6, 1, 3, NULL, 'MXN', 1955.00, 3616.75, 1, '2026-04-09 18:57:55'),
(215, 6, 2, 3, NULL, 'MXN', 2639.25, 4882.57, 1, '2026-04-09 18:57:55'),
(216, 6, 1, 4, NULL, 'MXN', 2380.00, 4403.00, 1, '2026-04-09 18:57:55'),
(217, 6, 2, 4, NULL, 'MXN', 3213.00, 5944.05, 1, '2026-04-09 18:57:55'),
(218, 6, 1, 5, NULL, 'MXN', 2975.00, 5503.75, 1, '2026-04-09 18:57:55'),
(219, 6, 2, 5, NULL, 'MXN', 4016.25, 7430.02, 1, '2026-04-09 18:57:55'),
(220, 7, 1, 1, NULL, 'MXN', 1870.00, 3459.50, 1, '2026-04-09 18:57:55'),
(221, 7, 2, 1, NULL, 'MXN', 2524.50, 4670.41, 1, '2026-04-09 18:57:55'),
(222, 7, 1, 2, NULL, 'MXN', 2125.00, 3931.25, 1, '2026-04-09 18:57:55'),
(223, 7, 2, 2, NULL, 'MXN', 2868.75, 5307.23, 1, '2026-04-09 18:57:55'),
(224, 7, 1, 3, NULL, 'MXN', 2380.00, 4403.00, 1, '2026-04-09 18:57:55'),
(225, 7, 2, 3, NULL, 'MXN', 3213.00, 5944.05, 1, '2026-04-09 18:57:55'),
(226, 7, 1, 4, NULL, 'MXN', 2805.00, 5189.25, 1, '2026-04-09 18:57:55'),
(227, 7, 2, 4, NULL, 'MXN', 3786.75, 7005.53, 1, '2026-04-09 18:57:55'),
(228, 7, 1, 5, NULL, 'MXN', 3400.00, 6290.00, 1, '2026-04-09 18:57:55'),
(229, 7, 2, 5, NULL, 'MXN', 4590.00, 8491.50, 1, '2026-04-09 18:57:55'),
(253, 2, 3, 1, NULL, 'USD', 92.81, 171.70, 1, '2026-04-09 18:57:55'),
(254, 1, 3, 1, NULL, 'USD', 75.94, 140.49, 1, '2026-04-09 18:57:55'),
(255, 4, 3, 1, NULL, 'USD', 160.31, 296.58, 1, '2026-04-09 18:57:55'),
(256, 3, 3, 1, NULL, 'USD', 126.56, 234.14, 1, '2026-04-09 18:57:55'),
(257, 5, 3, 1, NULL, 'USD', 253.13, 468.29, 1, '2026-04-09 18:57:55'),
(258, 2, 3, 2, NULL, 'USD', 118.13, 218.54, 1, '2026-04-09 18:57:55'),
(259, 1, 3, 2, NULL, 'USD', 101.25, 187.31, 1, '2026-04-09 18:57:55'),
(260, 4, 3, 2, NULL, 'USD', 185.63, 343.41, 1, '2026-04-09 18:57:55'),
(261, 3, 3, 2, NULL, 'USD', 151.88, 280.98, 1, '2026-04-09 18:57:55'),
(262, 5, 3, 2, NULL, 'USD', 278.44, 515.11, 1, '2026-04-09 18:57:55'),
(263, 2, 3, 3, NULL, 'USD', 143.44, 265.36, 1, '2026-04-09 18:57:55'),
(264, 1, 3, 3, NULL, 'USD', 126.56, 234.14, 1, '2026-04-09 18:57:55'),
(265, 4, 3, 3, NULL, 'USD', 210.94, 390.24, 1, '2026-04-09 18:57:55'),
(266, 3, 3, 3, NULL, 'USD', 177.19, 327.80, 1, '2026-04-09 18:57:55'),
(267, 5, 3, 3, NULL, 'USD', 303.75, 561.94, 1, '2026-04-09 18:57:55'),
(268, 2, 3, 4, NULL, 'USD', 185.63, 343.41, 1, '2026-04-09 18:57:55'),
(269, 1, 3, 4, NULL, 'USD', 168.75, 312.19, 1, '2026-04-09 18:57:55'),
(270, 4, 3, 4, NULL, 'USD', 253.13, 468.29, 1, '2026-04-09 18:57:55'),
(271, 3, 3, 4, NULL, 'USD', 219.38, 405.85, 1, '2026-04-09 18:57:55'),
(272, 5, 3, 4, NULL, 'USD', 345.94, 639.99, 1, '2026-04-09 18:57:55'),
(273, 2, 3, 5, NULL, 'USD', 244.69, 452.68, 1, '2026-04-09 18:57:55'),
(274, 1, 3, 5, NULL, 'USD', 227.81, 421.45, 1, '2026-04-09 18:57:55'),
(275, 4, 3, 5, NULL, 'USD', 312.19, 577.55, 1, '2026-04-09 18:57:55'),
(276, 3, 3, 5, NULL, 'USD', 278.44, 515.11, 1, '2026-04-09 18:57:55'),
(277, 5, 3, 5, NULL, 'USD', 405.00, 749.25, 1, '2026-04-09 18:57:55'),
(278, 2, 3, 1, NULL, 'MXN', 1577.81, 2918.90, 1, '2026-04-09 18:57:55'),
(279, 1, 3, 1, NULL, 'MXN', 1290.94, 2388.29, 1, '2026-04-09 18:57:55'),
(280, 4, 3, 1, NULL, 'MXN', 2725.31, 5041.78, 1, '2026-04-09 18:57:55'),
(281, 3, 3, 1, NULL, 'MXN', 2151.56, 3980.34, 1, '2026-04-09 18:57:55'),
(282, 5, 3, 1, NULL, 'MXN', 4303.13, 7960.89, 1, '2026-04-09 18:57:55'),
(283, 2, 3, 2, NULL, 'MXN', 2008.13, 3715.14, 1, '2026-04-09 18:57:55'),
(284, 1, 3, 2, NULL, 'MXN', 1721.25, 3184.31, 1, '2026-04-09 18:57:55'),
(285, 4, 3, 2, NULL, 'MXN', 3155.63, 5838.01, 1, '2026-04-09 18:57:55'),
(286, 3, 3, 2, NULL, 'MXN', 2581.88, 4776.58, 1, '2026-04-09 18:57:55'),
(287, 5, 3, 2, NULL, 'MXN', 4733.44, 8756.91, 1, '2026-04-09 18:57:55'),
(288, 2, 3, 3, NULL, 'MXN', 2438.44, 4511.16, 1, '2026-04-09 18:57:55'),
(289, 1, 3, 3, NULL, 'MXN', 2151.56, 3980.34, 1, '2026-04-09 18:57:55'),
(290, 4, 3, 3, NULL, 'MXN', 3585.94, 6634.04, 1, '2026-04-09 18:57:55'),
(291, 3, 3, 3, NULL, 'MXN', 3012.19, 5572.60, 1, '2026-04-09 18:57:55'),
(292, 5, 3, 3, NULL, 'MXN', 5163.75, 9552.94, 1, '2026-04-09 18:57:55'),
(293, 2, 3, 4, NULL, 'MXN', 3155.63, 5838.01, 1, '2026-04-09 18:57:55'),
(294, 1, 3, 4, NULL, 'MXN', 2868.75, 5307.19, 1, '2026-04-09 18:57:55'),
(295, 4, 3, 4, NULL, 'MXN', 4303.13, 7960.89, 1, '2026-04-09 18:57:55'),
(296, 3, 3, 4, NULL, 'MXN', 3729.38, 6899.45, 1, '2026-04-09 18:57:55'),
(297, 5, 3, 4, NULL, 'MXN', 5880.94, 10879.79, 1, '2026-04-09 18:57:55'),
(298, 2, 3, 5, NULL, 'MXN', 4159.69, 7695.48, 1, '2026-04-09 18:57:55'),
(299, 1, 3, 5, NULL, 'MXN', 3872.81, 7164.65, 1, '2026-04-09 18:57:55'),
(300, 4, 3, 5, NULL, 'MXN', 5307.19, 9818.35, 1, '2026-04-09 18:57:55'),
(301, 3, 3, 5, NULL, 'MXN', 4733.44, 8756.91, 1, '2026-04-09 18:57:55'),
(302, 5, 3, 5, NULL, 'MXN', 6885.00, 12737.25, 1, '2026-04-09 18:57:55'),
(303, 8, 3, 1, NULL, 'USD', 202.50, 374.63, 1, '2026-04-09 18:57:55'),
(304, 9, 3, 1, NULL, 'USD', 168.75, 312.19, 1, '2026-04-09 18:57:55'),
(305, 6, 3, 1, NULL, 'USD', 143.44, 265.36, 1, '2026-04-09 18:57:55'),
(306, 7, 3, 1, NULL, 'USD', 185.63, 343.41, 1, '2026-04-09 18:57:55'),
(307, 8, 3, 2, NULL, 'USD', 227.81, 421.45, 1, '2026-04-09 18:57:55'),
(308, 9, 3, 2, NULL, 'USD', 194.06, 359.01, 1, '2026-04-09 18:57:55'),
(309, 6, 3, 2, NULL, 'USD', 168.75, 312.19, 1, '2026-04-09 18:57:55'),
(310, 7, 3, 2, NULL, 'USD', 210.94, 390.24, 1, '2026-04-09 18:57:55'),
(311, 8, 3, 3, NULL, 'USD', 253.13, 468.29, 1, '2026-04-09 18:57:55'),
(312, 9, 3, 3, NULL, 'USD', 219.38, 405.85, 1, '2026-04-09 18:57:55'),
(313, 6, 3, 3, NULL, 'USD', 194.06, 359.01, 1, '2026-04-09 18:57:55'),
(314, 7, 3, 3, NULL, 'USD', 236.25, 437.06, 1, '2026-04-09 18:57:55'),
(315, 8, 3, 4, NULL, 'USD', 295.31, 546.33, 1, '2026-04-09 18:57:55'),
(316, 9, 3, 4, NULL, 'USD', 261.56, 483.89, 1, '2026-04-09 18:57:55'),
(317, 6, 3, 4, NULL, 'USD', 236.25, 437.06, 1, '2026-04-09 18:57:55'),
(318, 7, 3, 4, NULL, 'USD', 278.44, 515.11, 1, '2026-04-09 18:57:55'),
(319, 8, 3, 5, NULL, 'USD', 354.38, 655.60, 1, '2026-04-09 18:57:55'),
(320, 9, 3, 5, NULL, 'USD', 320.63, 593.16, 1, '2026-04-09 18:57:55'),
(321, 6, 3, 5, NULL, 'USD', 295.31, 546.33, 1, '2026-04-09 18:57:55'),
(322, 7, 3, 5, NULL, 'USD', 337.50, 624.38, 1, '2026-04-09 18:57:55'),
(323, 8, 3, 1, NULL, 'MXN', 3442.50, 6368.63, 1, '2026-04-09 18:57:55'),
(324, 8, 3, 2, NULL, 'MXN', 3872.81, 7164.65, 1, '2026-04-09 18:57:55'),
(325, 8, 3, 3, NULL, 'MXN', 4303.13, 7960.89, 1, '2026-04-09 18:57:55'),
(326, 8, 3, 4, NULL, 'MXN', 5020.31, 9287.53, 1, '2026-04-09 18:57:55'),
(327, 8, 3, 5, NULL, 'MXN', 6024.38, 11145.20, 1, '2026-04-09 18:57:55'),
(328, 9, 3, 1, NULL, 'MXN', 2868.75, 5307.19, 1, '2026-04-09 18:57:55'),
(329, 9, 3, 2, NULL, 'MXN', 3299.06, 6103.21, 1, '2026-04-09 18:57:55'),
(330, 9, 3, 3, NULL, 'MXN', 3729.38, 6899.45, 1, '2026-04-09 18:57:55'),
(331, 9, 3, 4, NULL, 'MXN', 4446.56, 8226.09, 1, '2026-04-09 18:57:55'),
(332, 9, 3, 5, NULL, 'MXN', 5450.63, 10083.76, 1, '2026-04-09 18:57:55'),
(333, 6, 3, 1, NULL, 'MXN', 2438.44, 4511.16, 1, '2026-04-09 18:57:55'),
(334, 6, 3, 2, NULL, 'MXN', 2868.75, 5307.19, 1, '2026-04-09 18:57:55'),
(335, 6, 3, 3, NULL, 'MXN', 3299.06, 6103.21, 1, '2026-04-09 18:57:55'),
(336, 6, 3, 4, NULL, 'MXN', 4016.25, 7430.06, 1, '2026-04-09 18:57:55'),
(337, 6, 3, 5, NULL, 'MXN', 5020.31, 9287.53, 1, '2026-04-09 18:57:55'),
(338, 7, 3, 1, NULL, 'MXN', 3155.63, 5838.01, 1, '2026-04-09 18:57:55'),
(339, 7, 3, 2, NULL, 'MXN', 3585.94, 6634.04, 1, '2026-04-09 18:57:55'),
(340, 7, 3, 3, NULL, 'MXN', 4016.25, 7430.06, 1, '2026-04-09 18:57:55'),
(341, 7, 3, 4, NULL, 'MXN', 4733.44, 8756.91, 1, '2026-04-09 18:57:55'),
(342, 7, 3, 5, NULL, 'MXN', 5737.50, 10614.38, 1, '2026-04-09 18:57:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) NOT NULL,
  `code` varchar(60) NOT NULL,
  `name` varchar(120) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `code`, `name`, `created_at`) VALUES
(1, 'admin', 'Administrador', '2026-04-09 18:57:55'),
(2, 'operator', 'Operador / chofer', '2026-04-09 18:57:55'),
(3, 'sales', 'Ventas / reservaciones', '2026-04-09 18:57:55'),
(4, 'accounting', 'Contabilidad', '2026-04-09 18:57:55'),
(6, 'agency', 'Agencia / agente externo', '2026-05-07 00:33:52'),
(12, 'superadmin', 'Super Administrator', '2026-05-08 02:06:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` bigint(20) NOT NULL,
  `permission_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(6, 1),
(1, 2),
(2, 2),
(3, 2),
(6, 2),
(1, 3),
(3, 3),
(1, 4),
(1, 5),
(1, 6),
(2, 6),
(1, 7),
(4, 7),
(1, 8),
(4, 8),
(1, 9),
(1, 11),
(3, 11),
(6, 11),
(12, 23);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `service_types`
--

CREATE TABLE `service_types` (
  `id` bigint(20) NOT NULL,
  `code` varchar(40) NOT NULL,
  `name_es` varchar(120) NOT NULL,
  `name_en` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `service_types`
--

INSERT INTO `service_types` (`id`, `code`, `name_es`, `name_en`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'REGULAR', 'Regular', 'Regular', 1, 10, '2026-04-09 18:57:55'),
(2, 'VIP', 'VIP', 'VIP', 0, 20, '2026-04-09 18:57:55'),
(3, 'LUXURY', 'Luxury', 'Luxury', 0, 30, '2026-04-09 18:57:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `site_content`
--

CREATE TABLE `site_content` (
  `id` bigint(20) NOT NULL,
  `content_key` varchar(80) NOT NULL,
  `content_json` longtext NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `site_content`
--

INSERT INTO `site_content` (`id`, `content_key`, `content_json`, `updated_by`, `updated_at`) VALUES
(1, 'home_page', '{\"sections\":{\"show_social_links\":true,\"show_hero_badges\":true,\"show_booking_panel\":true,\"show_highlights\":true,\"show_routes\":true,\"show_story\":true,\"show_story_points\":true,\"show_support\":true,\"show_closing_cta\":true,\"show_floating_contact\":true},\"brand_logo\":\"https://new.expresstransfercancun.com/assets/expresslogo-300x122.png.webp\",\"brand_logo_light\":\"/uploads/home/light-logo-20260507235356-23a6f46c.png\",\"home_theme\":\"day\",\"eyebrow\":\"Private Cancun airport transfers for resorts, villas, executive arrivals and high-touch travel teams\",\"hero_mode\":\"slider\",\"hero_images\":[\"https://images.unsplash.com/photo-1621976498727-9e65a5f721a4?auto=format&fit=crop&w=2200&q=80\",\"https://images.unsplash.com/photo-1464219789935-c2d9d9aba644?auto=format&fit=crop&w=2200&q=80\",\"https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=2200&q=80\"],\"hero_title\":\"Private airport arrivals handled with chauffeur-level calm.\",\"hero_subtitle\":\"Book Cancun and Riviera Maya transfers through a cleaner, more premium interface designed to feel closer to a concierge desk than a generic transport page.\",\"hero_primary_cta_label\":\"Reserve your transfer\",\"hero_primary_cta_href\":\"#booking-form\",\"hero_secondary_cta_label\":\"Speak with concierge\",\"hero_secondary_cta_href\":\"#contact-channels\",\"search_label\":\"Private transfer request\",\"search_panel_layout\":\"center-horizontal\",\"search_help\":\"Choose route, dates and passenger count to get the right private service tier without losing the speed expected from a direct airport transfer booking.\",\"search_button_label\":\"View private options\",\"badges\":[\"Private vehicles only\",\"Arrival monitoring included\",\"Rates visible before checkout\"],\"hero_slides\":[{\"title\":\"Arrival-first reassurance\",\"text\":\"Guests landing in Cancun need visible support, direct contact and a polished booking path that feels reliable before they even request a quote.\",\"label\":\"Open WhatsApp\",\"href\":\"https://wa.me/529981234567\"},{\"title\":\"Resort and villa coordination\",\"text\":\"Frame every route like a curated private movement for luxury resorts, residences and family arrivals instead of generic point-to-point transport.\",\"label\":\"Explore routes\",\"href\":\"#routes\"},{\"title\":\"Operational confidence\",\"text\":\"Blend direct contact, visible route curation and clear service tiers so the page feels premium without hiding the operational clarity travelers need.\",\"label\":\"See support\",\"href\":\"#contact-channels\"}],\"highlights\":[{\"title\":\"Arrival-led operations\",\"text\":\"Flight monitoring, airport coordination and pickup sequencing for guests, concierges and villa teams.\"},{\"title\":\"Tiered private service\",\"text\":\"Present regular, VIP and luxury transport tiers with clearer service language and less visual clutter.\"},{\"title\":\"Direct-booking confidence\",\"text\":\"A calmer premium presentation builds trust before the guest reduces the decision to price alone.\"}],\"collections\":[{\"title\":\"Cancun Hotel Zone\",\"text\":\"The main arrival corridor for premium resort guests who expect fast booking and polished airport coordination.\"},{\"title\":\"Costa Mujeres\",\"text\":\"Newer resort inventory where the visual identity should reinforce exclusivity and private-service positioning.\"},{\"title\":\"Playa del Carmen\",\"text\":\"A high-demand corridor where route clarity, vehicle tiering and operational trust matter immediately.\"},{\"title\":\"Tulum corridor\",\"text\":\"Longer private journeys that benefit from calmer layout, visible reassurance and stronger concierge tone.\"}],\"story_title\":\"A premium transfer homepage should sell confidence before it sells transportation.\",\"story_body\":\"The booking flow can stay direct and practical while the visual language shifts toward private aviation, concierge support and luxury resort arrivals. That balance is what separates a premium transfer brand from a commodity transport page.\",\"story_points\":[\"Hero language centered on arrivals, service and reassurance\",\"Reusable sections for routes, service tiers and visible contact\",\"Admin-editable text blocks so marketing updates do not require code\"],\"contact_channels\":[{\"type\":\"whatsapp\",\"title\":\"WhatsApp\",\"value\":\"+52 998 123 4567\",\"url\":\"https://wa.me/529981234567\"},{\"type\":\"call\",\"title\":\"Call us\",\"value\":\"+52 998 123 4567\",\"url\":\"tel:+529981234567\"},{\"type\":\"sms\",\"title\":\"SMS updates\",\"value\":\"+52 998 123 4567\",\"url\":\"sms:+529981234567\"},{\"type\":\"telegram\",\"title\":\"Telegram\",\"value\":\"@ktransfers\",\"url\":\"https://t.me/ktransfers\"}],\"social_links\":[{\"label\":\"Instagram\",\"url\":\"https://instagram.com/ktransfers\"},{\"label\":\"Facebook\",\"url\":\"https://facebook.com/ktransfers\"},{\"label\":\"Tripadvisor\",\"url\":\"https://tripadvisor.com/\"}],\"testimonial_quote\":\"Guests book faster when the page feels deliberate, calm and operationally credible. The design should carry the same confidence as the pickup itself.\",\"testimonial_author\":\"KTransfers Product Direction\",\"testimonial_role\":\"Brand and Operations\",\"cta_title\":\"Ready to request a private transfer with a more elevated first impression?\",\"cta_body\":\"The booking structure remains practical, but the experience now frames the service with more confidence, better hierarchy and a stronger chauffeur-style tone.\",\"cta_button_label\":\"Start your request\",\"cta_button_href\":\"#booking-form\"}', 1, '2026-05-07 23:53:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'sysadmin', 'info@expresstransfercancun.com', '$2y$10$hx/qoSFRn.PK91ZhxgEd/e.SErK2YhVpMAJVNywdNEc0Y1NmRknvu', 1, '2026-04-09 18:57:55', '2026-05-08 02:12:15'),
(2, 'Juan Manuel Del Toro', 'operador@expresstransfercancun.com', '$2y$10$W.7xFAHcIOkDcubFH.0m4Ojzzinmkujg1iTFgcby56YrEgcG0QvS6', 1, '2026-05-07 00:23:51', NULL),
(3, 'Chistopher Guitierrez', 'chrisgc190123@gmail.com', '$2y$10$rinssKmpJpX.X68rRnFLvOl2/aTkfxv/N3JhEncu54OJoyZcFV0zC', 1, '2026-05-07 00:24:32', '2026-05-07 00:34:48'),
(4, 'Administrador', 'info1@expresstransfercancun.com', '$2y$10$c4e9zGkvlboJ21WnCIvm/eiOpDxYfGAshDU.DrX1cMhrKIcHXkgj.', 1, '2026-05-08 02:09:39', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` bigint(20) NOT NULL,
  `role_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(4, 1),
(2, 2),
(3, 6),
(1, 12);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehicles`
--

CREATE TABLE `vehicles` (
  `id` bigint(20) NOT NULL,
  `code` varchar(60) NOT NULL,
  `name` varchar(120) NOT NULL,
  `max_pax` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vehicles`
--

INSERT INTO `vehicles` (`id`, `code`, `name`, `max_pax`, `is_active`, `created_at`) VALUES
(1, 'SEDAN_1_3', 'Sedan 1-3 pax', 3, 1, '2026-04-09 18:57:55'),
(2, 'SUV_1_5', 'SUV 1-5 pax', 5, 1, '2026-04-09 18:57:55'),
(3, 'VAN_1_8', 'Van 1-8 pax', 8, 1, '2026-04-09 18:57:55'),
(4, 'SPRINTER_1_16', 'Sprinter 1-16 pax', 16, 1, '2026-04-09 18:57:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `work_orders`
--

CREATE TABLE `work_orders` (
  `id` bigint(20) NOT NULL,
  `work_date` date NOT NULL,
  `booking_id` bigint(20) NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `work_orders`
--

INSERT INTO `work_orders` (`id`, `work_date`, `booking_id`, `notes`, `created_at`) VALUES
(1, '2026-04-11', 1, '4 aguas \r\n5 cervezas', '2026-04-10 20:51:43'),
(2, '2026-05-21', 2, NULL, '2026-05-07 00:38:39'),
(3, '2026-05-07', 3, NULL, '2026-05-07 20:31:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zones`
--

CREATE TABLE `zones` (
  `id` bigint(20) NOT NULL,
  `code` varchar(60) NOT NULL,
  `name_es` varchar(120) NOT NULL,
  `name_en` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `zones`
--

INSERT INTO `zones` (`id`, `code`, `name_es`, `name_en`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'CUN_HOTEL_ZONE', 'Zona Hotelera Cancún', 'Cancun Hotel Zone', 1, 10, '2026-04-09 18:57:55'),
(2, 'CUN_DOWNTOWN', 'Centro Cancún', 'Cancun Downtown', 1, 20, '2026-04-09 18:57:55'),
(3, 'PUERTO_MORELOS', 'Puerto Morelos', 'Puerto Morelos', 1, 30, '2026-04-09 18:57:55'),
(4, 'PLAYA_DEL_CARMEN', 'Playa del Carmen', 'Playa del Carmen', 1, 40, '2026-04-09 18:57:55'),
(5, 'TULUM', 'Tulum', 'Tulum', 1, 50, '2026-04-09 18:57:55'),
(6, 'PLAYA_MUJERES', 'Playa Mujeres', 'Playa Mujeres', 1, 60, '2026-04-09 18:57:55'),
(7, 'PUERTO_AVENTURAS', 'Puerto Aventuras', 'Puerto Aventuras', 1, 70, '2026-04-09 18:57:55'),
(8, 'AKUMAL', 'Akumal', 'Akumal', 1, 80, '2026-04-09 18:57:55'),
(9, 'MAROMA', 'Maroma', 'Maroma', 1, 90, '2026-04-09 18:57:55');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `airlines`
--
ALTER TABLE `airlines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indices de la tabla `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_id` (`booking_id`),
  ADD KEY `fk_asg_provider` (`provider_id`),
  ADD KEY `fk_asg_vehicle` (`vehicle_id`),
  ADD KEY `fk_asg_operator` (`operator_user_id`);

--
-- Indices de la tabla `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_code` (`booking_code`),
  ADD KEY `fk_booking_service` (`service_type_id`),
  ADD KEY `fk_booking_zone` (`zone_id`),
  ADD KEY `fk_booking_place` (`place_id`),
  ADD KEY `fk_booking_currency` (`currency_code`),
  ADD KEY `idx_bookings_created_by_user` (`created_by_user_id`);

--
-- Indices de la tabla `booking_passengers`
--
ALTER TABLE `booking_passengers`
  ADD PRIMARY KEY (`booking_id`);

--
-- Indices de la tabla `booking_payments`
--
ALTER TABLE `booking_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pay_booking` (`booking_id`),
  ADD KEY `fk_pay_currency` (`currency_code`);

--
-- Indices de la tabla `booking_status_history`
--
ALTER TABLE `booking_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bsh_booking` (`booking_id`),
  ADD KEY `fk_bsh_user` (`changed_by`);

--
-- Indices de la tabla `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_countries_iso2` (`iso2`),
  ADD UNIQUE KEY `uq_countries_iso3` (`iso3`);

--
-- Indices de la tabla `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`code`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `filename` (`filename`);

--
-- Indices de la tabla `pax_ranges`
--
ALTER TABLE `pax_ranges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pax_range` (`min_pax`,`max_pax`);

--
-- Indices de la tabla `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indices de la tabla `places`
--
ALTER TABLE `places`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_places_zone` (`zone_id`),
  ADD KEY `idx_places_name` (`name`);

--
-- Indices de la tabla `providers`
--
ALTER TABLE `providers`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `provider_transactions`
--
ALTER TABLE `provider_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pt_booking` (`booking_id`),
  ADD KEY `fk_pt_currency` (`currency_code`),
  ADD KEY `idx_pt_provider` (`provider_id`,`created_at`);

--
-- Indices de la tabla `rate_rules`
--
ALTER TABLE `rate_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rates_service` (`service_type_id`),
  ADD KEY `fk_rates_pax` (`pax_range_id`),
  ADD KEY `fk_rates_vehicle` (`vehicle_id`),
  ADD KEY `fk_rates_currency` (`currency_code`),
  ADD KEY `idx_rate_lookup` (`zone_id`,`service_type_id`,`pax_range_id`,`currency_code`,`is_active`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indices de la tabla `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `fk_role_perm_perm` (`permission_id`);

--
-- Indices de la tabla `service_types`
--
ALTER TABLE `service_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indices de la tabla `site_content`
--
ALTER TABLE `site_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `content_key` (`content_key`),
  ADD KEY `fk_site_content_updated_by` (`updated_by`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `fk_user_roles_role` (`role_id`);

--
-- Indices de la tabla `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indices de la tabla `work_orders`
--
ALTER TABLE `work_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_id` (`booking_id`),
  ADD KEY `idx_wo_date` (`work_date`);

--
-- Indices de la tabla `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `airlines`
--
ALTER TABLE `airlines`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT de la tabla `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `booking_payments`
--
ALTER TABLE `booking_payments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `booking_status_history`
--
ALTER TABLE `booking_status_history`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `countries`
--
ALTER TABLE `countries`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `pax_ranges`
--
ALTER TABLE `pax_ranges`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `places`
--
ALTER TABLE `places`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT de la tabla `providers`
--
ALTER TABLE `providers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `provider_transactions`
--
ALTER TABLE `provider_transactions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rate_rules`
--
ALTER TABLE `rate_rules`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=380;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `service_types`
--
ALTER TABLE `service_types`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `site_content`
--
ALTER TABLE `site_content`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `work_orders`
--
ALTER TABLE `work_orders`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `zones`
--
ALTER TABLE `zones`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `fk_asg_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_asg_operator` FOREIGN KEY (`operator_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_asg_provider` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`),
  ADD CONSTRAINT `fk_asg_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`);

--
-- Filtros para la tabla `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_booking_currency` FOREIGN KEY (`currency_code`) REFERENCES `currencies` (`code`),
  ADD CONSTRAINT `fk_booking_place` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`),
  ADD CONSTRAINT `fk_booking_service` FOREIGN KEY (`service_type_id`) REFERENCES `service_types` (`id`),
  ADD CONSTRAINT `fk_booking_zone` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`),
  ADD CONSTRAINT `fk_bookings_created_by_user` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `booking_passengers`
--
ALTER TABLE `booking_passengers`
  ADD CONSTRAINT `fk_bp_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `booking_payments`
--
ALTER TABLE `booking_payments`
  ADD CONSTRAINT `fk_pay_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pay_currency` FOREIGN KEY (`currency_code`) REFERENCES `currencies` (`code`);

--
-- Filtros para la tabla `booking_status_history`
--
ALTER TABLE `booking_status_history`
  ADD CONSTRAINT `fk_bsh_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bsh_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `places`
--
ALTER TABLE `places`
  ADD CONSTRAINT `fk_places_zone` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`);

--
-- Filtros para la tabla `provider_transactions`
--
ALTER TABLE `provider_transactions`
  ADD CONSTRAINT `fk_pt_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pt_currency` FOREIGN KEY (`currency_code`) REFERENCES `currencies` (`code`),
  ADD CONSTRAINT `fk_pt_provider` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`);

--
-- Filtros para la tabla `rate_rules`
--
ALTER TABLE `rate_rules`
  ADD CONSTRAINT `fk_rates_currency` FOREIGN KEY (`currency_code`) REFERENCES `currencies` (`code`),
  ADD CONSTRAINT `fk_rates_pax` FOREIGN KEY (`pax_range_id`) REFERENCES `pax_ranges` (`id`),
  ADD CONSTRAINT `fk_rates_service` FOREIGN KEY (`service_type_id`) REFERENCES `service_types` (`id`),
  ADD CONSTRAINT `fk_rates_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`),
  ADD CONSTRAINT `fk_rates_zone` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`);

--
-- Filtros para la tabla `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_perm_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_role_perm_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `site_content`
--
ALTER TABLE `site_content`
  ADD CONSTRAINT `fk_site_content_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `work_orders`
--
ALTER TABLE `work_orders`
  ADD CONSTRAINT `fk_wo_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
