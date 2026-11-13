
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login_model extends CI_Model {

    public function buscar_usuario($usuario)
    {
        return $this->db
            ->where('usuario', $usuario)
            ->where('estado', 1)
            ->get('usuarios')
            ->row();
    }
}
