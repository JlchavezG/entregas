CREATE DATABASE delivery_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE delivery_db;

CREATE TABLE repartidores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100)
);

CREATE TABLE entregas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(255),
    direccion_origen VARCHAR(255) NOT NULL,
    lat_origen DECIMAL(10, 8),
    lng_origen DECIMAL(11, 8),
    direccion_destino VARCHAR(255) NOT NULL,
    lat_destino DECIMAL(10, 8),
    lng_destino DECIMAL(11, 8),
    fecha_entrega DATE NOT NULL,
    hora_entrega TIME NOT NULL,
    repartidor_id INT,
    estado ENUM('pendiente','en ruta','entregado') DEFAULT 'pendiente',
    FOREIGN KEY (repartidor_id) REFERENCES repartidores(id) ON DELETE SET NULL
);

-- Datos de ejemplo
INSERT INTO repartidores (nombre, telefono, email) VALUES
('Carlos Méndez', '5512345678', 'carlos@delivery.com'),
('Ana Rojas', '5587654321', 'ana@delivery.com');