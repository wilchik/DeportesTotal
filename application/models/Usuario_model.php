<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usuario_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // Consultar todos los usuarios
    public function obtener_usuarios()
    {
        return $this->db
                    ->order_by('id', 'DESC')
                    ->get('usuarios')
                    ->result();
    }

    // Consultar un usuario por ID
    public function obtener_usuario($id)
    {
        return $this->db
                    ->get_where('usuarios', array('id' => $id))
                    ->row();
    }

    // Comprobar si existe un nombre de usuario
    public function existe_usuario($usuario)
    {
        return $this->db
                    ->where('usuario', $usuario)
                    ->count_all_results('usuarios') > 0;
    }

    // Registrar usuario
    public function insertar_usuario($datos)
    {
        return $this->db->insert('usuarios', $datos);
    }

    // Actualizar usuario
    public function actualizar_usuario($id, $datos)
    {
        return $this->db
                    ->where('id', $id)
                    ->update('usuarios', $datos);
    }

    // Cambiar estado del usuario
    public function cambiar_estado($id, $estado)
    {
        return $this->db
                    ->where('id', $id)
                    ->update('usuarios', array('estado' => $estado));
    }
}