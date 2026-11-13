<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Compra_model extends CI_Model
{
    public function obtener_compras()
    {
        return $this->db->query("SELECT c.*, p.nombre AS proveedor, u.nombre AS usuario,
            (SELECT COUNT(*) FROM compras_detalle d WHERE d.compra_id=c.id) AS lineas
            FROM compras c INNER JOIN proveedores p ON p.id=c.proveedor_id
            LEFT JOIN usuarios u ON u.id=c.usuario_id ORDER BY c.fecha DESC LIMIT 100")->result();
    }

    public function proveedores_activos()
    {
        return $this->db->where('estado', 1)->order_by('nombre', 'ASC')->get('proveedores')->result();
    }

    public function obtener_proveedores()
    {
        return $this->db->order_by('nombre', 'ASC')->get('proveedores')->result();
    }

    public function obtener_proveedor($id)
    {
        return $this->db->get_where('proveedores', array('id' => $id))->row();
    }

    public function insertar_proveedor($datos)
    {
        $datos['estado'] = 1;
        return $this->db->insert('proveedores', $datos);
    }

    public function actualizar_proveedor($id, $datos)
    {
        return $this->db->where('id', $id)->update('proveedores', $datos);
    }

    public function cambiar_estado_proveedor($id, $estado)
    {
        return $this->db->where('id', $id)->update('proveedores', array('estado' => $estado));
    }

    public function telefono_proveedor_existe($telefono, $excluir = NULL)
    {
        if ($telefono === '') return FALSE;
        $this->db->where('telefono', $telefono);
        if ($excluir !== NULL) $this->db->where('id !=', $excluir);
        return $this->db->count_all_results('proveedores') > 0;
    }

    public function productos_activos()
    {
        return $this->db->where('estado', 1)->order_by('nombre', 'ASC')->order_by('talla', 'ASC')->get('productos')->result();
    }

    public function registrar_compra($datos)
    {
        $items = $datos['items'];
        if (empty($datos['proveedor_id']) || empty($items) || !is_array($items)) {
            return array('ok' => FALSE, 'mensaje' => 'Selecciona un proveedor y agrega al menos un producto.', 'persistente' => TRUE);
        }

        $this->db->trans_begin();
        $proveedor = $this->db->query('SELECT id FROM proveedores WHERE id=? AND estado=1 FOR UPDATE', array($datos['proveedor_id']))->row();
        if (!$proveedor) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'mensaje' => 'El proveedor seleccionado no existe o está inactivo.', 'persistente' => TRUE);
        }

        $items_validos = array();
        $stock_por_producto = array();
        $total_centavos = 0;
        foreach ($items as $item) {
            $producto_id = isset($item['producto_id']) ? (int) $item['producto_id'] : 0;
            $cantidad = isset($item['cantidad']) ? filter_var($item['cantidad'], FILTER_VALIDATE_INT) : FALSE;
            $costo = isset($item['costo']) ? $item['costo'] : NULL;
            if ($producto_id <= 0 || $cantidad === FALSE || $cantidad <= 0) {
                $this->db->trans_rollback();
                return array('ok' => FALSE, 'mensaje' => 'Revisa los productos, cantidades y costos ingresados.', 'persistente' => TRUE);
            }
            if (!isset($stock_por_producto[$producto_id])) {
                $producto = $this->db->query('SELECT * FROM productos WHERE id=? AND estado=1 FOR UPDATE', array($producto_id))->row();
                if (!$producto) {
                    $this->db->trans_rollback();
                    return array('ok' => FALSE, 'mensaje' => 'Uno de los productos no existe o está inactivo.', 'persistente' => TRUE);
                }
                $stock_por_producto[$producto_id] = array('producto' => $producto, 'cantidad' => 0, 'costo_total_centavos' => 0);
            }
            $costo = $this->normalizar_decimal($costo);
            if ($costo === FALSE) {
                $this->db->trans_rollback();
                return array('ok' => FALSE, 'mensaje' => 'El costo de compra debe ser válido y no negativo.', 'persistente' => TRUE);
            }
            $costo_centavos = $this->centavos($costo);
            $subtotal_centavos = $costo_centavos * (int) $cantidad;
            $subtotal = $this->decimal($subtotal_centavos);
            $stock_por_producto[$producto_id]['cantidad'] += (int) $cantidad;
            $stock_por_producto[$producto_id]['costo_total_centavos'] += $subtotal_centavos;
            $items_validos[] = array('producto_id' => $producto_id, 'cantidad' => (int) $cantidad, 'costo' => $costo, 'subtotal' => $subtotal);
            $total_centavos += $subtotal_centavos;
        }

        $this->db->insert('compras', array(
            'proveedor_id' => (int) $datos['proveedor_id'],
            'usuario_id' => (int) $datos['usuario_id'],
            'total' => $this->decimal($total_centavos),
            'observaciones' => $datos['observaciones']
        ));
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'mensaje' => 'No fue posible crear el registro de compra.');
        }
        $compra_id = (int) $this->db->insert_id();

        foreach ($items_validos as $linea) {
            $this->db->insert('compras_detalle', array(
                'compra_id' => $compra_id,
                'producto_id' => (int) $linea['producto_id'],
                'cantidad' => $linea['cantidad'],
                'costo_unitario' => $linea['costo'],
                'subtotal' => $linea['subtotal']
            ));
        }
        foreach ($stock_por_producto as $producto_id => $stock_info) {
            $producto = $stock_info['producto'];
            $cantidad_total = (int) $stock_info['cantidad'];
            $costo_compra_total = $this->decimal($stock_info['costo_total_centavos']);
            if ((int) $producto->stock === 0) {
                $costo_medio_centavos = intdiv(($stock_info['costo_total_centavos'] * 2 + $cantidad_total), (2 * $cantidad_total));
                $this->db->where('id', $producto_id)->update('productos', array(
                    'stock' => $cantidad_total,
                    'costo_adquisicion' => $this->decimal($costo_medio_centavos)
                ));
            } elseif ($producto->costo_adquisicion !== NULL) {
                $this->db->query('UPDATE productos SET costo_adquisicion=ROUND((costo_adquisicion*stock+?)/(stock+?),2), stock=stock+? WHERE id=?', array($costo_compra_total, $cantidad_total, $cantidad_total, (int) $producto_id));
            } else {
                $this->db->where('id', $producto_id)->update('productos', array('stock' => (int) $producto->stock + $cantidad_total));
            }
            $this->db->insert('movimientos_inventario', array(
                'producto_id' => (int) $producto_id,
                'usuario_id' => (int) $datos['usuario_id'],
                'tipo' => 'entrada',
                'cantidad' => $cantidad_total,
                'motivo' => 'Compra #' . $compra_id,
                'fecha' => date('Y-m-d H:i:s')
            ));
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'mensaje' => 'La compra no se pudo completar. No se actualizaron las existencias.');
        }
        $this->db->trans_commit();
        return array('ok' => TRUE, 'mensaje' => 'Compra #' . $compra_id . ' registrada. Inventario actualizado. Total: $' . number_format($total_centavos / 100, 0, ',', '.') );
    }

    private function normalizar_decimal($valor)
    {
        $valor = trim((string) $valor);
        if (!preg_match('/^\d{1,10}(?:\.\d{1,2})?$/', $valor)) return FALSE;
        $partes = explode('.', $valor, 2);
        $entero = ltrim($partes[0], '0');
        if ($entero === '') $entero = '0';
        return $entero . '.' . str_pad(isset($partes[1]) ? $partes[1] : '', 2, '0', STR_PAD_RIGHT);
    }

    private function centavos($valor)
    {
        if (!preg_match('/^(\d+)(?:\.(\d{1,2}))?$/', (string) $valor, $partes)) return 0;
        return ((int) $partes[1] * 100) + (int) str_pad(isset($partes[2]) ? $partes[2] : '', 2, '0', STR_PAD_RIGHT);
    }

    private function decimal($centavos)
    {
        $centavos = (int) $centavos;
        return intdiv($centavos, 100) . '.' . str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
    }
}
