-- phpMyAdmin SQL Dump
-- version 4.9.10
-- https://www.phpmyadmin.net/
--
-- Host: obuma2.cluster-cdxecnghta3h.us-east-1.rds.amazonaws.com
-- Generation Time: May 29, 2025 at 10:05 AM
-- Server version: 8.0.36
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `obuma_online`
--

-- --------------------------------------------------------

--
-- Table structure for table `producto_categoria`
--

CREATE TABLE `producto_categoria` (
  `producto_categoria_id` bigint NOT NULL,
  `producto_categoria_codigo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_categoria_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_categoria_descripcion` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_categoria_imagen` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_categoria_posicion` int NOT NULL,
  `producto_categoria_mostrar` int NOT NULL,
  `producto_categoria_metatitle` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_categoria_metadescription` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_categoria_metakeywords` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_categoria_urlseo` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_categoria_webutildata` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'se usa para almacenar valores de rapida busqueda en json',
  `producto_categoria_mercadolibre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rel_empresa_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `producto_categoria`
--

INSERT INTO `producto_categoria` (`producto_categoria_id`, `producto_categoria_codigo`, `producto_categoria_nombre`, `producto_categoria_descripcion`, `producto_categoria_imagen`, `producto_categoria_posicion`, `producto_categoria_mostrar`, `producto_categoria_metatitle`, `producto_categoria_metadescription`, `producto_categoria_metakeywords`, `producto_categoria_urlseo`, `producto_categoria_webutildata`, `producto_categoria_mercadolibre`, `rel_empresa_id`) VALUES
(1, '', 'Accesorios CCTV', '', '', 17, 1, 'Accesorios Camaras de Seguridad', 'Accesorios Camaras de seguridad', 'accesorios camaras de seguridad', 'accesorios-camaras-de-seguridad', '', '', 4),
(2, '', 'Alarmas Hogar  y Empresas', '', '', 8, 0, 'Alarmas Hogar  y Empresas', '', '', 'alarmas-hogar-y-empresas', '', '', 4),
(4, '', 'Control de Asistencia', '', '', 6, 1, 'Control de Asistencia', '', '', 'control-de-asistencia', '', '', 4),
(6, '', 'Materiales Instalacion CCTV', '', '', 3, 0, 'Materiales Instalacion CCTV', '', '', 'materiales-instalacion-cctv', '', '', 4),
(7, '', 'Servicios Instalacion', '', '', 4, 1, 'Servicios Instalacion', '', '', 'servicios-instalacion', '', '', 4),
(8, '', 'Alarmas', '', '', 5, 0, 'Alarmas', '', '', 'alarmas', '', '', 4),
(9, '', 'Computadores', '', '', 7, 0, 'Computadores', '', '', 'computadores', '', '', 4),
(11, '', 'EPP', '', '', 0, 0, '', '', '', '', '', '', 3),
(12, '', 'TALLER', '', '', 0, 0, '', '', '', '', '', '', 3),
(19, '', 'Espiritualidad', '', '', 1, 0, '', '', '', '', '', '', 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `producto_categoria`
--
ALTER TABLE `producto_categoria`
  ADD PRIMARY KEY (`producto_categoria_id`),
  ADD KEY `rel_empresa_id` (`rel_empresa_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `producto_categoria`
--
ALTER TABLE `producto_categoria`
  MODIFY `producto_categoria_id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12967;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
