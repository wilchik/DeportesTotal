-- Indices usados por los buscadores del punto de venta.
-- Reversion: ejecutar 2026_11_12_sales_search_indexes_down.sql.
ALTER TABLE productos ADD INDEX idx_productos_estado_nombre (estado, nombre);
ALTER TABLE clientes ADD INDEX idx_clientes_nombre (nombre);
ALTER TABLE clientes ADD INDEX idx_clientes_telefono (telefono);