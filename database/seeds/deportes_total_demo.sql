-- Deportes Total: base de demostración académica anonimizada, derivada del snapshot del 12/11/2026.
-- Incluye estructura, registros comerciales y relaciones completas.
-- IMPORTANTE: este dump elimina/recrea las tablas del esquema destino; úselo solo en una base vacía o descartable.
-- Cuentas: propietario_demo, administrador_demo y caja_demo. Clave común: DeportesDemo2026!
CREATE DATABASE IF NOT EXISTS deportes_total CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE deportes_total;
-- MariaDB dump 10.19  Distrib 10.4.27-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: deportes_total
-- ------------------------------------------------------
-- Server version	10.11.16-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `caja_sesiones`
--

DROP TABLE IF EXISTS `caja_sesiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caja_sesiones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `fecha_apertura` datetime NOT NULL,
  `monto_inicial` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` enum('abierta','cerrada') NOT NULL DEFAULT 'abierta',
  `usuario_abierto_id` int(11) GENERATED ALWAYS AS (case when `estado` = 'abierta' then `usuario_id` else NULL end) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_caja_usuario_abierta` (`usuario_abierto_id`),
  KEY `idx_caja_usuario_apertura` (`usuario_id`,`fecha_apertura`),
  CONSTRAINT `fk_caja_sesiones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `chk_caja_monto_inicial` CHECK (`monto_inicial` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caja_sesiones`
--

LOCK TABLES `caja_sesiones` WRITE;
/*!40000 ALTER TABLE `caja_sesiones` DISABLE KEYS */;
INSERT INTO `caja_sesiones` VALUES (1,4,'2026-11-09 09:40:00',150000.00,'cerrada',NULL),(2,3,'2026-11-10 08:30:00',120000.00,'cerrada',NULL),(3,5,'2026-11-11 09:00:00',100000.00,'cerrada',NULL),(4,5,'2026-11-13 00:26:04',100000.00,'cerrada',NULL),(5,6,'2026-11-13 00:44:05',100000.00,'abierta',6);
/*!40000 ALTER TABLE `caja_sesiones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cierres_caja`
--

DROP TABLE IF EXISTS `cierres_caja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cierres_caja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caja_sesion_id` int(11) NOT NULL,
  `usuario_cierre_id` int(11) NOT NULL,
  `fecha_cierre` datetime NOT NULL,
  `operaciones` int(11) NOT NULL DEFAULT 0,
  `total_ventas` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_efectivo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_transferencia` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_tarjeta` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_otros` decimal(12,2) NOT NULL DEFAULT 0.00,
  `canal_tienda` decimal(12,2) NOT NULL DEFAULT 0.00,
  `canal_whatsapp` decimal(12,2) NOT NULL DEFAULT 0.00,
  `canal_instagram` decimal(12,2) NOT NULL DEFAULT 0.00,
  `efectivo_esperado` decimal(12,2) NOT NULL DEFAULT 0.00,
  `efectivo_contado` decimal(12,2) NOT NULL DEFAULT 0.00,
  `diferencia` decimal(12,2) NOT NULL DEFAULT 0.00,
  `observaciones` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cierre_caja_sesion` (`caja_sesion_id`),
  KEY `idx_cierre_caja_fecha` (`fecha_cierre`),
  KEY `idx_cierre_caja_usuario` (`usuario_cierre_id`,`fecha_cierre`),
  CONSTRAINT `fk_cierres_caja_sesion` FOREIGN KEY (`caja_sesion_id`) REFERENCES `caja_sesiones` (`id`),
  CONSTRAINT `fk_cierres_caja_usuario` FOREIGN KEY (`usuario_cierre_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `chk_cierre_efectivo_no_negativo` CHECK (`efectivo_esperado` >= 0 and `efectivo_contado` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cierres_caja`
--

LOCK TABLES `cierres_caja` WRITE;
/*!40000 ALTER TABLE `cierres_caja` DISABLE KEYS */;
INSERT INTO `cierres_caja` VALUES (1,1,4,'2026-11-09 18:00:00',4,649000.00,315000.00,75000.00,259000.00,0.00,315000.00,75000.00,259000.00,465000.00,465000.00,0.00,'Cierre de demostraci?n'),(2,2,3,'2026-11-10 18:00:00',5,666000.00,223000.00,184000.00,259000.00,0.00,223000.00,259000.00,184000.00,343000.00,343000.00,0.00,'Cierre de demostraci?n'),(3,3,5,'2026-11-11 18:00:00',4,567000.00,184000.00,219000.00,164000.00,0.00,184000.00,219000.00,164000.00,284000.00,282000.00,-2000.00,'Cierre de demostraci?n'),(4,4,5,'2026-11-13 00:31:52',3,728000.00,558000.00,170000.00,0.00,0.00,653000.00,75000.00,0.00,678000.00,638000.00,-40000.00,'Cierre de demostraci?n');
/*!40000 ALTER TABLE `cierres_caja` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `deporte_interes` varchar(60) NOT NULL DEFAULT 'No especificado',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_clientes_nombre` (`nombre`),
  KEY `idx_clientes_telefono` (`telefono`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (17,'Cliente Demo 017','3000000017','cliente17@example.invalid','Fútbol','2026-11-09 12:30:00'),(18,'Cliente Demo 018','3000000018','cliente18@example.invalid','Running','2026-11-09 12:32:00'),(19,'Cliente Demo 019','3000000019','cliente19@example.invalid','Gimnasio','2026-11-09 12:34:00'),(20,'Cliente Demo 020','3000000020','cliente20@example.invalid','Baloncesto','2026-11-09 12:36:00'),(21,'Cliente Demo 021','3000000021','cliente21@example.invalid','Ciclismo','2026-11-09 12:38:00'),(22,'Cliente Demo 022','3000000022','cliente22@example.invalid','Voleibol','2026-11-09 12:40:00'),(23,'Cliente Demo 023','3000000023','cliente23@example.invalid','Fútbol','2026-11-09 12:42:00'),(24,'Cliente Demo 024','3000000024','cliente24@example.invalid','Running','2026-11-09 12:44:00'),(25,'Cliente Demo 025','3000000025','cliente25@example.invalid','Gimnasio','2026-11-09 12:46:00'),(26,'Cliente Demo 026','3000000026','cliente26@example.invalid','Ciclismo','2026-11-09 12:48:00');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compras`
--

DROP TABLE IF EXISTS `compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `compras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proveedor_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `observaciones` varchar(250) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_compras_proveedor` (`proveedor_id`),
  KEY `idx_compras_usuario` (`usuario_id`),
  KEY `idx_compras_fecha` (`fecha`),
  CONSTRAINT `fk_compras_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  CONSTRAINT `fk_compras_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compras`
--

LOCK TABLES `compras` WRITE;
/*!40000 ALTER TABLE `compras` DISABLE KEYS */;
INSERT INTO `compras` VALUES (1,1,3,1135000.00,'Compra de demostraci?n','2026-11-09 13:00:00'),(2,2,3,1650000.00,'Compra de demostraci?n','2026-11-09 14:00:00'),(3,3,3,874000.00,'Compra de demostraci?n','2026-11-10 13:00:00'),(4,1,2,760000.00,'Compra de demostraci?n','2026-11-12 23:36:52');
/*!40000 ALTER TABLE `compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compras_detalle`
--

DROP TABLE IF EXISTS `compras_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `compras_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `compra_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `costo_unitario` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_compra_detalle_compra` (`compra_id`),
  KEY `idx_compra_detalle_producto` (`producto_id`),
  CONSTRAINT `fk_detalle_compra` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`),
  CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compras_detalle`
--

LOCK TABLES `compras_detalle` WRITE;
/*!40000 ALTER TABLE `compras_detalle` DISABLE KEYS */;
INSERT INTO `compras_detalle` VALUES (1,1,18,6,65000.00,390000.00),(2,1,19,5,65000.00,325000.00),(3,1,22,5,42000.00,210000.00),(4,1,25,12,12000.00,144000.00),(5,1,26,3,22000.00,66000.00),(6,2,20,3,180000.00,540000.00),(7,2,23,4,38000.00,152000.00),(8,2,24,3,38000.00,114000.00),(9,2,28,3,58000.00,174000.00),(10,2,29,3,45000.00,135000.00),(11,2,30,3,31000.00,93000.00),(12,2,31,2,145000.00,290000.00),(13,2,32,4,38000.00,152000.00),(14,3,18,4,65000.00,260000.00),(15,3,21,2,180000.00,360000.00),(16,3,25,6,12000.00,72000.00),(17,3,28,2,58000.00,116000.00),(18,3,27,3,22000.00,66000.00),(19,4,32,20,38000.00,760000.00);
/*!40000 ALTER TABLE `compras_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_caja`
--

DROP TABLE IF EXISTS `movimientos_caja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movimientos_caja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caja_sesion_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` enum('entrada','salida') NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `descripcion` varchar(250) NOT NULL,
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_movimientos_caja_sesion_fecha` (`caja_sesion_id`,`fecha`),
  KEY `fk_movimientos_caja_usuario` (`usuario_id`),
  CONSTRAINT `fk_movimientos_caja_sesion` FOREIGN KEY (`caja_sesion_id`) REFERENCES `caja_sesiones` (`id`),
  CONSTRAINT `fk_movimientos_caja_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `chk_movimiento_caja_monto_positivo` CHECK (`monto` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_caja`
--

LOCK TABLES `movimientos_caja` WRITE;
/*!40000 ALTER TABLE `movimientos_caja` DISABLE KEYS */;
INSERT INTO `movimientos_caja` VALUES (1,4,5,'entrada',20000.00,'Movimiento de caja de demostraci?n','2026-11-13 00:30:12');
/*!40000 ALTER TABLE `movimientos_caja` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_inventario`
--

DROP TABLE IF EXISTS `movimientos_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movimientos_inventario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo` enum('entrada','salida') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `motivo` varchar(180) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_mov_producto` (`producto_id`),
  KEY `idx_mov_usuario` (`usuario_id`),
  CONSTRAINT `fk_mov_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `fk_mov_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_inventario`
--

LOCK TABLES `movimientos_inventario` WRITE;
/*!40000 ALTER TABLE `movimientos_inventario` DISABLE KEYS */;
INSERT INTO `movimientos_inventario` VALUES (51,18,3,'entrada',6,'Movimiento de inventario de demostraci?n','2026-11-09 08:05:00'),(52,19,3,'entrada',5,'Movimiento de inventario de demostraci?n','2026-11-09 08:05:00'),(53,22,3,'entrada',5,'Movimiento de inventario de demostraci?n','2026-11-09 08:05:00'),(54,25,3,'entrada',12,'Movimiento de inventario de demostraci?n','2026-11-09 08:05:00'),(55,26,3,'entrada',3,'Movimiento de inventario de demostraci?n','2026-11-09 08:05:00'),(56,20,3,'entrada',3,'Movimiento de inventario de demostraci?n','2026-11-09 09:05:00'),(57,23,3,'entrada',4,'Movimiento de inventario de demostraci?n','2026-11-09 09:05:00'),(58,24,3,'entrada',3,'Movimiento de inventario de demostraci?n','2026-11-09 09:05:00'),(59,28,3,'entrada',3,'Movimiento de inventario de demostraci?n','2026-11-09 09:05:00'),(60,29,3,'entrada',3,'Movimiento de inventario de demostraci?n','2026-11-09 09:05:00'),(61,30,3,'entrada',3,'Movimiento de inventario de demostraci?n','2026-11-09 09:05:00'),(62,31,3,'entrada',2,'Movimiento de inventario de demostraci?n','2026-11-09 09:05:00'),(63,32,3,'entrada',4,'Movimiento de inventario de demostraci?n','2026-11-09 09:05:00'),(64,18,4,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-09 10:00:00'),(65,25,4,'salida',2,'Movimiento de inventario de demostraci?n','2026-11-09 10:00:00'),(66,22,4,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-09 11:10:00'),(67,20,4,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-09 12:00:00'),(68,23,4,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-09 15:00:00'),(69,28,4,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-09 15:00:00'),(70,18,3,'entrada',4,'Movimiento de inventario de demostraci?n','2026-11-10 08:05:00'),(71,21,3,'entrada',2,'Movimiento de inventario de demostraci?n','2026-11-10 08:05:00'),(72,25,3,'entrada',6,'Movimiento de inventario de demostraci?n','2026-11-10 08:05:00'),(73,28,3,'entrada',2,'Movimiento de inventario de demostraci?n','2026-11-10 08:05:00'),(74,27,3,'entrada',3,'Movimiento de inventario de demostraci?n','2026-11-10 08:05:00'),(75,19,3,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-10 09:00:00'),(76,26,3,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-10 10:30:00'),(77,25,3,'salida',2,'Movimiento de inventario de demostraci?n','2026-11-10 10:30:00'),(78,21,3,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-10 12:00:00'),(79,29,3,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-10 14:00:00'),(80,32,3,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-10 16:30:00'),(81,24,3,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-10 16:30:00'),(82,30,5,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-11 10:00:00'),(83,25,5,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-11 10:00:00'),(84,31,5,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-11 12:00:00'),(85,28,5,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-11 14:00:00'),(86,32,5,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-11 14:00:00'),(87,18,5,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-11 16:00:00'),(88,19,5,'salida',4,'Movimiento de inventario de demostraci?n','2026-11-13 00:28:24'),(89,32,5,'salida',2,'Movimiento de inventario de demostraci?n','2026-11-13 00:28:24'),(90,28,5,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-13 00:29:16'),(91,22,5,'salida',1,'Movimiento de inventario de demostraci?n','2026-11-13 00:30:45'),(92,32,2,'entrada',20,'Movimiento de inventario de demostraci?n','2026-11-13 00:33:58'),(93,32,2,'entrada',20,'Movimiento de inventario de demostraci?n','2026-11-13 00:36:52');
/*!40000 ALTER TABLE `movimientos_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `referencia` varchar(50) NOT NULL,
  `talla` varchar(20) DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `costo_adquisicion` decimal(12,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `estado` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_productos_referencia_talla` (`referencia`,`talla`),
  KEY `idx_productos_referencia_talla` (`referencia`,`talla`),
  KEY `idx_productos_estado_nombre` (`estado`,`nombre`),
  CONSTRAINT `chk_productos_precio_no_negativo` CHECK (`precio` >= 0),
  CONSTRAINT `chk_productos_costo_no_negativo` CHECK (`costo_adquisicion` is null or `costo_adquisicion` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (18,'Camiseta Adidas Colombia','AD-COL-01','M',105000.00,65000.00,8,1),(19,'Camiseta Adidas Colombia','AD-COL-01','L',105000.00,65000.00,0,1),(20,'Tenis Nike Running','NI-RUN-02','38',259000.00,180000.00,2,1),(21,'Tenis Nike Running','NI-RUN-02','39',259000.00,180000.00,1,1),(22,'Balón Golty fútbol No. 5','GOL-FUT-03','5',75000.00,42000.00,3,1),(23,'Pantaloneta Puma deportiva','PUM-PAN-04','M',65000.00,38000.00,3,1),(24,'Pantaloneta Puma deportiva','PUM-PAN-04','L',65000.00,38000.00,2,1),(25,'Medias deportivas Soccer','SP-MED-05','36-40',25000.00,12000.00,13,1),(26,'Guantes para gimnasio','REE-GUA-06','M',39000.00,22000.00,2,1),(27,'Guantes para gimnasio','REE-GUA-06','L',39000.00,22000.00,3,1),(28,'Balón de voleibol','WIL-VOLE-07','Única',95000.00,58000.00,2,1),(29,'Mochila deportiva','AD-MOCH-08','Única',79000.00,45000.00,2,1),(30,'Guantes para ciclismo','AS-CIL-09','M',54000.00,31000.00,2,1),(31,'Tenis deportivos indoor','TEN-IND-10','41',219000.00,145000.00,1,1),(32,'Balón de fútbol sala','BL-FUT-11','4',69000.00,38000.00,40,1);
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(25) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `nit` varchar(30) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_proveedores_nombre` (`nombre`),
  KEY `idx_proveedores_telefono` (`telefono`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'Proveedor Demo 01','Contacto Demo 01','6010000001','proveedor1@example.invalid','900000000-1',1,'2026-11-09 12:10:00'),(2,'Proveedor Demo 02','Contacto Demo 02','6010000002','proveedor2@example.invalid','900000000-2',1,'2026-11-09 12:12:00'),(3,'Proveedor Demo 03','Contacto Demo 03','6010000003','proveedor3@example.invalid','900000000-3',1,'2026-11-09 12:15:00');
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('propietario','administrador','caja') NOT NULL,
  `estado` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Propietario Demo','propietario_demo','$2y$10$MOxPZ5rduNl40CcWFzGe5eI6zZgg7AYb7tB6WlbMX.n1lzyX3AzFW','propietario',1),(2,'Propietario Auxiliar Demo','propietario_demo_aux','$2y$10$MOxPZ5rduNl40CcWFzGe5eI6zZgg7AYb7tB6WlbMX.n1lzyX3AzFW','propietario',1),(3,'Administrador Demo','administrador_demo','$2y$10$MOxPZ5rduNl40CcWFzGe5eI6zZgg7AYb7tB6WlbMX.n1lzyX3AzFW','administrador',1),(4,'Caja Demo','caja_demo','$2y$10$MOxPZ5rduNl40CcWFzGe5eI6zZgg7AYb7tB6WlbMX.n1lzyX3AzFW','caja',1),(5,'Caja Auxiliar Demo','caja_demo_aux','$2y$10$MOxPZ5rduNl40CcWFzGe5eI6zZgg7AYb7tB6WlbMX.n1lzyX3AzFW','caja',1),(6,'Administrador Auxiliar Demo','administrador_demo_aux','$2y$10$MOxPZ5rduNl40CcWFzGe5eI6zZgg7AYb7tB6WlbMX.n1lzyX3AzFW','administrador',1);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ventas`
--

DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ventas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_venta` varchar(45) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `producto_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `canal` enum('Tienda','WhatsApp','Instagram') NOT NULL,
  `metodo_pago` varchar(30) NOT NULL DEFAULT 'No especificado',
  `caja_sesion_id` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `costo_unitario_historico` decimal(12,2) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `producto_id` (`producto_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_ventas_codigo` (`codigo_venta`),
  KEY `idx_ventas_fecha_canal` (`fecha`,`canal`),
  KEY `idx_ventas_caja_sesion` (`caja_sesion_id`),
  CONSTRAINT `fk_ventas_caja_sesion` FOREIGN KEY (`caja_sesion_id`) REFERENCES `caja_sesiones` (`id`),
  CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `ventas_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `chk_ventas_costo_historico_no_negativo` CHECK (`costo_unitario_historico` is null or `costo_unitario_historico` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas`
--

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
INSERT INTO `ventas` VALUES (34,'DT-DEMO-20261109-01',17,18,4,'Tienda','Efectivo',1,1,105000.00,65000.00,'2026-11-09 15:00:00'),(35,'DT-DEMO-20261109-01',17,25,4,'Tienda','Efectivo',1,2,50000.00,12000.00,'2026-11-09 15:00:00'),(36,'DT-DEMO-20261109-02',18,22,4,'WhatsApp','Transferencia',1,1,75000.00,42000.00,'2026-11-09 16:10:00'),(37,'DT-DEMO-20261109-03',19,20,4,'Instagram','Tarjeta',1,1,259000.00,180000.00,'2026-11-09 17:00:00'),(38,'DT-DEMO-20261109-04',20,23,4,'Tienda','Efectivo',1,1,65000.00,38000.00,'2026-11-09 20:00:00'),(39,'DT-DEMO-20261109-04',20,28,4,'Tienda','Efectivo',1,1,95000.00,58000.00,'2026-11-09 20:00:00'),(40,'DT-DEMO-20261110-01',17,19,3,'Instagram','Transferencia',2,1,105000.00,65000.00,'2026-11-10 14:00:00'),(41,'DT-DEMO-20261110-02',21,26,3,'Tienda','Efectivo',2,1,39000.00,22000.00,'2026-11-10 15:30:00'),(42,'DT-DEMO-20261110-02',21,25,3,'Tienda','Efectivo',2,2,50000.00,12000.00,'2026-11-10 15:30:00'),(43,'DT-DEMO-20261110-03',18,21,3,'WhatsApp','Tarjeta',2,1,259000.00,180000.00,'2026-11-10 17:00:00'),(44,'DT-DEMO-20261110-04',22,29,3,'Instagram','Transferencia',2,1,79000.00,45000.00,'2026-11-10 19:00:00'),(45,'DT-DEMO-20261110-05',23,32,3,'Tienda','Efectivo',2,1,69000.00,38000.00,'2026-11-10 21:30:00'),(46,'DT-DEMO-20261110-05',23,24,3,'Tienda','Efectivo',2,1,65000.00,38000.00,'2026-11-10 21:30:00'),(47,'DT-DEMO-20261111-01',24,30,5,'Tienda','Efectivo',3,1,54000.00,31000.00,'2026-11-11 15:00:00'),(48,'DT-DEMO-20261111-01',24,25,5,'Tienda','Efectivo',3,1,25000.00,12000.00,'2026-11-11 15:00:00'),(49,'DT-DEMO-20261111-02',17,31,5,'WhatsApp','Transferencia',3,1,219000.00,145000.00,'2026-11-11 17:00:00'),(50,'DT-DEMO-20261111-03',25,28,5,'Instagram','Tarjeta',3,1,95000.00,58000.00,'2026-11-11 19:00:00'),(51,'DT-DEMO-20261111-03',25,32,5,'Instagram','Tarjeta',3,1,69000.00,38000.00,'2026-11-11 19:00:00'),(52,'DT-DEMO-20261111-04',26,18,5,'Tienda','Efectivo',3,1,105000.00,65000.00,'2026-11-11 21:00:00'),(53,'DT-20261113-002824-5-269',23,19,5,'Tienda','Efectivo',4,4,420000.00,65000.00,'2026-11-12 23:28:24'),(54,'DT-20261113-002824-5-269',23,32,5,'Tienda','Efectivo',4,2,138000.00,38000.00,'2026-11-12 23:28:24'),(55,'DT-20261113-002916-5-369',24,28,5,'Tienda','Transferencia',4,1,95000.00,58000.00,'2026-11-12 23:29:16'),(56,'DT-20261113-003045-5-595',21,22,5,'WhatsApp','Transferencia',4,1,75000.00,42000.00,'2026-11-12 23:30:45');
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'deportes_total'
--

--
-- Dumping routines for database 'deportes_total'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-12 19:08:02
