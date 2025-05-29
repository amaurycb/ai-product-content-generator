-- phpMyAdmin SQL Dump
-- version 4.9.10
-- https://www.phpmyadmin.net/
--
-- Host: obuma2.cluster-cdxecnghta3h.us-east-1.rds.amazonaws.com
-- Generation Time: May 29, 2025 at 10:55 AM
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
-- Table structure for table `a_queue_jobs`
--

CREATE TABLE `a_queue_jobs` (
  `job_id` int NOT NULL,
  `job_name` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_path` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_message` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_created_by` int NOT NULL,
  `job_created_at` datetime NOT NULL,
  `job_updated_at` datetime NOT NULL,
  `job_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_status` int NOT NULL COMMENT '0=pendiente, 5=ok, 9=procesando',
  `job_status_detail` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_download_link` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_log` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rel_usuario_id` int NOT NULL,
  `rel_empresa_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `a_queue_jobs`
--

INSERT INTO `a_queue_jobs` (`job_id`, `job_name`, `job_path`, `job_message`, `job_created_by`, `job_created_at`, `job_updated_at`, `job_type`, `job_status`, `job_status_detail`, `job_download_link`, `job_log`, `rel_usuario_id`, `rel_empresa_id`) VALUES
(1, 'emails-intercambio', 'verifica-dte-get-contribuyentes-csv-to-mysql.php', '', 0, '0000-00-00 00:00:00', '2024-03-18 22:32:38', '', 5, '', '', '', 0, 2),
(2, 'prueba', 'https://test1.obuma.cl/obuma2.0/mod-contabilidad/libro-diario/download-xls.php?download=diferido&job_id=2&tokenu=20240802123658_06rm95ghv0tc1srgtckm0t8ncb', '{\r\n\"sql\" : \"SELECT *  FROM c_libro_diario WHERE ld_ano=\'2024\' AND rel_empresa_id=\'2\' ORDER BY ld_id DESC \"\r\n}', 0, '0000-00-00 00:00:00', '2024-08-02 12:39:56', '', 1, '', 'https://obuma.cl/mydata-tmp/libro-diario-02-08-2024-123947.xls', '', 0, 2),
(3, 'productos', 'https://test1.obuma.cl/obuma2.0/mod-productos/productos/download-productos-sin-imagenes-xls.php?download=diferido&job_id=3&tokenu=20241230095612_dinq75gi9ug3kv1bp1qnts7l1i', '', 0, '0000-00-00 00:00:00', '2024-12-30 23:45:27', '', 1, '', 'https://obuma.cl/mydata-tmp/productos-sin-imagenes-xls-113-30-12-2024-234411.xls', '<br>SQL : SELECT producto_id ,producto_tipo ,producto_codigo_comercial ,producto_nombre ,producto_precio_clp_neto ,producto_precio_clp_iva ,producto_precio_clp_total ,producto_costo_clp_neto_estandar ,producto_utilidad_porciento ,producto_categoria ,producto_subcategoria ,producto_fabricante FROM producto WHERE rel_empresa_id=113 ORDER BY producto_nombre ASC', 311, 113),
(4, 'productos', 'https://test1.obuma.cl/obuma2.0/mod-productos/productos/download-productos-sin-imagenes-xls.php?download=diferido&job_id=4&tokenu=20241230120417_1qoa8o59jns7m02uefgp8h972e', '', 0, '0000-00-00 00:00:00', '2024-12-30 23:47:19', '', 1, '', 'https://obuma.cl/mydata-tmp/productos-sin-imagenes-xls-575-30-12-2024-234511.xls', '<br>SQL : SELECT producto_id ,producto_tipo ,producto_codigo_comercial ,producto_nombre ,producto_precio_clp_neto ,producto_precio_clp_iva ,producto_precio_clp_total ,producto_costo_clp_neto_estandar ,producto_utilidad_porciento ,producto_categoria ,producto_subcategoria ,producto_fabricante FROM producto WHERE rel_empresa_id=575 ORDER BY producto_nombre ASC', 2335, 575),
(5, 'productos', 'https://test1.obuma.cl/obuma2.0/mod-productos/productos/download-productos-sin-imagenes-xls.php?download=diferido&job_id=5&tokenu=20241230191741_1qoa8o59jns7m02uefgp8h972e', '', 0, '0000-00-00 00:00:00', '2024-12-30 23:48:09', '', 1, '', 'https://obuma.cl/mydata-tmp/productos-sin-imagenes-xls-574-30-12-2024-234611.xls', '<br>SQL : SELECT producto_id ,producto_tipo ,producto_codigo_comercial ,producto_nombre ,producto_precio_clp_neto ,producto_precio_clp_iva ,producto_precio_clp_total ,producto_costo_clp_neto_estandar ,producto_utilidad_porciento ,producto_categoria ,producto_subcategoria ,producto_fabricante FROM producto WHERE rel_empresa_id=574 ORDER BY producto_nombre ASC', 2338, 574),
(6, 'productos', 'https://test1.obuma.cl/obuma2.0/cron-jobs/upload-productos-imagenes.php?job_id=6', '', 0, '0000-00-00 00:00:00', '2025-01-19 23:18:07', '', 1, '', '/var/www/html/efs/data-container/a-queue-files-upload/imagenes-prueba.csv', '<h3>Log de registros extraidos:</h3><div class=\"row-fluid\" style=\"border-bottom:1px solid #ededed;\"><div class=\"span2\"><b>Codigo Producto</b></div><div class=\"span8\"><b>Nombre Producto</b></div><div class=\"span2\"><b>Estado Upload</b></div></div><div class=\"row-fluid\" style=\"border-bottom:1px solid #ededed;\"><div class=\"span2\">prueba</div><div class=\"span8\">bozzo prueba</div><div class=\"span2\"><span class=\"label label-success\">Subido</span><br></div></div>', 9543, 2),
(7, 'upload-productos-imagenes-upload_678dbe32d18ee4.43154187', 'https://test1.obuma.cl/obuma2.0/cron-jobs/upload-productos-imagenes.php', '', 0, '2025-01-20 03:08:34', '2025-01-20 00:09:56', '', 1, '', '/var/www/html/efs/data-container/a-queue-files-upload/upload_678dbe32d18ee4.43154187', '<h3>Log de registros extraidos:</h3><div class=\"row-fluid\" style=\"border-bottom:1px solid #ededed;\"><div class=\"span2\"><b>Codigo Producto</b></div><div class=\"span8\"><b>Nombre Producto</b></div><div class=\"span2\"><b>Estado Upload</b></div></div><div class=\"row-fluid\" style=\"border-bottom:1px solid #ededed;\"><div class=\"span2\">prueba</div><div class=\"span8\">bozzo prueba</div><div class=\"span2\"><span class=\"label label-success\">Subido</span><br></div></div>', 9543, 2),
(8, 'upload-productos-imagenes-upload_678e80a1c28dc2.52992534', 'https://test1.obuma.cl/obuma2.0/cron-jobs/upload-productos-imagenes.php', '', 0, '2025-01-20 16:58:09', '2025-01-20 14:22:51', 'upload', 8, '', '/var/www/html/efs/data-container/a-queue-files-upload/upload_678e80a1c28dc2.52992534', '', 9543, 2),
(9, 'Sugerencia IA productos', 'https://test1.obuma.cl/obuma2.0/pruebas/regulariza-producto-ia-suggest.php?id_empresa=2552&id_categoria=12808&proccess=1&update=1', '', 0, '2025-02-18 14:46:58', '2025-02-18 17:43:09', '', 8, '', '', '', 0, 2552),
(10, 'upload-productos-imagenes-upload_67cadc358a07e8.06940965', 'https://app.obuma.cl/obuma2.0/cron-jobs/upload-productos-imagenes.php', '', 0, '2025-03-07 11:44:53', '2025-03-07 08:46:33', 'upload', 5, '', '/var/www/html/efs/data-container/a-queue-files-upload/upload_67cadc358a07e8.06940965', '<h3>Log de registros extraidos:</h3><div class=\"row-fluid\" style=\"border-bottom:1px solid #ededed;\"><div class=\"span2\"><b>Codigo Producto</b></div><div class=\"span8\"><b>Nombre Producto</b></div><div class=\"span2\"><b>Estado Upload</b></div></div><div class=\"row-fluid\" style=\"border-bottom:1px solid #ededed;\"><div class=\"span2\">34356792292</div><div class=\"span8\">SENSOR DESGASTE PASTILLA DE FRENO</div><div class=\"span2\"><span class=\"label label-success\">Subido</span><br></div></div><div class=\"row-fluid\" style=\"border-bottom:1px solid #ededed;\"><div class=\"span2\">705802</div><div class=\"span8\">LIQUIDO DE FRENOS ATE SL DOT41 LT</div><div class=\"span2\"><span class=\"label label-success\">Subido</span><br></div></div><div class=\"row-fluid\" style=\"border-bottom:1px solid #ededed;\"><div class=\"span2\">601429774</div><div class=\"span8\">ACEITE HIDRAULICO TITAN CHF 11S 1 LT</div><div class=\"span2\"><span class=\"label label-success\">Subido</span><br></div></div>', 27647, 1085);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `a_queue_jobs`
--
ALTER TABLE `a_queue_jobs`
  ADD PRIMARY KEY (`job_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `a_queue_jobs`
--
ALTER TABLE `a_queue_jobs`
  MODIFY `job_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
