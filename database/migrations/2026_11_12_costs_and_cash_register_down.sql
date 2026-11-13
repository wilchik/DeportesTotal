-- Reversion estructural. Debe ejecutarse solo si se decide retirar esta fase.
ALTER TABLE ventas
    DROP FOREIGN KEY fk_ventas_caja_sesion,
    DROP CHECK chk_ventas_costo_historico_no_negativo,
    DROP KEY idx_ventas_caja_sesion,
    DROP COLUMN caja_sesion_id,
    DROP COLUMN costo_unitario_historico;

DROP TABLE cierres_caja;
DROP TABLE movimientos_caja;
DROP TABLE caja_sesiones;

ALTER TABLE productos
    DROP CHECK chk_productos_precio_no_negativo,
    DROP CHECK chk_productos_costo_no_negativo;
ALTER TABLE productos DROP COLUMN costo_adquisicion;
