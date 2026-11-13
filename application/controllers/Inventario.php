<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventario extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->verificar_rol(array('propietario', 'administrador'));
        $this->load->model('Inventario_model');
    }

    public function index()
    {
        $data['productos'] = $this->Inventario_model->obtener_productos();
        $data['movimientos'] = $this->Inventario_model->obtener_movimientos();
        $this->load->view('inventario/index', $data);
    }

    public function ajustar()
    {
        $producto_id = (int) $this->input->post('producto_id');
        $tipo = $this->input->post('tipo', TRUE);
        $cantidad = (int) $this->input->post('cantidad');
        $motivo = trim($this->input->post('motivo', TRUE));
        if ($producto_id <= 0 || $cantidad <= 0 || $motivo === '' || !in_array($tipo, array('entrada', 'salida'), TRUE)) {
            $this->session->set_flashdata('error_persistente', 'Completa todos los datos del movimiento.');
            redirect('inventario');
            return;
        }
        $resultado = $this->Inventario_model->ajustar_stock($producto_id, $tipo, $cantidad, $motivo, (int) $this->session->userdata('id'));
        $tipoAviso = $resultado['ok'] ? 'exito' : (!empty($resultado['persistente']) ? 'error_persistente' : 'error');
        $this->session->set_flashdata($tipoAviso, $resultado['mensaje']);
        redirect('inventario');
    }
}
