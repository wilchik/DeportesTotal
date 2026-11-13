<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Caja extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->verificar_rol(array('administrador', 'caja'));
        $this->load->model('Caja_model');
    }

    public function index()
    {
        $usuario_id = (int) $this->session->userdata('id');
        $data['sesion'] = $this->Caja_model->sesion_abierta($usuario_id);
        $data['resumen'] = $data['sesion'] ? $this->Caja_model->resumen_sesion($data['sesion']->id) : NULL;
        $data['movimientos'] = $data['sesion'] ? $this->Caja_model->movimientos_sesion($data['sesion']->id) : array();
        $data['cierres'] = $this->Caja_model->cierres_usuario($usuario_id);
        $this->load->view('caja/index', $data);
    }

    public function abrir()
    {
        $this->requerir_post();
        $monto = $this->normalizar_dinero($this->input->post('monto_inicial'));
        if ($monto === FALSE) {
            $this->session->set_flashdata('error_persistente', 'Ingresa un monto inicial válido, igual o superior a cero.');
            redirect('caja');
            return;
        }
        $resultado = $this->Caja_model->abrir((int) $this->session->userdata('id'), $monto);
        $this->session->set_flashdata($resultado['ok'] ? 'exito' : 'error_persistente', $resultado['mensaje']);
        redirect('caja');
    }

    public function cerrar()
    {
        $this->requerir_post();
        $monto = $this->normalizar_dinero($this->input->post('efectivo_contado'));
        $observaciones = trim($this->input->post('observaciones', TRUE));
        if ($monto === FALSE || strlen($observaciones) > 500) {
            $this->session->set_flashdata('error_persistente', 'Ingresa un efectivo contado válido y una observación de máximo 500 caracteres.');
            redirect('caja');
            return;
        }
        $resultado = $this->Caja_model->cerrar((int) $this->session->userdata('id'), $monto, $observaciones);
        $this->session->set_flashdata($resultado['ok'] ? 'exito' : 'error_persistente', $resultado['mensaje']);
        redirect('caja');
    }

    public function movimiento()
    {
        $this->requerir_post();
        $tipo = $this->input->post('tipo', TRUE);
        $monto = $this->normalizar_dinero($this->input->post('monto'));
        $descripcion = trim($this->input->post('descripcion', TRUE));
        if (!in_array($tipo, array('entrada', 'salida'), TRUE) || $monto === FALSE || $monto === '0.00' || $descripcion === '' || strlen($descripcion) > 250) {
            $this->session->set_flashdata('error_persistente', 'Indica tipo, monto mayor que cero y una descripción de máximo 250 caracteres.');
            redirect('caja');
            return;
        }
        $resultado = $this->Caja_model->registrar_movimiento((int) $this->session->userdata('id'), $tipo, $monto, $descripcion);
        $this->session->set_flashdata($resultado['ok'] ? 'exito' : 'error_persistente', $resultado['mensaje']);
        redirect('caja');
    }

    private function normalizar_dinero($valor)
    {
        $valor = trim((string) $valor);
        if (!preg_match('/^\d{1,10}(?:\.\d{1,2})?$/', $valor)) return FALSE;
        $partes = explode('.', $valor, 2);
        $entero = ltrim($partes[0], '0');
        if ($entero === '') $entero = '0';
        return $entero . '.' . str_pad(isset($partes[1]) ? $partes[1] : '', 2, '0', STR_PAD_RIGHT);
    }
}
