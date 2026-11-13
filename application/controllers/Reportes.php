<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reportes extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->verificar_rol(array('propietario', 'administrador'));
        $this->load->model('Reporte_model');
    }

    public function index()
    {
        $desde = $this->input->get('desde', TRUE);
        $hasta = $this->input->get('hasta', TRUE);
        if (!$this->fecha_valida($desde)) $desde = date('Y-m-01');
        if (!$this->fecha_valida($hasta)) $hasta = date('Y-m-d');
        if ($desde > $hasta) {
            $temporal = $desde;
            $desde = $hasta;
            $hasta = $temporal;
        }

        $data['desde'] = $desde;
        $data['hasta'] = $hasta;
        $data['resumen'] = $this->Reporte_model->resumen($desde, $hasta);
        $data['canales'] = $this->Reporte_model->por_canal($desde, $hasta);
        $data['productos'] = $this->Reporte_model->productos_mas_vendidos($desde, $hasta);
        $data['vendedores'] = $this->Reporte_model->por_vendedor($desde, $hasta);
        $data['clientes'] = $this->Reporte_model->clientes_frecuentes($desde, $hasta);
        $data['es_propietario'] = $this->session->userdata('rol') === 'propietario';
        if ($data['es_propietario']) {
            $this->load->model('Caja_model');
            $data['ganancias'] = $this->Reporte_model->resumen_ganancias($desde, $hasta);
            $data['ganancias_dia'] = $this->Reporte_model->ganancias_por_dia($desde, $hasta);
            $data['ganancias_canal'] = $this->Reporte_model->ganancias_por_canal($desde, $hasta);
            $data['ganancias_producto'] = $this->Reporte_model->ganancias_por_producto($desde, $hasta);
            $data['valor_inventario'] = $this->Reporte_model->valor_inventario();
            $usuario_id = $this->input->get('usuario_id', TRUE);
            $usuario_id = ctype_digit((string) $usuario_id) && (int) $usuario_id > 0 ? (int) $usuario_id : NULL;
            $data['usuario_filtro'] = $usuario_id;
            $data['usuarios_caja'] = $this->Reporte_model->usuarios_caja();
            $data['cierres'] = $this->Caja_model->cierres_propietario($desde, $hasta, $usuario_id);
            $data['movimientos_caja'] = $this->Caja_model->movimientos_propietario($desde, $hasta, $usuario_id);
            $data['indicadores_cierres'] = $this->Caja_model->indicadores_cierres($data['cierres']);
        }
        $this->load->view('reportes/index', $data);
    }

    private function fecha_valida($fecha)
    {
        if (!is_string($fecha) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) return FALSE;
        $partes = explode('-', $fecha);
        return checkdate((int) $partes[1], (int) $partes[2], (int) $partes[0]);
    }
}
