-- Reversion de los indices de busqueda del punto de venta.
ALTER TABLE productos DROP INDEX idx_productos_estado_nombre;
ALTER TABLE clientes DROP INDEX idx_clientes_nombre;
ALTER TABLE clientes DROP INDEX idx_clientes_telefono;