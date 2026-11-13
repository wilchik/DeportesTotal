<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Compras extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->verificar_rol(array('propietario', 'administrador'));
        $this->load->model('Compra_model');
    }

    public function index()
    {
        $data['compras'] = $this->Compra_model->obtener_compras();
        $this->load->view('compras/index', $data);
    }

    public function nueva()
    {
        $data['proveedores'] = $this->Compra_model->proveedores_activos();
        $data['productos'] = $this->Compra_model->productos_activos();
        $this->load->view('compras/nueva', $data);
    }

    public function guardar()
    {
        $this->requerir_post();
        $proveedor_id = (int) $this->input->post('proveedor_id');
        $items = $this->input->post('items');
        $observaciones = trim($this->input->post('observaciones', TRUE));
        $resultado = $this->Compra_model->registrar_compra(array(
            'proveedor_id' => $proveedor_id,
            'usuario_id' => (int) $this->session->userdata('id'),
            'items' => $items,
            'observaciones' => $observaciones
        ));
        $tipoAviso = $resultado['ok'] ? 'exito' : (!empty($resultado['persistente']) ? 'error_persistente' : 'error');
        $this->session->set_flashdata($tipoAviso, $resultado['mensaje']);
        redirect($resultado['ok'] ? 'compras' : 'compras/nueva');
    }

    public function proveedores()
    {
        $data['proveedores'] = $this->Compra_model->obtener_proveedores();
        $this->load->view('compras/proveedores', $data);
    }

    public function guardar_proveedor()
    {
        $datos = $this->leer_proveedor();
        if ($datos === FALSE) {
            $this->session->set_flashdata('error_persistente', 'El nombre del proveedor es obligatorio. Verifica también el correo.');
            redirect('compras/proveedores');
            return;
        }
        if ($this->Compra_model->telefono_proveedor_existe($datos['telefono'])) {
            $this->session->set_flashdata('error_persistente', 'Ya existe un proveedor con ese teléfono.');
            redirect('compras/proveedores');
            return;
        }
        $ok = $this->Compra_model->insertar_proveedor($datos);
        $this->session->set_flashdata($ok ? 'exito' : 'error', $ok ? 'Proveedor registrado correctamente.' : 'No fue posible registrar el proveedor.');
        redirect('compras/proveedores');
    }

    public function editar_proveedor($id)
    {
        $data['proveedor'] = $this->Compra_model->obtener_proveedor($id);
        if (!$data['proveedor']) { show_404(); return; }
        $this->load->view('compras/editar_proveedor', $data);
    }

    public function actualizar_proveedor($id)
    {
        $proveedor = $this->Compra_model->obtener_proveedor($id);
        if (!$proveedor) { show_404(); return; }
        $datos = $this->leer_proveedor();
        if ($datos === FALSE || $this->Compra_model->telefono_proveedor_existe($datos['telefono'], $id)) {
            $this->session->set_flashdata('error_persistente', 'Revisa los datos del proveedor o su teléfono.');
            redirect('compras/editar_proveedor/' . $id);
            return;
        }
        $ok = $this->Compra_model->actualizar_proveedor($id, $datos);
        $this->session->set_flashdata($ok ? 'exito' : 'error', $ok ? 'Proveedor actualizado.' : 'No fue posible actualizar el proveedor.');
        redirect('compras/proveedores');
    }

    public function cambiar_estado_proveedor($id)
    {
        $this->requerir_post();
        $proveedor = $this->Compra_model->obtener_proveedor($id);
        if (!$proveedor) { show_404(); return; }
        $ok = $this->Compra_model->cambiar_estado_proveedor($id, $proveedor->estado ? 0 : 1);
        $this->session->set_flashdata($ok ? 'exito' : 'error', $ok ? 'Estado del proveedor actualizado.' : 'No fue posible cambiar el estado.');
        redirect('compras/proveedores');
    }

    private function leer_proveedor()
    {
        $nombre = trim($this->input->post('nombre', TRUE));
        $contacto = trim($this->input->post('contacto', TRUE));
        $telefono = trim($this->input->post('telefono', TRUE));
        $correo = trim($this->input->post('correo', TRUE));
        $nit = trim($this->input->post('nit', TRUE));
        if ($nombre === '') return FALSE;
        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) return FALSE;
        return array('nombre' => $nombre, 'contacto' => $contacto, 'telefono' => $telefono, 'correo' => $correo !== '' ? $correo : NULL, 'nit' => $nit);
    }
}
