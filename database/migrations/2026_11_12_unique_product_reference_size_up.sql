-- Deportes Total: unicidad del catalogo por referencia y talla.
-- Precondicion: verificar que la consulta de duplicados no devuelva filas.
-- Reversion: ejecutar 2026_11_12_unique_product_reference_size_down.sql.
ALTER TABLE productos
    ADD UNIQUE KEY uq_productos_referencia_talla (referencia, talla);