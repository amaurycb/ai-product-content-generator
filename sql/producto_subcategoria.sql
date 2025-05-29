-- phpMyAdmin SQL Dump
-- version 4.9.10
-- https://www.phpmyadmin.net/
--
-- Host: obuma2.cluster-cdxecnghta3h.us-east-1.rds.amazonaws.com
-- Generation Time: May 29, 2025 at 10:08 AM
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
-- Table structure for table `producto_subcategoria`
--

CREATE TABLE `producto_subcategoria` (
  `producto_subcategoria_id` bigint NOT NULL,
  `producto_subcategoria_codigo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_subcategoria_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_subcategoria_posicion` int NOT NULL,
  `producto_subcategoria_imagen` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_subcategoria_mostrar` int NOT NULL,
  `producto_subcategoria_metatitle` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_subcategoria_metadescription` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_subcategoria_metakeywords` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_subcategoria_urlseo` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `rel_producto_categoria_id` int NOT NULL,
  `rel_empresa_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `producto_subcategoria`
--

INSERT INTO `producto_subcategoria` (`producto_subcategoria_id`, `producto_subcategoria_codigo`, `producto_subcategoria_nombre`, `producto_subcategoria_posicion`, `producto_subcategoria_imagen`, `producto_subcategoria_mostrar`, `producto_subcategoria_metatitle`, `producto_subcategoria_metadescription`, `producto_subcategoria_metakeywords`, `producto_subcategoria_urlseo`, `rel_producto_categoria_id`, `rel_empresa_id`) VALUES
(1, '', 'Kit\'s videovigilancia', 1, '', 0, '', '', '', '', 1, 4),
(2, '', 'Cámaras ocultas, espias', 6, '', 0, '', '', '', '', 1, 4),
(3, '', 'Cámaras infrarrojo', 3, '', 0, '', '', '', '', 1, 4),
(4, '', 'Cámaras domo', 4, '', 0, '', '', '', '', 1, 4),
(5, '', 'Cámaras PTZ', 7, '', 0, '', '', '', '', 1, 4),
(6, '', 'DVR autonomos / Standalone', 2, '', 0, '', '', '', '', 1, 4),
(7, '', 'Tarjetas DVR para PC', 0, '', 0, '', '', '', '', 1, 4),
(8, '', 'Teclados Joystick para PTZ', 14, '', 1, 'Teclados Joystick para PTZ', 'Teclados Joystick para PTZ', 'Teclados Joystick para PTZ', 'teclados-joystick-para-ptz', 1, 4),
(9, '', 'Alarmas Inalambricas', 0, '', 0, 'Alarmas Inalambricas', '', '', 'alarmas-inalambricas', 2, 4),
(10, '', 'Cámaras IP', 8, '', 0, '', '', '', '', 1, 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `producto_subcategoria`
--
ALTER TABLE `producto_subcategoria`
  ADD PRIMARY KEY (`producto_subcategoria_id`),
  ADD KEY `rel_empresa_id` (`rel_empresa_id`),
  ADD KEY `rel_producto_categoria_id` (`rel_producto_categoria_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `producto_subcategoria`
--
ALTER TABLE `producto_subcategoria`
  MODIFY `producto_subcategoria_id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25629;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
