<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inicio extends MY_Controller
{
    public function index()
    {
        $this->verificar_sesion();
        $rol = $this->session->userdata('rol');

        // El propietario inicia en el panel de análisis; administrador y caja en ventas.
        if ($rol === 'administrador' || $rol === 'caja') {
            redirect('ventas/registrar');
            return;
        }

        if ($rol !== 'propietario') {
            show_error('El rol de esta cuenta no tiene acceso al sistema.', 403);
            return;
        }

        $this->load->model('Inicio_model');
        $data['resumen'] = $this->Inicio_model->resumen_general();
        $this->load->view('inicio', $data);
    }
}
