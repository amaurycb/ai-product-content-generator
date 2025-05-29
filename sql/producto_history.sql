-- phpMyAdmin SQL Dump
-- version 4.9.10
-- https://www.phpmyadmin.net/
--
-- Host: obuma2.cluster-cdxecnghta3h.us-east-1.rds.amazonaws.com
-- Generation Time: May 29, 2025 at 10:06 AM
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
-- Table structure for table `producto_history`
--

CREATE TABLE `producto_history` (
  `ph_id` int NOT NULL,
  `ph_fecha` datetime NOT NULL,
  `ph_status` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ph_descripcion` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `rel_usuario_id` int NOT NULL,
  `rel_producto_id` int NOT NULL,
  `rel_empresa_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `producto_history`
--

INSERT INTO `producto_history` (`ph_id`, `ph_fecha`, `ph_status`, `ph_descripcion`, `rel_usuario_id`, `rel_producto_id`, `rel_empresa_id`) VALUES
(1, '2023-05-18 15:12:59', 'Edita costo ultima compra', 'Via manual', 9543, 794095, 2),
(2, '2023-05-18 22:12:54', 'Edita producto', 'Via csv full', 48, 238962, 2),
(3, '2023-05-18 22:14:45', 'Edita costo promedio', 'Via manual', 48, 238962, 2),
(4, '2023-05-18 22:15:05', 'Edita costo producto', 'Via manual', 48, 238962, 2),
(5, '2023-05-18 22:15:05', 'Edita producto', 'Via manual', 48, 238962, 2),
(6, '2023-05-18 22:54:01', 'Edita LdM - Agrega item...', 'Via manual', 48, 722517, 2),
(7, '2023-05-18 22:55:02', 'Edita LdM - Elimina item...', 'Via manual', 48, 59625, 2),
(8, '2023-05-18 22:56:09', 'Edita costo producto', 'Via manual', 13951, 792203, 1619),
(9, '2023-05-18 22:56:09', 'Edita producto', 'Via manual', 13951, 792203, 1619),
(10, '2023-05-18 22:57:10', 'Edita costo producto', 'Via manual', 13951, 792203, 1619);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `producto_history`
--
ALTER TABLE `producto_history`
  ADD PRIMARY KEY (`ph_id`),
  ADD KEY `rel_producto_id` (`rel_producto_id`),
  ADD KEY `rel_empresa_id` (`rel_empresa_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `producto_history`
--
ALTER TABLE `producto_history`
  MODIFY `ph_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3073677;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
