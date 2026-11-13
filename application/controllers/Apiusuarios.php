<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Apiusuarios extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->verificar_rol(array('propietario'));
        $this->load->model('Usuario_model');
    }

    public function usuarios()
    {
        $usuarios = $this->db
            ->select('id, nombre, usuario, rol, estado')
            ->order_by('id', 'DESC')
            ->get('usuarios')
            ->result();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($usuarios));
    }

    public function usuario($id)
    {
        $usuario = $this->db
            ->select('id, nombre, usuario, rol, estado')
            ->get_where('usuarios', array('id' => $id))
            ->row();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($usuario));
    }
}
