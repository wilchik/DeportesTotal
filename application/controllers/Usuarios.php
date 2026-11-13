
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usuarios extends MY_Controller
{
    private $roles_permitidos = array(
        'propietario',
        'administrador',
        'caja'
    );

    public function __construct()
    {
        parent::__construct();

        // Solo los propietarios pueden gestionar usuarios
        $this->verificar_rol(array('propietario'));

        $this->load->model('Usuario_model');
        $this->load->library('form_validation');
    }

    // Mostrar listado de usuarios
    public function index()
    {
        $data['usuarios'] = $this->Usuario_model->obtener_usuarios();

        $this->load->view('usuarios/index', $data);
    }

    // Mostrar formulario de creación
    public function nuevo()
    {
        $data['roles'] = $this->roles_permitidos;

        $this->load->view('usuarios/nuevo', $data);
    }

    // Registrar usuario
    public function guardar()
    {
        $this->form_validation->set_rules(
            'nombre', 'Nombre', 'required|trim'
        );

        $this->form_validation->set_rules(
            'usuario', 'Usuario', 'required|trim|min_length[3]|max_length[50]'
        );

        $this->form_validation->set_rules(
            'password', 'Contraseña', 'required|min_length[6]'
        );

        $this->form_validation->set_rules(
            'confirmar_password',
            'Confirmar contraseña',
            'required|matches[password]'
        );

        $this->form_validation->set_rules(
            'rol', 'Rol', 'required'
        );

        if ($this->form_validation->run() == FALSE) {
            $this->nuevo();
            return;
        }

        $usuario = trim($this->input->post('usuario'));
        $rol = $this->input->post('rol');

        if ($this->Usuario_model->existe_usuario($usuario)) {
            $this->session->set_flashdata(
                'error_persistente',
                'El nombre de usuario ya está registrado.'
            );

            redirect('usuarios/nuevo');
            return;
        }

        if (!in_array($rol, $this->roles_permitidos, true)) {
            show_error('Rol no permitido.', 403);
            return;
        }

        $datos = array(
            'nombre' => trim($this->input->post('nombre')),
            'usuario' => $usuario,
            'password' => password_hash(
                $this->input->post('password'),
                PASSWORD_DEFAULT
            ),
            'rol' => $rol,
            'estado' => 1
        );

        if ($this->Usuario_model->insertar_usuario($datos)) {
            $this->session->set_flashdata(
                'exito',
                'Usuario creado correctamente.'
            );
        } else {
            $this->session->set_flashdata(
                'error',
                'No fue posible crear el usuario.'
            );
        }

        redirect('usuarios');
    }

    // Mostrar formulario de edición
    public function editar($id)
    {
        $usuario = $this->Usuario_model->obtener_usuario($id);

        if (!$usuario) {
            show_404();
            return;
        }

        // Impedir que el propietario edite su propia cuenta
        if ($usuario->id == $this->session->userdata('id')) {
            show_error('No puedes editar tu propia cuenta desde este módulo.', 403);
            return;
        }

        $data['usuario'] = $usuario;
        $data['roles'] = $this->roles_permitidos;

        $this->load->view('usuarios/editar', $data);
    }

    // Actualizar usuario
    public function actualizar($id)
    {
        $usuario = $this->Usuario_model->obtener_usuario($id);

        if (!$usuario) {
            show_404();
            return;
        }

        // Impedir modificar la cuenta de la sesión actual
        if ($usuario->id == $this->session->userdata('id')) {
            show_error('No puedes modificar tu propia cuenta desde este módulo.', 403);
            return;
        }

        $nombre = trim($this->input->post('nombre'));
        $rol = $this->input->post('rol');

        if (
            $nombre === '' ||
            !in_array($rol, $this->roles_permitidos, true)
        ) {
            show_error('Datos inválidos.', 400);
            return;
        }

        $datos = array(
            'nombre' => $nombre,
            'rol' => $rol
        );

        $nueva_password = $this->input->post('password');

        if (!empty($nueva_password)) {

            if (strlen($nueva_password) < 6) {
                show_error(
                    'La contraseña debe tener mínimo 6 caracteres.',
                    400
                );
                return;
            }

            $datos['password'] = password_hash(
                $nueva_password,
                PASSWORD_DEFAULT
            );
        }

        if ($this->Usuario_model->actualizar_usuario($id, $datos)) {
            $this->session->set_flashdata(
                'exito',
                'Usuario actualizado correctamente.'
            );
        } else {
            $this->session->set_flashdata(
                'error',
                'No fue posible actualizar el usuario.'
            );
        }

        redirect('usuarios');
    }

    // Activar o desactivar usuario
    public function cambiar_estado($id)
    {
        $this->requerir_post();
        $usuario = $this->Usuario_model->obtener_usuario($id);

        if (!$usuario) {
            show_404();
            return;
        }

        // No permitir modificar el estado de la sesión actual
        if ($usuario->id == $this->session->userdata('id')) {
            show_error('No puedes modificar el estado de tu propia cuenta.', 403);
            return;
        }

        $nuevo_estado = ($usuario->estado == 1) ? 0 : 1;

        if ($this->Usuario_model->cambiar_estado($id, $nuevo_estado)) {
            $this->session->set_flashdata(
                'exito',
                'Estado del usuario actualizado.'
            );
        } else {
            $this->session->set_flashdata(
                'error',
                'No fue posible cambiar el estado.'
            );
        }

        redirect('usuarios');
    }
}

