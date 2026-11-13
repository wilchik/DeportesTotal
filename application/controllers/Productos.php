<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Productos extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->verificar_rol(array('propietario', 'administrador', 'caja'));
        $this->load->model('Producto_model');
    }

    public function index()
    {
        $es_caja = $this->session->userdata('rol') === 'caja';
        $data['productos'] = $this->Producto_model->obtener_todos($es_caja);
        $data['puede_administrar'] = in_array($this->session->userdata('rol'), array('propietario', 'administrador'), TRUE);
        $this->load->view('productos/index', $data);
    }

    public function buscar_para_venta()
    {
        $this->verificar_rol(array('administrador', 'caja'));
        $termino = trim((string) $this->input->get('q', TRUE));
        $campo = $this->input->get('campo', TRUE);
        if (!in_array($campo, array('nombre', 'referencia'), TRUE)) $campo = 'nombre';

        $productos = array();
        if (strlen($termino) >= 2) {
            foreach ($this->Producto_model->buscar_para_venta($campo, $termino, 12) as $producto) {
                $productos[] = array(
                    'id' => (int) $producto->id,
                    'nombre' => $producto->nombre,
                    'referencia' => $producto->referencia,
                    'talla' => $producto->talla,
                    'precio' => (string) $producto->precio,
                    'stock' => (int) $producto->stock
                );
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('resultados' => $productos)));
    }

    public function nuevo()
    {
        $this->verificar_rol(array('propietario', 'administrador'));
        $this->load->view('productos/nuevo');
    }

    public function guardar()
    {
        $this->verificar_rol(array('propietario', 'administrador'));
        $this->requerir_post();
        $datos = $this->leer_datos_producto();
        if ($datos === FALSE) {
            $this->session->set_flashdata('error_persistente', 'Completa todos los campos. El precio debe ser mayor que cero y el stock no puede ser negativo.');
            redirect('productos/nuevo');
            return;
        }
        if ($this->Producto_model->referencia_talla_existe($datos['referencia'], $datos['talla'])) {
            $this->session->set_flashdata('error_persistente', 'Ya existe un producto con esa referencia y talla.');
            redirect('productos/nuevo');
            return;
        }
        $datos['estado'] = 1;
        $this->db->trans_begin();
        $this->Producto_model->insertar($datos);
        $id = (int) $this->db->insert_id();
        if ($id > 0 && $datos['stock'] > 0) {
            $this->db->insert('movimientos_inventario', array(
                'producto_id' => $id,
                'usuario_id' => (int) $this->session->userdata('id'),
                'tipo' => 'entrada',
                'cantidad' => $datos['stock'],
                'motivo' => 'Existencia inicial del producto',
                'fecha' => date('Y-m-d H:i:s')
            ));
        }
        if ($this->db->trans_status() === FALSE || $id <= 0) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'No fue posible registrar el producto.');
        } else {
            $this->db->trans_commit();
            $this->session->set_flashdata('exito', 'Producto registrado correctamente.');
        }
        redirect('productos');
    }

    public function editar($id)
    {
        $this->verificar_rol(array('propietario', 'administrador'));
        $data['producto'] = $this->Producto_model->obtener_por_id($id);
        if (!$data['producto']) { show_404(); return; }
        $this->load->view('productos/editar', $data);
    }

    public function actualizar($id)
    {
        $this->verificar_rol(array('propietario', 'administrador'));
        $this->requerir_post();
        $producto = $this->Producto_model->obtener_por_id($id);
        if (!$producto) { show_404(); return; }
        $datos = $this->leer_datos_producto();
        if ($datos === FALSE) {
            $this->session->set_flashdata('error_persistente', 'Revisa los datos ingresados.');
            redirect('productos/editar/' . $id);
            return;
        }
        if ($this->Producto_model->referencia_talla_existe($datos['referencia'], $datos['talla'], $id)) {
            $this->session->set_flashdata('error_persistente', 'Ya existe otro producto con esa referencia y talla.');
            redirect('productos/editar/' . $id);
            return;
        }

        $stock_nuevo = (int) $datos['stock'];
        unset($datos['stock']);
        $this->db->trans_begin();
        $actual = $this->db->query('SELECT stock FROM productos WHERE id=? FOR UPDATE', array($id))->row();
        if (!$actual) {
            $this->db->trans_rollback();
            show_404();
            return;
        }
        $diferencia = $stock_nuevo - (int) $actual->stock;
        $this->Producto_model->actualizar($id, $datos);
        $this->db->where('id', $id)->update('productos', array('stock' => $stock_nuevo));
        if ($diferencia !== 0) {
            $this->db->insert('movimientos_inventario', array(
                'producto_id' => $id,
                'usuario_id' => (int) $this->session->userdata('id'),
                'tipo' => $diferencia > 0 ? 'entrada' : 'salida',
                'cantidad' => abs($diferencia),
                'motivo' => 'Ajuste realizado desde la edición del producto',
                'fecha' => date('Y-m-d H:i:s')
            ));
        }
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'No fue posible actualizar el producto.');
        } else {
            $this->db->trans_commit();
            $this->session->set_flashdata('exito', 'Producto actualizado correctamente.');
        }
        redirect('productos');
    }

    public function cambiar_estado($id)
    {
        $this->verificar_rol(array('propietario', 'administrador'));
        $this->requerir_post();
        $producto = $this->Producto_model->obtener_por_id($id);
        if (!$producto) { show_404(); return; }
        $estado = $producto->estado ? 0 : 1;
        $this->Producto_model->cambiar_estado($id, $estado);
        $this->session->set_flashdata('exito', 'Estado del producto actualizado.');
        redirect('productos');
    }

    private function leer_datos_producto()
    {
        $nombre = trim($this->input->post('nombre', TRUE));
        $referencia = strtoupper(trim($this->input->post('referencia', TRUE)));
        $talla = strtoupper(trim($this->input->post('talla', TRUE)));
        $precio = $this->normalizar_decimal($this->input->post('precio'), 8);
        $costo = $this->normalizar_decimal($this->input->post('costo_adquisicion'), 10);
        $stock = $this->input->post('stock');

        if ($nombre === '' || $referencia === '' || $talla === '' || $precio === FALSE || $precio === '0.00' || $costo === FALSE || filter_var($stock, FILTER_VALIDATE_INT) === FALSE || (int) $stock < 0) {
            return FALSE;
        }
        return array('nombre' => $nombre, 'referencia' => $referencia, 'talla' => $talla, 'precio' => $precio, 'costo_adquisicion' => $costo, 'stock' => (int) $stock);
    }

    private function normalizar_decimal($valor, $digitos_enteros)
    {
        $valor = trim((string) $valor);
        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $valor)) return FALSE;
        $partes = explode('.', $valor, 2);
        $entero = ltrim($partes[0], '0');
        if ($entero === '') $entero = '0';
        if (strlen($entero) > $digitos_enteros) return FALSE;
        $decimal = isset($partes[1]) ? str_pad($partes[1], 2, '0', STR_PAD_RIGHT) : '00';
        return $entero . '.' . $decimal;
    }
}
