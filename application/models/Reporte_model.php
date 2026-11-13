<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reporte_model extends CI_Model
{
    private function rango($desde, $hasta)
    {
        return array($desde . ' 00:00:00', date('Y-m-d', strtotime($hasta . ' +1 day')) . ' 00:00:00');
    }

    public function resumen($desde, $hasta)
    {
        list($inicio, $fin) = $this->rango($desde, $hasta);
        return $this->db->query("SELECT
            COUNT(DISTINCT COALESCE(NULLIF(codigo_venta,''), CONCAT('LEGACY-', id))) AS operaciones,
            COALESCE(SUM(total),0) AS ingresos,
            COALESCE(SUM(cantidad),0) AS unidades
            FROM ventas WHERE fecha >= ? AND fecha < ?", array($inicio, $fin))->row();
    }

    public function por_canal($desde, $hasta)
    {
        list($inicio, $fin) = $this->rango($desde, $hasta);
        return $this->db->query("SELECT canal,
            COUNT(DISTINCT COALESCE(NULLIF(codigo_venta,''), CONCAT('LEGACY-', id))) AS operaciones,
            COALESCE(SUM(total),0) AS ingresos, COALESCE(SUM(cantidad),0) AS unidades
            FROM ventas WHERE fecha >= ? AND fecha < ? GROUP BY canal ORDER BY ingresos DESC", array($inicio, $fin))->result();
    }

    public function productos_mas_vendidos($desde, $hasta)
    {
        list($inicio, $fin) = $this->rango($desde, $hasta);
        return $this->db->query("SELECT p.nombre, p.referencia, p.talla,
            SUM(v.cantidad) AS unidades, SUM(v.total) AS ingresos
            FROM ventas v INNER JOIN productos p ON p.id=v.producto_id
            WHERE v.fecha >= ? AND v.fecha < ?
            GROUP BY p.id, p.nombre, p.referencia, p.talla
            ORDER BY unidades DESC, ingresos DESC LIMIT 10", array($inicio, $fin))->result();
    }

    public function por_vendedor($desde, $hasta)
    {
        list($inicio, $fin) = $this->rango($desde, $hasta);
        return $this->db->query("SELECT COALESCE(u.nombre,'Usuario') AS vendedor,
            COUNT(DISTINCT COALESCE(NULLIF(v.codigo_venta,''), CONCAT('LEGACY-',v.id))) AS operaciones,
            SUM(v.total) AS ingresos
            FROM ventas v LEFT JOIN usuarios u ON u.id=v.usuario_id
            WHERE v.fecha >= ? AND v.fecha < ?
            GROUP BY u.id, u.nombre ORDER BY ingresos DESC", array($inicio, $fin))->result();
    }

    public function clientes_frecuentes($desde, $hasta)
    {
        list($inicio, $fin) = $this->rango($desde, $hasta);
        return $this->db->query("SELECT COALESCE(c.nombre,'Cliente sin registrar') AS cliente,
            c.telefono, COUNT(DISTINCT COALESCE(NULLIF(v.codigo_venta,''), CONCAT('LEGACY-',v.id))) AS operaciones,
            SUM(v.total) AS total
            FROM ventas v LEFT JOIN clientes c ON c.id=v.cliente_id
            WHERE v.fecha >= ? AND v.fecha < ?
            GROUP BY c.id, c.nombre, c.telefono ORDER BY total DESC LIMIT 10", array($inicio, $fin))->result();
    }

    public function resumen_ganancias($desde, $hasta)
    {
        list($inicio, $fin) = $this->rango($desde, $hasta);
        return $this->db->query("SELECT
            COALESCE(SUM(total),0) AS ingresos,
            COALESCE(SUM(CASE WHEN costo_unitario_historico IS NOT NULL THEN costo_unitario_historico * cantidad ELSE 0 END),0) AS costo_vendido_conocido,
            COALESCE(SUM(CASE WHEN costo_unitario_historico IS NOT NULL THEN total - costo_unitario_historico * cantidad ELSE 0 END),0) AS ganancia_bruta_conocida,
            COALESCE(SUM(CASE WHEN costo_unitario_historico IS NULL THEN total ELSE 0 END),0) AS ingresos_sin_costo_historico,
            COALESCE(SUM(CASE WHEN costo_unitario_historico IS NULL THEN 1 ELSE 0 END),0) AS lineas_sin_costo_historico
            FROM ventas WHERE fecha >= ? AND fecha < ?", array($inicio, $fin))->row();
    }

    public function ganancias_por_dia($desde, $hasta)
    {
        list($inicio, $fin) = $this->rango($desde, $hasta);
        return $this->db->query("SELECT DATE(fecha) AS dia, SUM(total) AS ingresos,
            SUM(CASE WHEN costo_unitario_historico IS NOT NULL THEN costo_unitario_historico * cantidad ELSE 0 END) AS costo_conocido,
            SUM(CASE WHEN costo_unitario_historico IS NOT NULL THEN total - costo_unitario_historico * cantidad ELSE 0 END) AS ganancia_bruta_conocida,
            SUM(CASE WHEN costo_unitario_historico IS NULL THEN 1 ELSE 0 END) AS lineas_sin_costo
            FROM ventas WHERE fecha >= ? AND fecha < ? GROUP BY DATE(fecha) ORDER BY dia", array($inicio, $fin))->result();
    }

    public function ganancias_por_canal($desde, $hasta)
    {
        list($inicio, $fin) = $this->rango($desde, $hasta);
        return $this->db->query("SELECT canal, SUM(total) AS ingresos,
            SUM(CASE WHEN costo_unitario_historico IS NOT NULL THEN costo_unitario_historico * cantidad ELSE 0 END) AS costo_conocido,
            SUM(CASE WHEN costo_unitario_historico IS NOT NULL THEN total - costo_unitario_historico * cantidad ELSE 0 END) AS ganancia_bruta_conocida,
            SUM(CASE WHEN costo_unitario_historico IS NULL THEN 1 ELSE 0 END) AS lineas_sin_costo
            FROM ventas WHERE fecha >= ? AND fecha < ? GROUP BY canal ORDER BY ingresos DESC", array($inicio, $fin))->result();
    }

    public function ganancias_por_producto($desde, $hasta)
    {
        list($inicio, $fin) = $this->rango($desde, $hasta);
        return $this->db->query("SELECT p.nombre, p.referencia, p.talla, SUM(v.cantidad) AS unidades,
            SUM(v.total) AS ingresos,
            SUM(CASE WHEN v.costo_unitario_historico IS NOT NULL THEN v.costo_unitario_historico * v.cantidad ELSE 0 END) AS costo_conocido,
            SUM(CASE WHEN v.costo_unitario_historico IS NOT NULL THEN v.total - v.costo_unitario_historico * v.cantidad ELSE 0 END) AS ganancia_bruta_conocida,
            SUM(CASE WHEN v.costo_unitario_historico IS NULL THEN 1 ELSE 0 END) AS lineas_sin_costo
            FROM ventas v INNER JOIN productos p ON p.id=v.producto_id
            WHERE v.fecha >= ? AND v.fecha < ?
            GROUP BY p.id,p.nombre,p.referencia,p.talla ORDER BY ganancia_bruta_conocida DESC LIMIT 50", array($inicio, $fin))->result();
    }

    public function valor_inventario()
    {
        return $this->db->query("SELECT COALESCE(SUM(CASE WHEN costo_adquisicion IS NOT NULL THEN costo_adquisicion * stock ELSE 0 END),0) AS total_conocido,
            SUM(CASE WHEN stock>0 AND costo_adquisicion IS NULL THEN 1 ELSE 0 END) AS productos_sin_costo
            FROM productos WHERE estado=1")->row();
    }

    public function usuarios_caja()
    {
        return $this->db->select('id,nombre')->where_in('rol', array('administrador','caja'))->where('estado',1)->order_by('nombre','ASC')->get('usuarios')->result();
    }
}
