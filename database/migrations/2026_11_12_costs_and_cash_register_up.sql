-- Migration reversible para costos historicos, sesiones de caja y cierres.
-- Productos/ventas existentes reciben NULL: no se inventan costos historicos.
ALTER TABLE productos
    ADD COLUMN costo_adquisicion DECIMAL(12,2) NULL DEFAULT NULL AFTER precio;
ALTER TABLE productos
    ADD CONSTRAINT chk_productos_precio_no_negativo CHECK (precio >= 0),
    ADD CONSTRAINT chk_productos_costo_no_negativo CHECK (costo_adquisicion IS NULL OR costo_adquisicion >= 0);

CREATE TABLE caja_sesiones (
    id INT(11) NOT NULL AUTO_INCREMENT,
    usuario_id INT(11) NOT NULL,
    fecha_apertura DATETIME NOT NULL,
    monto_inicial DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    estado ENUM('abierta','cerrada') NOT NULL DEFAULT 'abierta',
    usuario_abierto_id INT(11) GENERATED ALWAYS AS
        (CASE WHEN estado = 'abierta' THEN usuario_id ELSE NULL END) STORED,
    PRIMARY KEY (id),
    UNIQUE KEY uq_caja_usuario_abierta (usuario_abierto_id),
    KEY idx_caja_usuario_apertura (usuario_id, fecha_apertura),
    CONSTRAINT fk_caja_sesiones_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id),
    CONSTRAINT chk_caja_monto_inicial CHECK (monto_inicial >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cierres_caja (
    id INT(11) NOT NULL AUTO_INCREMENT,
    caja_sesion_id INT(11) NOT NULL,
    usuario_cierre_id INT(11) NOT NULL,
    fecha_cierre DATETIME NOT NULL,
    operaciones INT(11) NOT NULL DEFAULT 0,
    total_ventas DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_efectivo DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_transferencia DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_tarjeta DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_otros DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    canal_tienda DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    canal_whatsapp DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    canal_instagram DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    efectivo_esperado DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    efectivo_contado DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    diferencia DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    observaciones VARCHAR(500) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cierre_caja_sesion (caja_sesion_id),
    KEY idx_cierre_caja_fecha (fecha_cierre),
    KEY idx_cierre_caja_usuario (usuario_cierre_id, fecha_cierre),
    CONSTRAINT fk_cierres_caja_sesion FOREIGN KEY (caja_sesion_id)
        REFERENCES caja_sesiones (id),
    CONSTRAINT fk_cierres_caja_usuario FOREIGN KEY (usuario_cierre_id)
        REFERENCES usuarios (id),
    CONSTRAINT chk_cierre_efectivo_no_negativo CHECK (efectivo_esperado >= 0 AND efectivo_contado >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE movimientos_caja (
    id INT(11) NOT NULL AUTO_INCREMENT,
    caja_sesion_id INT(11) NOT NULL,
    usuario_id INT(11) NOT NULL,
    tipo ENUM('entrada','salida') NOT NULL,
    monto DECIMAL(12,2) NOT NULL,
    descripcion VARCHAR(250) NOT NULL,
    fecha DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_movimientos_caja_sesion_fecha (caja_sesion_id, fecha),
    CONSTRAINT fk_movimientos_caja_sesion FOREIGN KEY (caja_sesion_id)
        REFERENCES caja_sesiones (id),
    CONSTRAINT fk_movimientos_caja_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id),
    CONSTRAINT chk_movimiento_caja_monto_positivo CHECK (monto > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE ventas
    ADD COLUMN costo_unitario_historico DECIMAL(12,2) NULL DEFAULT NULL AFTER total,
    ADD COLUMN caja_sesion_id INT(11) NULL DEFAULT NULL AFTER metodo_pago,
    ADD KEY idx_ventas_caja_sesion (caja_sesion_id),
    ADD CONSTRAINT fk_ventas_caja_sesion FOREIGN KEY (caja_sesion_id)
        REFERENCES caja_sesiones (id),
    ADD CONSTRAINT chk_ventas_costo_historico_no_negativo CHECK (costo_unitario_historico IS NULL OR costo_unitario_historico >= 0);

