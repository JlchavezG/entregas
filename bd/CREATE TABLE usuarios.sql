CREATE TABLE delivery_db.usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'repartidor', 'sistemas') NOT NULL,
    repartidor_id INT NULL, -- solo para rol 'repartidor'
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (repartidor_id) REFERENCES repartidores(id) ON DELETE SET NULL
);