-- Creación de la base de datos en MySQL
CREATE DATABASE restaurante;
USE restaurante;

-- Table for users
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    username VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('administrador', 'vendedor') NOT NULL
);

-- Table for products
CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio_compra DECIMAL(10, 2) NOT NULL,
    precio_venta DECIMAL(10, 2) NOT NULL,
    fecha_vencimiento DATE
);

-- Table for sales
CREATE TABLE ventas (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    fecha_venta DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- Table for sale details
CREATE TABLE detalle_ventas (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT,
    id_producto INT,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

-- Table for suppliers
CREATE TABLE proveedores (
    id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(255),
    telefono VARCHAR(20)
    );

-- Table for purchases
CREATE TABLE compras (
    id_compra INT AUTO_INCREMENT PRIMARY KEY,
    id_proveedor INT,
    id_usuario INT,
    fecha_compra DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- Table for purchase details
CREATE TABLE detalle_compras (
    id_detalle_compra INT AUTO_INCREMENT PRIMARY KEY,
    id_compra INT,
    id_producto INT,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    lote VARCHAR(50),
    fecha_caducidad DATE,
    FOREIGN KEY (id_compra) REFERENCES compras(id_compra),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

-- Table for inventory
CREATE TABLE inventario (
    id_inventario INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT,
    cantidad INT NOT NULL,
    lote VARCHAR(50),
    fecha_caducidad DATE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

-- Table for wasted products
CREATE TABLE productos_desperdiciados (
    id_desperdicio INT AUTO_INCREMENT PRIMARY KEY,
    id_inventario INT,
    cantidad INT NOT NULL,
    fecha_desperdicio DATE NOT NULL,
    motivo VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_inventario) REFERENCES inventario(id_inventario)
);

-- Table for inventory movements
CREATE TABLE movimientos_inventario (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_inventario INT,
    tipo_movimiento ENUM('entrada', 'salida', 'ajuste') NOT NULL,
    cantidad INT NOT NULL,
    fecha_movimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_referencia INT,
    tipo_referencia ENUM('compra', 'venta', 'desperdicio', 'ajuste') NOT NULL,
    FOREIGN KEY (id_inventario) REFERENCES inventario(id_inventario)
);

-- Trigger to update inventory after a purchase
DELIMITER //
CREATE TRIGGER after_purchase_detail_insert
AFTER INSERT ON detalle_compras
FOR EACH ROW
BEGIN
    INSERT INTO inventario (id_producto, cantidad, lote, fecha_caducidad)
    VALUES (NEW.id_producto, NEW.cantidad, NEW.lote, NEW.fecha_caducidad)
    ON DUPLICATE KEY UPDATE cantidad = cantidad + NEW.cantidad;

    INSERT INTO movimientos_inventario (id_inventario, tipo_movimiento, cantidad, id_referencia, tipo_referencia)
    VALUES (
        (SELECT id_inventario FROM inventario WHERE id_producto = NEW.id_producto AND lote = NEW.lote),
        'entrada',
        NEW.cantidad,
        NEW.id_compra,
        'compra'
    );
END;
//
DELIMITER ;

-- Trigger to update inventory after a sale
DELIMITER //
CREATE TRIGGER after_sale_detail_insert
AFTER INSERT ON detalle_ventas
FOR EACH ROW
BEGIN
    UPDATE inventario
    SET cantidad = cantidad - NEW.cantidad
    WHERE id_producto = NEW.id_producto
    AND cantidad >= NEW.cantidad
    ORDER BY fecha_caducidad ASC
    LIMIT 1;

    INSERT INTO movimientos_inventario (id_inventario, tipo_movimiento, cantidad, id_referencia, tipo_referencia)
    VALUES (
        (SELECT id_inventario FROM inventario WHERE id_producto = NEW.id_producto ORDER BY fecha_caducidad ASC LIMIT 1),
        'salida',
        NEW.cantidad,
        NEW.id_venta,
        'venta'
    );
END;
//
DELIMITER ;

-- Trigger to update inventory after waste recording
DELIMITER //
CREATE TRIGGER after_waste_insert
AFTER INSERT ON productos_desperdiciados
FOR EACH ROW
BEGIN
    UPDATE inventario
    SET cantidad = cantidad - NEW.cantidad
    WHERE id_inventario = NEW.id_inventario;

    INSERT INTO movimientos_inventario (id_inventario, tipo_movimiento, cantidad, id_referencia, tipo_referencia)
    VALUES (
        NEW.id_inventario,
        'salida',
        NEW.cantidad,
        NEW.id_desperdicio,
        'desperdicio'
    );
END;
//
DELIMITER ;

-- View for current inventory levels
CREATE VIEW v_inventario_actual AS
SELECT 
    i.id_inventario,
    p.id_producto,
    p.nombre AS producto,
    i.cantidad,
    i.lote,
    i.fecha_caducidad
FROM 
    inventario i
JOIN 
    productos p ON i.id_producto = p.id_producto
WHERE 
    i.cantidad > 0;

-- Stored procedure for inventory valuation
DELIMITER //
CREATE PROCEDURE sp_valoracion_inventario()
BEGIN
    SELECT 
        p.id_producto,
        p.nombre AS producto,
        SUM(i.cantidad) AS cantidad_total,
        p.precio_compra,
        SUM(i.cantidad * p.precio_compra) AS valor_total
    FROM 
        inventario i
    JOIN 
        productos p ON i.id_producto = p.id_producto
    GROUP BY 
        p.id_producto, p.nombre, p.precio_compra;
END;
//
DELIMITER ;

-- Stored procedure for low stock alert
DELIMITER //
CREATE PROCEDURE sp_alerta_stock_bajo(IN umbral INT)
BEGIN
    SELECT 
        p.id_producto,
        p.nombre AS producto,
        SUM(i.cantidad) AS stock_actual
    FROM 
        inventario i
    JOIN 
        productos p ON i.id_producto = p.id_producto
    GROUP BY 
        p.id_producto, p.nombre
    HAVING 
        stock_actual < umbral
    ORDER BY 
        stock_actual ASC;
END;
//
DELIMITER ;