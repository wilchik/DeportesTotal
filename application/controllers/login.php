<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Login_model');
        $this->load->library('session');
    }

    public function index()
    {
        if ($this->session->userdata('id')) {
            redirect('inicio');
            return;
        }

        $this->load->view('login');
    }

    public function validar()
    {
        $usuario = trim($this->input->post('usuario', TRUE));
        $password = $this->input->post('password');

        $datos = $this->Login_model->buscar_usuario($usuario);

        if ($datos && password_verify($password, $datos->password)) {
            $this->session->sess_regenerate(TRUE);

            $this->session->set_userdata(array(
                'id' => $datos->id,
                'nombre' => $datos->nombre,
                'rol' => $datos->rol
            ));

            redirect('inicio');
            return;
        }

        $this->session->set_flashdata('error_persistente', 'Usuario o contraseña incorrectos');
        redirect('login');
    }

    public function cerrar_sesion()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('El cierre de sesión requiere una solicitud POST.', 405);
            return;
        }

        $this->session->sess_destroy();
        redirect('login');
    }
}
