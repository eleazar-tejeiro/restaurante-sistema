CREATE TABLE `usuarios` (
  `id_usuario` INT PRIMARY KEY AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  `apellido` VARCHAR(50) NOT NULL,
  `username` VARCHAR(20) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `tipo_usuario` ENUM ('administrador', 'vendedor') NOT NULL
);

CREATE TABLE `productos` (
  `id_producto` INT PRIMARY KEY AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` TEXT,
  `precio_compra` DECIMAL(10,2) NOT NULL,
  `precio_venta` DECIMAL(10,2) NOT NULL,
  `fecha_vencimiento` DATE
);

CREATE TABLE `ventas` (
  `id_venta` INT PRIMARY KEY AUTO_INCREMENT,
  `id_usuario` INT,
  `fecha_venta` DATETIME DEFAULT (CURRENT_TIMESTAMP),
  `total` DECIMAL(10,2) NOT NULL
);

CREATE TABLE `detalle_ventas` (
  `id_detalle` INT PRIMARY KEY AUTO_INCREMENT,
  `id_venta` INT,
  `id_producto` INT,
  `cantidad` INT NOT NULL,
  `precio_unitario` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL
);

CREATE TABLE `proveedores` (
  `id_proveedor` INT PRIMARY KEY AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `direccion` VARCHAR(255),
  `telefono` VARCHAR(20)
);

CREATE TABLE `compras` (
  `id_compra` INT PRIMARY KEY AUTO_INCREMENT,
  `id_proveedor` INT,
  `id_usuario` INT,
  `fecha_compra` DATETIME DEFAULT (CURRENT_TIMESTAMP),
  `total` DECIMAL(10,2) NOT NULL
);

CREATE TABLE `detalle_compras` (
  `id_detalle_compra` INT PRIMARY KEY AUTO_INCREMENT,
  `id_compra` INT,
  `id_producto` INT,
  `cantidad` INT NOT NULL,
  `precio_unitario` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `lote` VARCHAR(50),
  `fecha_caducidad` DATE
);

CREATE TABLE `inventario` (
  `id_inventario` INT PRIMARY KEY AUTO_INCREMENT,
  `id_producto` INT,
  `cantidad` INT NOT NULL,
  `lote` VARCHAR(50),
  `fecha_caducidad` DATE
);

CREATE TABLE `productos_desperdiciados` (
  `id_desperdicio` INT PRIMARY KEY AUTO_INCREMENT,
  `id_inventario` INT,
  `cantidad` INT NOT NULL,
  `fecha_desperdicio` DATE NOT NULL,
  `motivo` VARCHAR(255) NOT NULL
);

CREATE TABLE `movimientos_inventario` (
  `id_movimiento` INT PRIMARY KEY AUTO_INCREMENT,
  `id_inventario` INT,
  `tipo_movimiento` ENUM ('entrada', 'salida', 'ajuste') NOT NULL,
  `cantidad` INT NOT NULL,
  `fecha_movimiento` DATETIME DEFAULT (CURRENT_TIMESTAMP),
  `id_referencia` INT,
  `tipo_referencia` ENUM ('compra', 'venta', 'desperdicio', 'ajuste') NOT NULL
);

ALTER TABLE `ventas` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `detalle_ventas` ADD FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`);

ALTER TABLE `detalle_ventas` ADD FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

ALTER TABLE `compras` ADD FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`);

ALTER TABLE `compras` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `detalle_compras` ADD FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id_compra`);

ALTER TABLE `detalle_compras` ADD FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

ALTER TABLE `inventario` ADD FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

ALTER TABLE `productos_desperdiciados` ADD FOREIGN KEY (`id_inventario`) REFERENCES `inventario` (`id_inventario`);

ALTER TABLE `movimientos_inventario` ADD FOREIGN KEY (`id_inventario`) REFERENCES `inventario` (`id_inventario`);
