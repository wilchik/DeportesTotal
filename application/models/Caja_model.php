<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Caja_model extends CI_Model
{
    public function sesion_abierta($usuario_id)
    {
        return $this->db->get_where('caja_sesiones', array('usuario_id' => (int) $usuario_id, 'estado' => 'abierta'))->row();
    }

    public function abrir($usuario_id, $monto_inicial)
    {
        $this->db->trans_begin();
        $usuario = $this->db->query("SELECT id FROM usuarios WHERE id=? AND estado=1 AND rol IN ('administrador','caja') FOR UPDATE", array((int) $usuario_id))->row();
        if (!$usuario || $this->sesion_abierta($usuario_id)) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'mensaje' => 'Ya existe una caja abierta o el usuario no está activo.');
        }
        $this->db->insert('caja_sesiones', array(
            'usuario_id' => (int) $usuario_id,
            'fecha_apertura' => date('Y-m-d H:i:s'),
            'monto_inicial' => $monto_inicial,
            'estado' => 'abierta'
        ));
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'mensaje' => 'No fue posible abrir la caja.');
        }
        $this->db->trans_commit();
        return array('ok' => TRUE, 'mensaje' => 'Caja abierta correctamente.');
    }

    public function resumen_sesion($sesion_id)
    {
        $resumen = $this->db->query("SELECT
            COALESCE(SUM(total),0) AS total_ventas,
            COALESCE(SUM(CASE WHEN metodo_pago='Efectivo' THEN total ELSE 0 END),0) AS total_efectivo,
            COALESCE(SUM(CASE WHEN metodo_pago='Transferencia' THEN total ELSE 0 END),0) AS total_transferencia,
            COALESCE(SUM(CASE WHEN metodo_pago='Tarjeta' THEN total ELSE 0 END),0) AS total_tarjeta,
            COALESCE(SUM(CASE WHEN metodo_pago NOT IN ('Efectivo','Transferencia','Tarjeta') THEN total ELSE 0 END),0) AS total_otros,
            COUNT(DISTINCT COALESCE(NULLIF(codigo_venta,''), CONCAT('LEGACY-',id))) AS operaciones,
            COALESCE(SUM(CASE WHEN canal='Tienda' THEN total ELSE 0 END),0) AS canal_tienda,
            COALESCE(SUM(CASE WHEN canal='WhatsApp' THEN total ELSE 0 END),0) AS canal_whatsapp,
            COALESCE(SUM(CASE WHEN canal='Instagram' THEN total ELSE 0 END),0) AS canal_instagram,
            (SELECT COALESCE(SUM(monto),0) FROM movimientos_caja WHERE caja_sesion_id=? AND tipo='entrada') AS efectivo_entradas,
            (SELECT COALESCE(SUM(monto),0) FROM movimientos_caja WHERE caja_sesion_id=? AND tipo='salida') AS efectivo_salidas
            FROM ventas WHERE caja_sesion_id=?", array((int) $sesion_id, (int) $sesion_id, (int) $sesion_id))->row_array();
        $sesion = $this->db->select('monto_inicial')->get_where('caja_sesiones', array('id' => (int) $sesion_id))->row();
        $resumen['efectivo_esperado'] = $this->decimal(
            $this->centavos($sesion ? $sesion->monto_inicial : 0)
            + $this->centavos($resumen['total_efectivo'])
            + $this->centavos($resumen['efectivo_entradas'])
            - $this->centavos($resumen['efectivo_salidas'])
        );
        return $resumen;
    }

    public function registrar_movimiento($usuario_id, $tipo, $monto, $descripcion)
    {
        if (!in_array($tipo, array('entrada', 'salida'), TRUE) || $this->centavos($monto) <= 0 || trim($descripcion) === '') {
            return array('ok' => FALSE, 'mensaje' => 'El movimiento de efectivo no tiene datos válidos.');
        }
        $this->db->trans_begin();
        $sesion = $this->db->query("SELECT id,monto_inicial FROM caja_sesiones WHERE usuario_id=? AND estado='abierta' FOR UPDATE", array((int) $usuario_id))->row();
        if (!$sesion) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'mensaje' => 'Abre una caja antes de registrar movimientos de efectivo.');
        }
        if ($tipo === 'salida') {
            $resumen = $this->resumen_sesion($sesion->id);
            $disponible = $this->centavos($sesion->monto_inicial) + $this->centavos($resumen['total_efectivo'])
                + $this->centavos($resumen['efectivo_entradas']) - $this->centavos($resumen['efectivo_salidas']);
            if ($this->centavos($monto) > $disponible) {
                $this->db->trans_rollback();
                return array('ok' => FALSE, 'mensaje' => 'La salida supera el efectivo disponible en la caja.');
            }
        }
        $this->db->insert('movimientos_caja', array(
            'caja_sesion_id' => (int) $sesion->id,
            'usuario_id' => (int) $usuario_id,
            'tipo' => $tipo,
            'monto' => $monto,
            'descripcion' => $descripcion,
            'fecha' => date('Y-m-d H:i:s')
        ));
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'mensaje' => 'No fue posible registrar el movimiento de efectivo.');
        }
        $this->db->trans_commit();
        return array('ok' => TRUE, 'mensaje' => 'Movimiento de efectivo registrado.');
    }

    public function movimientos_sesion($sesion_id)
    {
        return $this->db->where('caja_sesion_id', (int) $sesion_id)->order_by('fecha', 'DESC')->limit(50)->get('movimientos_caja')->result();
    }

    public function cerrar($usuario_id, $efectivo_contado, $observaciones)
    {
        $this->db->trans_begin();
        $sesion = $this->db->query("SELECT * FROM caja_sesiones WHERE usuario_id=? AND estado='abierta' FOR UPDATE", array((int) $usuario_id))->row();
        if (!$sesion) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'mensaje' => 'No tienes una caja abierta para cerrar.');
        }
        $resumen = $this->resumen_sesion($sesion->id);
        $esperadoCentavos = $this->centavos($sesion->monto_inicial) + $this->centavos($resumen['total_efectivo'])
            + $this->centavos($resumen['efectivo_entradas']) - $this->centavos($resumen['efectivo_salidas']);
        $contadoCentavos = $this->centavos($efectivo_contado);
        $diferenciaCentavos = $contadoCentavos - $esperadoCentavos;

        $this->db->insert('cierres_caja', array(
            'caja_sesion_id' => (int) $sesion->id,
            'usuario_cierre_id' => (int) $usuario_id,
            'fecha_cierre' => date('Y-m-d H:i:s'),
            'operaciones' => (int) $resumen['operaciones'],
            'total_ventas' => $resumen['total_ventas'],
            'total_efectivo' => $resumen['total_efectivo'],
            'total_transferencia' => $resumen['total_transferencia'],
            'total_tarjeta' => $resumen['total_tarjeta'],
            'total_otros' => $resumen['total_otros'],
            'canal_tienda' => $resumen['canal_tienda'],
            'canal_whatsapp' => $resumen['canal_whatsapp'],
            'canal_instagram' => $resumen['canal_instagram'],
            'efectivo_esperado' => $this->decimal($esperadoCentavos),
            'efectivo_contado' => $this->decimal($contadoCentavos),
            'diferencia' => $this->decimal($diferenciaCentavos),
            'observaciones' => $observaciones !== '' ? $observaciones : NULL
        ));
        $this->db->where('id', (int) $sesion->id)->where('estado', 'abierta')->update('caja_sesiones', array('estado' => 'cerrada'));
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'mensaje' => 'No fue posible cerrar la caja.');
        }
        $this->db->trans_commit();
        return array('ok' => TRUE, 'mensaje' => $this->mensaje_diferencia($diferenciaCentavos));
    }

    public function cierres_usuario($usuario_id, $limite = 20)
    {
        return $this->db->select('cc.*, cs.fecha_apertura, cs.monto_inicial, u.nombre AS responsable')
            ->from('cierres_caja cc')->join('caja_sesiones cs', 'cs.id=cc.caja_sesion_id')
            ->join('usuarios u', 'u.id=cc.usuario_cierre_id')->where('cc.usuario_cierre_id', (int) $usuario_id)
            ->order_by('cc.fecha_cierre', 'DESC')->limit((int) $limite)->get()->result();
    }

    public function cierres_propietario($desde, $hasta, $usuario_id = NULL)
    {
        $this->db->select('cc.*, cs.fecha_apertura, cs.monto_inicial, u.nombre AS responsable')
            ->from('cierres_caja cc')->join('caja_sesiones cs', 'cs.id=cc.caja_sesion_id')
            ->join('usuarios u', 'u.id=cc.usuario_cierre_id');
        if ($desde) $this->db->where('cc.fecha_cierre >=', $desde . ' 00:00:00');
        if ($hasta) $this->db->where('cc.fecha_cierre <', date('Y-m-d', strtotime($hasta . ' +1 day')) . ' 00:00:00');
        if ($usuario_id) $this->db->where('cc.usuario_cierre_id', (int) $usuario_id);
        return $this->db->order_by('cc.fecha_cierre', 'DESC')->limit(200)->get()->result();
    }

    public function movimientos_propietario($desde, $hasta, $usuario_id = NULL)
    {
        $this->db->select('mc.*,u.nombre AS responsable,cs.fecha_apertura')
            ->from('movimientos_caja mc')->join('caja_sesiones cs','cs.id=mc.caja_sesion_id')
            ->join('usuarios u','u.id=mc.usuario_id');
        if ($desde) $this->db->where('mc.fecha >=', $desde . ' 00:00:00');
        if ($hasta) $this->db->where('mc.fecha <', date('Y-m-d', strtotime($hasta . ' +1 day')) . ' 00:00:00');
        if ($usuario_id) $this->db->where('mc.usuario_id', (int) $usuario_id);
        return $this->db->order_by('mc.fecha','DESC')->limit(300)->get()->result();
    }

    public function indicadores_cierres($cierres)
    {
        $totales = array('total_ventas' => 0, 'total_efectivo' => 0, 'total_transferencia' => 0, 'total_tarjeta' => 0, 'cierres' => count($cierres), 'faltantes' => 0, 'sobrantes' => 0);
        foreach ($cierres as $cierre) {
            foreach (array('total_ventas', 'total_efectivo', 'total_transferencia', 'total_tarjeta') as $campo) {
                $totales[$campo] += $this->centavos($cierre->$campo);
            }
            $diferencia = $this->centavos($cierre->diferencia);
            if ($diferencia < 0) $totales['faltantes'] += abs($diferencia);
            if ($diferencia > 0) $totales['sobrantes'] += $diferencia;
        }
        foreach ($totales as $campo => $valor) if ($campo !== 'cierres') $totales[$campo] = $this->decimal($valor);
        return $totales;
    }

    private function centavos($valor)
    {
        $valor = (string) $valor;
        if (!preg_match('/^(-?)(\d+)(?:\.(\d{1,2}))?$/', $valor, $partes)) return 0;
        $centavos = ((int) $partes[2] * 100) + (int) str_pad(isset($partes[3]) ? $partes[3] : '', 2, '0', STR_PAD_RIGHT);
        return isset($partes[1]) && $partes[1] === '-' ? -$centavos : $centavos;
    }

    private function decimal($centavos)
    {
        $centavos = (int) $centavos;
        $absolutos = abs($centavos);
        return ($centavos < 0 ? '-' : '') . intdiv($absolutos, 100) . '.' . str_pad((string) ($absolutos % 100), 2, '0', STR_PAD_LEFT);
    }

    private function mensaje_diferencia($centavos)
    {
        if ($centavos === 0) return 'Caja cerrada y cuadrada.';
        return $centavos > 0 ? 'Caja cerrada con sobrante de $' . number_format($centavos / 100, 2, ',', '.') . '.' : 'Caja cerrada con faltante de $' . number_format(abs($centavos) / 100, 2, ',', '.') . '.';
    }
}
