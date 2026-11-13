<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventario_model extends CI_Model
{
    public function obtener_productos()
    {
        return $this->db->where('estado', 1)->order_by('nombre', 'ASC')->order_by('talla', 'ASC')->get('productos')->result();
    }

    public function obtener_movimientos()
    {
        return $this->db->query("SELECT m.*, p.nombre AS producto, p.referencia, p.talla, u.nombre AS usuario
            FROM movimientos_inventario m
            INNER JOIN productos p ON p.id = m.producto_id
            LEFT JOIN usuarios u ON u.id = m.usuario_id
            ORDER BY m.fecha DESC, m.id DESC LIMIT 50")->result();
    }

    public function ajustar_stock($producto_id, $tipo, $cantidad, $motivo, $usuario_id)
    {
        $this->db->trans_begin();
        $producto = $this->db->query('SELECT * FROM productos WHERE id=? AND estado=1 FOR UPDATE', array($producto_id))->row();
        if (!$producto) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'mensaje' => 'El producto no existe o está inactivo.', 'persistente' => TRUE);
        }

        $nuevo_stock = (int) $producto->stock;
        if ($tipo === 'entrada') $nuevo_stock += $cantidad;
        elseif ($tipo === 'salida') $nuevo_stock -= $cantidad;
        else {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'mensaje' => 'Tipo de movimiento no válido.', 'persistente' => TRUE);
        }

        if ($nuevo_stock < 0) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'mensaje' => 'No hay suficientes unidades para registrar esa salida.', 'persistente' => TRUE);
        }

        $this->db->where('id', $producto_id)->update('productos', array('stock' => $nuevo_stock));
        $this->db->insert('movimientos_inventario', array(
            'producto_id' => $producto_id,
            'usuario_id' => $usuario_id,
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'motivo' => $motivo,
            'fecha' => date('Y-m-d H:i:s')
        ));

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'mensaje' => 'No fue posible guardar el movimiento.');
        }
        $this->db->trans_commit();
        return array('ok' => TRUE, 'mensaje' => 'Movimiento registrado correctamente.');
    }
}
