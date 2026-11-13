<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Venta_model extends CI_Model
{
    public function productos_disponibles()
    {
        return $this->db->where('estado', 1)->where('stock >', 0)
            ->order_by('nombre', 'ASC')->order_by('talla', 'ASC')->get('productos')->result();
    }

    public function clientes_disponibles()
    {
        return $this->db->order_by('nombre', 'ASC')->get('clientes')->result();
    }

    public function registrar_venta($datos)
    {
        if (!isset($datos['metodo_pago']) || !in_array($datos['metodo_pago'], array('Efectivo', 'Transferencia', 'Tarjeta'), TRUE)) {
            return array('ok' => FALSE, 'mensaje' => 'Selecciona un método de pago válido.', 'persistente' => TRUE);
        }
        if (!isset($datos['canal']) || !in_array($datos['canal'], array('Tienda', 'WhatsApp', 'Instagram'), TRUE)) {
            return array('ok' => FALSE, 'mensaje' => 'Selecciona un canal de venta válido.', 'persistente' => TRUE);
        }
        $items = $datos['items'];
        if (empty($items) || !is_array($items)) {
            return array('ok' => FALSE, 'mensaje' => 'Agrega al menos un producto a la venta.', 'persistente' => TRUE);
        }
        if (count($items) > 30) {
            return array('ok' => FALSE, 'mensaje' => 'Una venta no puede superar 30 líneas de productos.', 'persistente' => TRUE);
        }

        $this->db->trans_begin();
        $sesion = $this->db->query("SELECT cs.id FROM caja_sesiones cs INNER JOIN usuarios u ON u.id=cs.usuario_id WHERE cs.usuario_id=? AND cs.estado='abierta' AND u.estado=1 AND u.rol IN ('administrador','caja') FOR UPDATE", array((int) $datos['usuario_id']))->row();
        if (!$sesion) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'mensaje' => 'Abre una caja antes de registrar ventas.', 'persistente' => TRUE);
        }
        $caja_sesion_id = (int) $sesion->id;
        $cliente_id = (int) $datos['cliente_id'];
        if ($cliente_id <= 0) {
            $nuevo = $datos['nuevo_cliente'];
            if (empty($nuevo['nombre']) || empty($nuevo['telefono'])) {
                $this->db->trans_rollback();
                return array('ok' => FALSE, 'mensaje' => 'Selecciona un cliente o registra su nombre y teléfono.', 'persistente' => TRUE);
            }
            $existente = $this->db->where('telefono', $nuevo['telefono'])->get('clientes')->row();
            if ($existente) {
                $cliente_id = (int) $existente->id;
                $actualizar_cliente = array('nombre' => $nuevo['nombre'], 'deporte_interes' => $nuevo['deporte_interes']);
                if ($nuevo['correo'] !== '') $actualizar_cliente['correo'] = $nuevo['correo'];
                $this->db->where('id', $cliente_id)->update('clientes', $actualizar_cliente);
            } else {
                $this->db->insert('clientes', array(
                    'nombre' => $nuevo['nombre'],
                    'telefono' => $nuevo['telefono'],
                    'correo' => $nuevo['correo'] !== '' ? $nuevo['correo'] : NULL,
                    'deporte_interes' => $nuevo['deporte_interes'] !== '' ? $nuevo['deporte_interes'] : 'No especificado'
                ));
                $cliente_id = (int) $this->db->insert_id();
            }
        } else {
            $existe = $this->db->where('id', $cliente_id)->count_all_results('clientes');
            if (!$existe) {
                $this->db->trans_rollback();
                return array('ok' => FALSE, 'mensaje' => 'El cliente seleccionado no existe.', 'persistente' => TRUE);
            }
        }

        $codigo = 'DT-' . date('Ymd-His') . '-' . (int) $datos['usuario_id'] . '-' . mt_rand(100, 999);
        $total_venta_centavos = 0;
        $cantidades = array();
        foreach ($items as $item) {
            $producto_id = isset($item['producto_id']) ? (int) $item['producto_id'] : 0;
            $cantidad = isset($item['cantidad']) ? filter_var($item['cantidad'], FILTER_VALIDATE_INT) : FALSE;
            if ($producto_id <= 0 || $cantidad === FALSE || $cantidad <= 0) {
                $this->db->trans_rollback();
                return array('ok' => FALSE, 'mensaje' => 'Revisa los productos y las cantidades ingresadas.', 'persistente' => TRUE);
            }
            if (!isset($cantidades[$producto_id])) $cantidades[$producto_id] = 0;
            $cantidades[$producto_id] += (int) $cantidad;
        }

        $lineas = array();
        foreach ($cantidades as $producto_id => $cantidad) {
            $producto = $this->db->query('SELECT * FROM productos WHERE id=? AND estado=1 FOR UPDATE', array($producto_id))->row();
            if (!$producto || (int) $producto->stock < $cantidad) {
                $nombre = $producto ? $producto->nombre . ' - talla ' . $producto->talla : 'El producto seleccionado';
                $this->db->trans_rollback();
                return array('ok' => FALSE, 'mensaje' => $nombre . ' no tiene existencias suficientes.', 'persistente' => TRUE);
            }
            $subtotal_centavos = $this->centavos($producto->precio) * $cantidad;
            $total_venta_centavos += $subtotal_centavos;
            $lineas[] = array('producto' => $producto, 'cantidad' => $cantidad, 'subtotal' => $this->decimal($subtotal_centavos));
        }

        foreach ($lineas as $linea) {
            $producto = $linea['producto'];
            $cantidad = $linea['cantidad'];
            $this->db->insert('ventas', array(
                'codigo_venta' => $codigo,
                'cliente_id' => $cliente_id,
                'producto_id' => (int) $producto->id,
                'usuario_id' => (int) $datos['usuario_id'],
                'canal' => $datos['canal'],
                'metodo_pago' => $datos['metodo_pago'],
                'caja_sesion_id' => $caja_sesion_id,
                'cantidad' => $cantidad,
                'total' => $linea['subtotal'],
                'costo_unitario_historico' => $producto->costo_adquisicion
            ));
            $this->db->where('id', $producto->id)->update('productos', array('stock' => (int) $producto->stock - $cantidad));
            $this->db->insert('movimientos_inventario', array(
                'producto_id' => (int) $producto->id,
                'usuario_id' => (int) $datos['usuario_id'],
                'tipo' => 'salida',
                'cantidad' => $cantidad,
                'motivo' => 'Venta ' . $codigo,
                'fecha' => date('Y-m-d H:i:s')
            ));
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'mensaje' => 'Ocurrió un error al guardar la venta. No se modificó el inventario.', 'persistente' => FALSE);
        }
        $this->db->trans_commit();
        return array('ok' => TRUE, 'codigo' => $codigo, 'total' => $this->decimal($total_venta_centavos));
    }

    private function centavos($valor)
    {
        $valor = (string) $valor;
        if (!preg_match('/^(\d+)(?:\.(\d{1,2}))?$/', $valor, $partes)) return 0;
        return ((int) $partes[1] * 100) + (int) str_pad(isset($partes[2]) ? $partes[2] : '', 2, '0', STR_PAD_RIGHT);
    }

    private function decimal($centavos)
    {
        $centavos = (int) $centavos;
        return intdiv($centavos, 100) . '.' . str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
    }

    public function historial_ventas($usuario_id = NULL, $solo_usuario = FALSE)
    {
        $where = '';
        $params = array();
        if ($solo_usuario) {
            $where = 'WHERE v.usuario_id = ?';
            $params[] = (int) $usuario_id;
        }
        return $this->db->query("SELECT
            COALESCE(NULLIF(v.codigo_venta,''), CONCAT('Venta #', MIN(v.id))) AS codigo,
            MAX(v.fecha) AS fecha,
            COALESCE(MAX(c.nombre), 'Cliente sin registrar') AS cliente,
            COALESCE(MAX(u.nombre), 'Usuario') AS vendedor,
            v.canal,
            COALESCE(MAX(v.metodo_pago), 'No especificado') AS metodo_pago,
            SUM(v.cantidad) AS unidades,
            SUM(v.total) AS total
            FROM ventas v
            LEFT JOIN clientes c ON c.id=v.cliente_id
            LEFT JOIN usuarios u ON u.id=v.usuario_id
            $where
            GROUP BY COALESCE(NULLIF(v.codigo_venta,''), CONCAT('LEGACY-',v.id)), v.canal
            ORDER BY MAX(v.fecha) DESC LIMIT 200", $params)->result();
    }
}
