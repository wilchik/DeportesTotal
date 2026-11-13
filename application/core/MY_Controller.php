<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        // Mantener consistentes los DATETIME/TIMESTAMP de la aplicación con Colombia.
        $this->db->query("SET time_zone = '-05:00'");
    }

    protected function verificar_sesion()
    {
        $id = (int) $this->session->userdata('id');
        if ($id <= 0) {
            redirect('login');
            exit;
        }

        // Si un propietario desactiva una cuenta, el usuario pierde acceso
        // en la siguiente petición, sin necesidad de esperar a que cierre sesión.
        $cuenta = $this->db->select('id, nombre, rol, estado')->get_where('usuarios', array('id' => $id))->row();
        if (!$cuenta || (int) $cuenta->estado !== 1) {
            $this->session->sess_destroy();
            redirect('login');
            exit;
        }

        // Reflejar cambios de nombre o rol hechos por otro propietario.
        $this->session->set_userdata(array('nombre' => $cuenta->nombre, 'rol' => $cuenta->rol));
    }

    protected function verificar_rol($roles)
    {
        $this->verificar_sesion();
        $rol = $this->session->userdata('rol');
        if (!in_array($rol, $roles, TRUE)) {
            show_error('No tienes permisos para acceder a esta sección.', 403);
            exit;
        }
    }

    protected function requerir_post()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Esta operación requiere una solicitud POST.', 405);
            exit;
        }
    }
}
