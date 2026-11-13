<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cliente_model extends CI_Model
{
    public function buscar_para_venta($termino, $limite = 10)
    {
        return $this->db
            ->select('id, nombre, telefono, correo, deporte_interes')
            ->group_start()
                ->like('nombre', $termino, 'after')
                ->or_like('telefono', $termino, 'after')
            ->group_end()
            ->order_by('nombre', 'ASC')
            ->limit((int) $limite)
            ->get('clientes')
            ->result();
    }

    public function obtener_todos()
    {
        return $this->db->query("SELECT c.*, COUNT(DISTINCT CASE WHEN v.id IS NOT NULL THEN COALESCE(v.codigo_venta, CONCAT('LEGACY-', v.id)) END) AS compras, COALESCE(SUM(v.total),0) AS total_comprado
            FROM clientes c LEFT JOIN ventas v ON v.cliente_id = c.id
            GROUP BY c.id ORDER BY c.fecha_registro DESC")->result();
    }

    public function obtener_por_id($id)
    {
        return $this->db->get_where('clientes', array('id' => $id))->row();
    }

    public function insertar($datos)
    {
        return $this->db->insert('clientes', $datos);
    }

    public function actualizar($id, $datos)
    {
        return $this->db->where('id', $id)->update('clientes', $datos);
    }

    public function telefono_existe($telefono, $excluir = NULL)
    {
        $this->db->where('telefono', $telefono);
        if ($excluir !== NULL) $this->db->where('id !=', $excluir);
        return $this->db->count_all_results('clientes') > 0;
    }

    public function obtener_historial($id)
    {
        return $this->db->query("SELECT
            COALESCE(NULLIF(v.codigo_venta,''), CONCAT('Venta #', MIN(v.id))) AS codigo,
            MAX(v.fecha) AS fecha,
            v.canal,
            COALESCE(MAX(v.metodo_pago), 'No especificado') AS metodo_pago,
            GROUP_CONCAT(CONCAT(p.nombre, ' (talla ', p.talla, ') x', v.cantidad) ORDER BY p.nombre SEPARATOR ', ') AS productos,
            SUM(v.total) AS total
            FROM ventas v
            INNER JOIN productos p ON p.id=v.producto_id
            WHERE v.cliente_id=?
            GROUP BY COALESCE(NULLIF(v.codigo_venta,''), CONCAT('LEGACY-',v.id)), v.canal
            ORDER BY MAX(v.fecha) DESC", array((int) $id))->result();
    }
}
