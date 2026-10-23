-- Base de Datos: bdbella (Miss Uñas Valentina)

SET FOREIGN_KEY_CHECKS=0;

-- Estructura de tabla para `atencion`
DROP TABLE IF EXISTS `atencion`;
CREATE TABLE `atencion` (
  `id_atencion` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int NOT NULL,
  `id_servicio` int NOT NULL,
  `id_usuario` int NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `observaciones` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado` enum('Reservado','En Atencion','Finalizado','Cancelado') COLLATE utf8mb4_general_ci DEFAULT 'Reservado',
  `total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_atencion`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_servicio` (`id_servicio`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `atencion_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  CONSTRAINT `atencion_ibfk_2` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`),
  CONSTRAINT `atencion_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos para la tabla `atencion`
INSERT INTO `atencion` (`id_atencion`, `id_cliente`, `id_servicio`, `id_usuario`, `fecha`, `hora`, `observaciones`, `estado`, `total`) VALUES
('1', '1', '1', '2', '2026-07-01', '09:00:00', 'Primera visita', 'Finalizado', '35.00'),
('2', '2', '3', '2', '2026-07-01', '10:00:00', 'Cliente frecuente', 'En Atencion', '120.00'),
('3', '3', '2', '3', '2026-07-02', '14:00:00', 'Sin observaciones', 'Finalizado', '50.00'),
('4', '4', '5', '3', '2026-07-03', '15:30:00', 'Canceló por viaje', 'Cancelado', '80.00'),
('5', '5', '4', '2', '2026-07-04', '11:00:00', 'Reserva confirmada', 'Reservado', '150.00');

-- Estructura de tabla para `categorias`
DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos para la tabla `categorias`
INSERT INTO `categorias` (`id_categoria`, `nombre`) VALUES
('1', 'Cosméticos'),
('2', 'Maquillaje'),
('3', 'Cuidado Capilar'),
('4', 'Manicure'),
('5', 'Pedicure');

-- Estructura de tabla para `clientes`
DROP TABLE IF EXISTS `clientes`;
CREATE TABLE `clientes` (
  `id_cliente` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_cliente`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos para la tabla `clientes`
INSERT INTO `clientes` (`id_cliente`, `nombre`, `apellido`, `telefono`, `email`) VALUES
('1', 'Ana', 'Lopez', '72541236', 'ana@gmail.com'),
('2', 'Carla', 'Flores', '72569874', 'carla@gmail.com'),
('3', 'Lucia', 'Perez', '72457896', 'lucia@gmail.com'),
('4', 'Mariela', 'Rodriguez', '71236547', 'mariela@gmail.com'),
('5', 'Sofia', 'Condori', '76541236', 'sofia@gmail.com');

-- Estructura de tabla para `compras`
DROP TABLE IF EXISTS `compras`;
CREATE TABLE `compras` (
  `id_compra` int NOT NULL AUTO_INCREMENT,
  `id_proveedor` int DEFAULT NULL,
  `id_usuario` int NOT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_compra`),
  KEY `id_proveedor` (`id_proveedor`),
  CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos para la tabla `compras`
INSERT INTO `compras` (`id_compra`, `id_proveedor`, `id_usuario`, `total`, `fecha`) VALUES
('1', '1', '1', '350.00', '2026-06-01 00:00:00'),
('2', '2', '1', '420.00', '2026-06-05 00:00:00'),
('3', '3', '2', '500.00', '2026-06-08 00:00:00');

-- Estructura de tabla para `detalle_compra`
DROP TABLE IF EXISTS `detalle_compra`;
CREATE TABLE `detalle_compra` (
  `id_detalle` int NOT NULL AUTO_INCREMENT,
  `id_compra` int DEFAULT NULL,
  `id_producto` int DEFAULT NULL,
  `cantidad` int DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `id_compra` (`id_compra`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `detalle_compra_ibfk_1` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id_compra`),
  CONSTRAINT `detalle_compra_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos para la tabla `detalle_compra`
INSERT INTO `detalle_compra` (`id_detalle`, `id_compra`, `id_producto`, `cantidad`, `precio`) VALUES
('1', '1', '1', '10', '15.00'),
('2', '1', '2', '5', '40.00'),
('3', '2', '3', '5', '40.00'),
('4', '3', '4', '10', '18.00'),
('5', '3', '5', '8', '25.00');

-- Estructura de tabla para `detalleventa`
DROP TABLE IF EXISTS `detalleventa`;
CREATE TABLE `detalleventa` (
  `id_detalle` int NOT NULL AUTO_INCREMENT,
  `id_venta` int DEFAULT NULL,
  `id_producto` int DEFAULT NULL,
  `cantidad` int DEFAULT NULL,
  `precioventa` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `id_venta` (`id_venta`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `detalleventa_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`),
  CONSTRAINT `detalleventa_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos para la tabla `detalleventa`
INSERT INTO `detalleventa` (`id_detalle`, `id_venta`, `id_producto`, `cantidad`, `precioventa`) VALUES
('1', '1', '1', '2', '25.00'),
('2', '1', '4', '1', '35.00'),
('3', '2', '3', '1', '65.00'),
('4', '3', '1', '1', '25.00'),
('5', '4', '5', '2', '40.00');

-- Estructura de tabla para `lote_producto`
DROP TABLE IF EXISTS `lote_producto`;
CREATE TABLE `lote_producto` (
  `idlote` int NOT NULL AUTO_INCREMENT,
  `idproducto` int NOT NULL,
  `fecha_caducidad` date NOT NULL,
  `cantidad_producto_lote` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`idlote`),
  KEY `fk_lote_producto` (`idproducto`),
  CONSTRAINT `fk_lote_producto` FOREIGN KEY (`idproducto`) REFERENCES `productos` (`id_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos para la tabla `lote_producto`
INSERT INTO `lote_producto` (`idlote`, `idproducto`, `fecha_caducidad`, `cantidad_producto_lote`) VALUES
('1', '1', '2027-12-30', '20'),
('2', '2', '2027-11-15', '15'),
('3', '3', '2028-01-20', '10'),
('4', '4', '2027-10-18', '18'),
('5', '5', '2028-05-01', '12');

-- Estructura de tabla para `marca`
DROP TABLE IF EXISTS `marca`;
CREATE TABLE `marca` (
  `idmarca` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`idmarca`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos para la tabla `marca`
INSERT INTO `marca` (`idmarca`, `nombre`) VALUES
('1', 'Yanbal'),
('2', 'Natura'),
('3', 'Esika'),
('4', 'L\'Oréal'),
('5', 'Maybelline');

-- Estructura de tabla para `productos`
DROP TABLE IF EXISTS `productos`;
CREATE TABLE `productos` (
  `id_producto` int NOT NULL AUTO_INCREMENT,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `color` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `imagen` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `stock` int DEFAULT '0',
  `preciocompra` decimal(10,2) DEFAULT NULL,
  `precioventa` decimal(10,2) DEFAULT NULL,
  `id_categoria` int DEFAULT NULL,
  `idmarca` int NOT NULL,
  `id_proveedor` int DEFAULT NULL,
  `idlote` int NOT NULL,
  PRIMARY KEY (`id_producto`),
  KEY `id_categoria` (`id_categoria`),
  KEY `fk_producto_marca` (`idmarca`),
  KEY `fk_producto_proveedor` (`id_proveedor`),
  CONSTRAINT `fk_producto_marca` FOREIGN KEY (`idmarca`) REFERENCES `marca` (`idmarca`),
  CONSTRAINT `fk_producto_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON DELETE SET NULL,
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos para la tabla `productos`
INSERT INTO `productos` (`id_producto`, `descripcion`, `color`, `imagen`, `stock`, `preciocompra`, `precioventa`, `id_categoria`, `idmarca`, `id_proveedor`, `idlote`) VALUES
('1', 'ESMALTE ROJO', 'ROJO', NULL, '27', '15.00', '25.00', '4', '1', '1', '1'),
('2', 'SHAMPOO NATURA', NULL, NULL, '20', '35.00', '50.00', '3', '2', '2', '2'),
('3', 'BASE LÍQUIDA', 'BEIGE', NULL, '14', '40.00', '65.00', '2', '5', '3', '3'),
('4', 'LABIAL MATE', 'ROJO', NULL, '27', '18.00', '35.00', '2', '3', '1', '4'),
('5', 'CREMA HIDRATANTE', NULL, NULL, '18', '25.00', '40.00', '1', '2', '2', '5');

-- Estructura de tabla para `proveedores`
DROP TABLE IF EXISTS `proveedores`;
CREATE TABLE `proveedores` (
  `id_proveedor` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_general_ci,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_proveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos para la tabla `proveedores`
INSERT INTO `proveedores` (`id_proveedor`, `nombre`, `apellido`, `telefono`, `direccion`, `email`) VALUES
('1', 'Carlos', 'Mamani', '72457896', 'Oruro', 'carlos@gmail.com'),
('2', 'Juan', 'Perez', '72589632', 'La Paz', 'juan@gmail.com'),
('3', 'Maria', 'Quispe', '72365412', 'Cochabamba', 'maria@gmail.com');

-- Estructura de tabla para `reservas`
DROP TABLE IF EXISTS `reservas`;
CREATE TABLE `reservas` (
  `id_reserva` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int DEFAULT NULL,
  `id_servicio` int DEFAULT NULL,
  `id_usuario` int DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `subtotal` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `adelanto` decimal(10,2) NOT NULL DEFAULT 0,
  `estado` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_reserva`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_servicio` (`id_servicio`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`),
  CONSTRAINT `reservas_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos para la tabla `reservas`
INSERT INTO `reservas` (`id_reserva`, `id_cliente`, `id_servicio`, `id_usuario`, `fecha`, `hora`, `subtotal`, `adelanto`, `estado`) VALUES
('1', '1', '1', '2', '2026-07-01', '09:00:00', '35', '0', 'Reservado'),
('2', '2', '3', '2', '2026-07-01', '10:00:00', '120', '0', 'Reservado'),
('3', '3', '2', '3', '2026-07-02', '14:00:00', '50', '0', 'Finalizado'),
('4', '4', '5', '3', '2026-07-03', '15:30:00', '80', '0', 'Cancelado'),
('5', '5', '4', '2', '2026-07-04', '11:00:00', '150', '0', 'Reservado');

-- Estructura de tabla para `servicios`
DROP TABLE IF EXISTS `servicios`;
CREATE TABLE `servicios` (
  `id_servicio` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descripcion` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_servicio`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos para la tabla `servicios`
INSERT INTO `servicios` (`id_servicio`, `nombre`, `descripcion`, `precio`) VALUES
('1', 'Manicure', 'Manicure tradicional', '35.00'),
('2', 'Pedicure', 'Pedicure completo', '50.00'),
('3', 'Uñas Acrílicas', 'Aplicación de uñas', '120.00'),
('4', 'Maquillaje', 'Maquillaje profesional', '150.00'),
('5', 'Peinado', 'Peinado para eventos', '80.00');

-- Estructura de tabla para `usuario`
DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rol` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pago` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos para la tabla `usuario`
INSERT INTO `usuario` (`id_usuario`, `username`, `email`, `telefono`, `password`, `rol`, `pago`, `estado`, `fecha_creacion`) VALUES
('1', 'admin', 'admin@gmail.com', NULL, '123456', 'Administrador', NULL, 'Activo', '2026-06-29 15:34:07'),
('2', 'valentina', 'valentina@gmail.com', NULL, '123456', 'Empleado', NULL, 'Activo', '2026-06-29 15:34:07'),
('3', 'maria', 'maria@gmail.com', NULL, '123456', 'Empleado', NULL, 'Activo', '2026-06-29 15:34:07');

-- Estructura de tabla para `especialidades`
DROP TABLE IF EXISTS `especialidades`;
CREATE TABLE `especialidades` (
  `id_especialidad` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_servicio` int NOT NULL,
  PRIMARY KEY (`id_especialidad`),
  UNIQUE KEY `especialidades_usuario_servicio_unique` (`id_usuario`,`id_servicio`),
  KEY `especialidades_id_servicio_foreign` (`id_servicio`),
  CONSTRAINT `especialidades_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `especialidades_id_servicio_foreign` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `ventas`
DROP TABLE IF EXISTS `ventas`;
CREATE TABLE `ventas` (
  `id_venta` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int DEFAULT NULL,
  `id_usuario` int DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_venta`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos para la tabla `ventas`
INSERT INTO `ventas` (`id_venta`, `id_cliente`, `id_usuario`, `fecha`, `total`) VALUES
('1', '1', '1', '2026-06-10', '90.00'),
('2', '2', '2', '2026-06-12', '65.00'),
('3', '3', '2', '2026-06-14', '35.00'),
('4', '4', '3', '2026-06-16', '100.00');

SET FOREIGN_KEY_CHECKS=1;
