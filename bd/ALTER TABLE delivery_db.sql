ALTER TABLE delivery_db.entregas 
ADD COLUMN estado ENUM('pendiente', 'en ruta', 'entregado') DEFAULT 'pendiente' AFTER repartidor_id;