<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Producto_model extends CI_Model
{
    public function buscar_para_venta($campo, $termino, $limite = 12)
    {
        $columna = $campo === 'referencia' ? 'referencia' : 'nombre';
        return $this->db
            ->select('id, nombre, referencia, talla, precio, stock')
            ->where('estado', 1)
            ->where('stock >', 0)
            ->like($columna, $termino, 'after')
            ->order_by('nombre', 'ASC')
            ->order_by('talla', 'ASC')
            ->limit((int) $limite)
            ->get('productos')
            ->result();
    }

    public function obtener_todos($solo_activos = FALSE)
    {
        // La proyeccion del modelo evita revelar el costo incluso si otro controlador
        // consulta el catalogo sin ocultarlo posteriormente en la vista.
        $columnas = $this->puede_ver_costos()
            ? '*'
            : 'id, nombre, referencia, talla, precio, stock, estado';
        if ($solo_activos || !$this->puede_ver_costos()) $this->db->where('estado', 1);
        return $this->db->select($columnas, FALSE)->order_by('nombre', 'ASC')->order_by('talla', 'ASC')->get('productos')->result();
    }

    public function obtener_por_id($id)
    {
        $columnas = $this->puede_ver_costos() ? '*' : 'id, nombre, referencia, talla, precio, stock, estado';
        return $this->db->select($columnas, FALSE)->get_where('productos', array('id' => $id))->row();
    }

    public function insertar($datos)
    {
        if (!$this->puede_administrar_costos()) return FALSE;
        return $this->db->insert('productos', $datos);
    }

    public function actualizar($id, $datos)
    {
        if (!$this->puede_administrar_costos()) return FALSE;
        return $this->db->where('id', $id)->update('productos', $datos);
    }

    public function cambiar_estado($id, $estado)
    {
        if (!$this->puede_administrar_costos()) return FALSE;
        return $this->db->where('id', $id)->update('productos', array('estado' => $estado));
    }

    public function referencia_talla_existe($referencia, $talla, $excluir = NULL)
    {
        $this->db->where('referencia', $referencia)->where('talla', $talla);
        if ($excluir !== NULL) $this->db->where('id !=', $excluir);
        return $this->db->count_all_results('productos') > 0;
    }

    private function puede_ver_costos()
    {
        $CI =& get_instance();
        return isset($CI->session) && in_array($CI->session->userdata('rol'), array('propietario', 'administrador'), TRUE);
    }

    private function puede_administrar_costos()
    {
        return $this->puede_ver_costos();
    }
}
