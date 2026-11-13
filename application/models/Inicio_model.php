<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inicio_model extends CI_Model
{
    public function resumen_general()
    {
        $resumen = array();
        $resumen['productos'] = (int) $this->db->where('estado', 1)->count_all_results('productos');
        $resumen['unidades'] = (int) $this->db->select_sum('stock')->where('estado', 1)->get('productos')->row()->stock;
        $resumen['clientes'] = (int) $this->db->count_all('clientes');
        $resumen['inventario_costos'] = $this->db->query("SELECT
            COALESCE(SUM(CASE WHEN costo_adquisicion IS NOT NULL THEN costo_adquisicion * stock ELSE 0 END),0) AS total_conocido,
            SUM(CASE WHEN stock > 0 AND costo_adquisicion IS NULL THEN 1 ELSE 0 END) AS productos_sin_costo,
            COALESCE(SUM(CASE WHEN costo_adquisicion IS NULL THEN stock ELSE 0 END),0) AS unidades_sin_costo
            FROM productos WHERE estado=1")->row();
        $resumen['inventario_desglose'] = $this->db->select('id, nombre, referencia, talla, costo_adquisicion, stock, costo_adquisicion * stock AS valor_inventario', FALSE)
            ->where('estado', 1)->order_by('nombre', 'ASC')->order_by('talla', 'ASC')->get('productos')->result();

        $hoy = $this->db->query("SELECT COUNT(DISTINCT COALESCE(codigo_venta, CONCAT('LEGACY-', id))) AS operaciones,
                                        COALESCE(SUM(total), 0) AS total
                                 FROM ventas WHERE DATE(fecha) = CURDATE()")->row();
        $resumen['ventas_hoy'] = (int) $hoy->operaciones;
        $resumen['ingresos_hoy'] = (float) $hoy->total;

        $resumen['segmentos_clientes'] = $this->db->query("SELECT COALESCE(NULLIF(c.deporte_interes, ''), 'No especificado') AS deporte,
                    COUNT(DISTINCT c.id) AS clientes,
                    COUNT(DISTINCT CASE WHEN v.id IS NOT NULL THEN COALESCE(v.codigo_venta, CONCAT('LEGACY-', v.id)) END) AS compras,
                    COALESCE(SUM(v.total), 0) AS total
                FROM clientes c
                LEFT JOIN ventas v ON v.cliente_id = c.id
                GROUP BY COALESCE(NULLIF(c.deporte_interes, ''), 'No especificado')
                ORDER BY total DESC, clientes DESC")->result();

        $resumen['canales'] = $this->db->query("SELECT canal,
                    COUNT(DISTINCT COALESCE(codigo_venta, CONCAT('LEGACY-', id))) AS operaciones,
                    COALESCE(SUM(total), 0) AS total
                FROM ventas
                WHERE fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                GROUP BY canal ORDER BY total DESC")->result();

        $resumen['recientes'] = $this->db->query("SELECT
                    COALESCE(v.codigo_venta, CONCAT('Venta #', v.id)) AS codigo,
                    MAX(v.fecha) AS fecha,
                    COALESCE(MAX(c.nombre), 'Cliente sin registrar') AS cliente,
                    COALESCE(MAX(u.nombre), 'Usuario') AS vendedor,
                    v.canal,
                    SUM(v.total) AS total
                FROM ventas v
                LEFT JOIN clientes c ON c.id = v.cliente_id
                LEFT JOIN usuarios u ON u.id = v.usuario_id
                GROUP BY COALESCE(v.codigo_venta, CONCAT('LEGACY-', v.id)), v.canal
                ORDER BY MAX(v.fecha) DESC LIMIT 8")->result();

        $resumen['bajo_stock'] = $this->db->where('estado', 1)->where('stock <=', 3)
            ->order_by('stock', 'ASC')->limit(6)->get('productos')->result();

        return $resumen;
    }
}
