<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Clientes extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->verificar_rol(array('propietario', 'administrador', 'caja'));
        $this->load->model('Cliente_model');
    }

    public function index()
    {
        $data['clientes'] = $this->Cliente_model->obtener_todos();
        $data['puede_editar'] = in_array($this->session->userdata('rol'), array('propietario', 'administrador'), TRUE);
        $this->load->view('clientes/index', $data);
    }

    public function buscar_para_venta()
    {
        $this->verificar_rol(array('administrador', 'caja'));
        $termino = trim((string) $this->input->get('q', TRUE));
        $clientes = array();
        if (strlen($termino) >= 2) {
            foreach ($this->Cliente_model->buscar_para_venta($termino, 10) as $cliente) {
                $clientes[] = array(
                    'id' => (int) $cliente->id,
                    'nombre' => $cliente->nombre,
                    'telefono' => $cliente->telefono,
                    'correo' => $cliente->correo,
                    'deporte_interes' => $cliente->deporte_interes
                );
            }
        }
        $this->respuesta_json(array('resultados' => $clientes));
    }

    public function crear_ajax()
    {
        $this->verificar_rol(array('administrador', 'caja'));
        $this->requerir_post();

        $datos = $this->leer_datos();
        if ($datos === FALSE) {
            $this->respuesta_json(array('ok' => FALSE, 'mensaje' => 'Completa el nombre y el teléfono; revisa también el formato del correo.'), 422);
            return;
        }
        if ($this->Cliente_model->telefono_existe($datos['telefono'])) {
            $this->respuesta_json(array('ok' => FALSE, 'mensaje' => 'Ya existe un cliente con ese teléfono. Búscalo y selecciónalo en la venta.'), 409);
            return;
        }
        if (!$this->Cliente_model->insertar($datos)) {
            $this->respuesta_json(array('ok' => FALSE, 'mensaje' => 'No fue posible registrar el cliente.'), 500);
            return;
        }

        $cliente = $this->Cliente_model->obtener_por_id($this->db->insert_id());
        $this->respuesta_json(array(
            'ok' => TRUE,
            'mensaje' => 'Cliente registrado y seleccionado.',
            'cliente' => array(
                'id' => (int) $cliente->id,
                'nombre' => $cliente->nombre,
                'telefono' => $cliente->telefono,
                'correo' => $cliente->correo,
                'deporte_interes' => $cliente->deporte_interes
            )
        ));
    }

    public function historial($id)
    {
        $data['cliente'] = $this->Cliente_model->obtener_por_id($id);
        if (!$data['cliente']) { show_404(); return; }
        $data['historial'] = $this->Cliente_model->obtener_historial($id);
        $this->load->view('clientes/historial', $data);
    }

    public function nuevo()
    {
        $this->load->view('clientes/nuevo');
    }

    public function guardar()
    {
        $datos = $this->leer_datos();
        if ($datos === FALSE) {
            $this->session->set_flashdata('error_persistente', 'El nombre y el teléfono son obligatorios.');
            redirect('clientes/nuevo');
            return;
        }
        if ($this->Cliente_model->telefono_existe($datos['telefono'])) {
            $this->session->set_flashdata('error_persistente', 'Ya existe un cliente registrado con ese teléfono.');
            redirect('clientes/nuevo');
            return;
        }
        if ($this->Cliente_model->insertar($datos)) {
            $this->session->set_flashdata('exito', 'Cliente registrado correctamente.');
        } else {
            $this->session->set_flashdata('error', 'No fue posible registrar el cliente.');
        }
        redirect('clientes');
    }

    public function editar($id)
    {
        $this->verificar_rol(array('propietario', 'administrador'));
        $data['cliente'] = $this->Cliente_model->obtener_por_id($id);
        if (!$data['cliente']) { show_404(); return; }
        $this->load->view('clientes/editar', $data);
    }

    public function actualizar($id)
    {
        $this->verificar_rol(array('propietario', 'administrador'));
        $cliente = $this->Cliente_model->obtener_por_id($id);
        if (!$cliente) { show_404(); return; }
        $datos = $this->leer_datos();
        if ($datos === FALSE || $this->Cliente_model->telefono_existe($datos['telefono'], $id)) {
            $this->session->set_flashdata('error_persistente', 'Revisa los datos o el teléfono, puede estar registrado por otro cliente.');
            redirect('clientes/editar/' . $id);
            return;
        }
        $this->Cliente_model->actualizar($id, $datos);
        $this->session->set_flashdata('exito', 'Cliente actualizado correctamente.');
        redirect('clientes');
    }

    private function leer_datos()
    {
        $nombre = trim($this->input->post('nombre', TRUE));
        $telefono = trim($this->input->post('telefono', TRUE));
        $correo = trim($this->input->post('correo', TRUE));
        $deporte = trim($this->input->post('deporte_interes', TRUE));
        if ($nombre === '' || $telefono === '') return FALSE;
        $permitidos = array('Fútbol', 'Baloncesto', 'Running', 'Gimnasio', 'Ciclismo', 'Voleibol', 'Otro', 'No especificado');
        if (!in_array($deporte, $permitidos, TRUE)) $deporte = 'No especificado';
        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) return FALSE;
        return array('nombre' => $nombre, 'telefono' => $telefono, 'correo' => $correo !== '' ? $correo : NULL, 'deporte_interes' => $deporte);
    }

    private function respuesta_json($datos, $estado = 200)
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
