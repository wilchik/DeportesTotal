<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ventas extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->verificar_rol(array('administrador', 'caja'));
        $this->load->model('Venta_model');
        $this->load->model('Caja_model');
    }

    public function index()
    {
        redirect('ventas/registrar');
    }

    public function registrar()
    {
        if (!$this->Caja_model->sesion_abierta((int) $this->session->userdata('id'))) {
            $this->session->set_flashdata('error_persistente', 'Abre una caja para iniciar una jornada de ventas.');
            redirect('caja');
            return;
        }
        $data['rol'] = $this->session->userdata('rol');
        $data['sesion_caja'] = $this->Caja_model->sesion_abierta((int) $this->session->userdata('id'));
        $this->load->view('ventas/registrar', $data);
    }

    public function historial()
    {
        $es_caja = $this->session->userdata('rol') === 'caja';
        $data['ventas'] = $this->Venta_model->historial_ventas((int) $this->session->userdata('id'), $es_caja);
        $data['es_caja'] = $es_caja;
        $this->load->view('ventas/historial', $data);
    }

    public function guardar()
    {
        $this->requerir_post();
        $ajax = $this->input->is_ajax_request();
        $canal = $this->input->post('canal', TRUE);
        $metodos = array('Efectivo', 'Transferencia', 'Tarjeta');
        $metodo_pago = $this->input->post('metodo_pago', TRUE);

        if (!in_array($canal, array('Tienda', 'WhatsApp', 'Instagram'), TRUE) || !in_array($metodo_pago, $metodos, TRUE)) {
            $mensaje = 'Selecciona un canal y método de pago válidos.';
            if ($ajax) { $this->responder_json(array('ok' => FALSE, 'mensaje' => $mensaje, 'persistente' => TRUE), 422); return; }
            $this->session->set_flashdata('error_persistente', $mensaje);
            redirect('ventas/registrar');
            return;
        }

        $nuevo_cliente = array(
            'nombre' => trim($this->input->post('nuevo_nombre', TRUE)),
            'telefono' => trim($this->input->post('nuevo_telefono', TRUE)),
            'correo' => trim($this->input->post('nuevo_correo', TRUE)),
            'deporte_interes' => trim($this->input->post('nuevo_deporte', TRUE))
        );
        if ($nuevo_cliente['correo'] !== '' && !filter_var($nuevo_cliente['correo'], FILTER_VALIDATE_EMAIL)) {
            $mensaje = 'El correo del cliente no tiene un formato válido.';
            if ($ajax) { $this->responder_json(array('ok' => FALSE, 'mensaje' => $mensaje, 'persistente' => TRUE), 422); return; }
            $this->session->set_flashdata('error_persistente', $mensaje);
            redirect('ventas/registrar');
            return;
        }

        $resultado = $this->Venta_model->registrar_venta(array(
            'items' => $this->input->post('items'),
            'cliente_id' => (int) $this->input->post('cliente_id'),
            'nuevo_cliente' => $nuevo_cliente,
            'usuario_id' => (int) $this->session->userdata('id'),
            'canal' => $canal,
            'metodo_pago' => $metodo_pago
        ));

        if (!$resultado['ok']) {
            if ($ajax) {
                $persistente = !empty($resultado['persistente']);
                $this->responder_json(array('ok' => FALSE, 'mensaje' => $resultado['mensaje'], 'persistente' => $persistente), $persistente ? 422 : 500);
                return;
            }
            $tipoAviso = !empty($resultado['persistente']) ? 'error_persistente' : 'error';
            $this->session->set_flashdata($tipoAviso, $resultado['mensaje']);
        } elseif ($ajax) {
            $this->responder_json(array(
                'ok' => TRUE,
                'codigo' => $resultado['codigo'],
                'total' => $resultado['total'],
                'mensaje' => 'Venta ' . $resultado['codigo'] . ' registrada correctamente.'
            ));
            return;
        } else {
            $this->session->set_flashdata('exito', 'Venta ' . $resultado['codigo'] . ' registrada. Total: $' . number_format($resultado['total'], 0, ',', '.'));
        }
        redirect('ventas/registrar');
    }

    private function responder_json($datos, $estado = 200)
    {
        $datos['csrf'] = array(
            'name' => $this->security->get_csrf_token_name(),
            'hash' => $this->security->get_csrf_hash()
        );
        $this->output
            ->set_status_header($estado)
            ->set_content_type('application/json')
            ->set_output(json_encode($datos));
    }
}
