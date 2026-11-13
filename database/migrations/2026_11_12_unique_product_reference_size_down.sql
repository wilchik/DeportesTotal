-- Reversion de la unicidad por referencia y talla.
ALTER TABLE productos
    DROP INDEX uq_productos_referencia_talla;