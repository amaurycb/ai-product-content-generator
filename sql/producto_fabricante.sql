-- phpMyAdmin SQL Dump
-- version 4.9.10
-- https://www.phpmyadmin.net/
--
-- Host: obuma2.cluster-cdxecnghta3h.us-east-1.rds.amazonaws.com
-- Generation Time: May 29, 2025 at 11:01 AM
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
-- Table structure for table `producto_fabricante`
--

CREATE TABLE `producto_fabricante` (
  `producto_fabricante_id` bigint NOT NULL,
  `producto_fabricante_codigo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_fabricante_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_fabricante_imagen` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_fabricante_url` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rel_empresa_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `producto_fabricante`
--

INSERT INTO `producto_fabricante` (`producto_fabricante_id`, `producto_fabricante_codigo`, `producto_fabricante_nombre`, `producto_fabricante_imagen`, `producto_fabricante_url`, `rel_empresa_id`) VALUES
(2, '', 'Rosenbauer Internacional', '', '', 3),
(3, '', 'CAMTEC', '', '', 4),
(4, '', 'Dahua security', '', '', 4),
(7, '', 'PHENIX HELMET', 'LogFinal.bmp', '', 3),
(8, '', 'BLACKHAWK', 'BL.png', '', 3),
(9, '', 'Chilectra', '', '', 13),
(10, '', 'Quanex', 'Quanex.jpg', 'www.quanex.cl', 11),
(11, '', 'PREUTECH', '', '', 10),
(15, '', 'MILTEX Japan', 'PUNZON BIOPSIA DERMICA MILTEX TODOS LOS DIAMETROS 1 A 8 mm.jpg', '', 13),
(16, '', 'Westernshelter', 'Logo WesternShelter.png', 'www.Westernshelter.com', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `producto_fabricante`
--
ALTER TABLE `producto_fabricante`
  ADD PRIMARY KEY (`producto_fabricante_id`),
  ADD KEY `rel_empresa_id` (`rel_empresa_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `producto_fabricante`
--
ALTER TABLE `producto_fabricante`
  MODIFY `producto_fabricante_id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8937;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
