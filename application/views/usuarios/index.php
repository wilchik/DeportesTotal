<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de usuarios | Deportes Total</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
        body { margin: 0; background: #f1f3f5; color: #263238; }
        .topbar { background: #263f59; color: #fff; padding: 16px 28px; display: flex; justify-content: space-between; align-items: center; gap: 15px; border-bottom: 4px solid #1b3045; }
        .brand { margin: 0; font-size: 22px; }
        .top-actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .top-link { display: inline-block; padding: 9px 13px; color: #fff; text-decoration: none; border: 1px solid #71869a; border-radius: 3px; font-size: 13px; font-weight: bold; }
        .top-link:hover { background: #34536f; }
        .top-link.logout { background: #a93636; border-color: #8e2929; }
        .top-link.logout:hover { background: #8e2929; }
        .container { max-width: 1150px; margin: 30px auto; padding: 0 18px; }
        .page-heading { display: flex; justify-content: space-between; align-items: center; gap: 15px; margin-bottom: 20px; }
        h1 { margin: 0; color: #263f59; font-size: 26px; }
        .subtitle { margin: 7px 0 0; color: #65727e; }
        .btn { display: inline-block; padding: 10px 15px; border: 1px solid transparent; border-radius: 3px; text-decoration: none; cursor: pointer; font-size: 14px; font-weight: bold; }
        .btn-primary { background: #315f85; border-color: #264b69; color: #fff; }
        .btn-primary:hover { background: #264b69; }
        .btn-edit { background: #e8eef3; border-color: #b8c6d1; color: #294d6b; }
        .btn-edit:hover { background: #dce6ed; }
        .btn-status { background: #f7edda; border-color: #dfc99b; color: #76551b; }
        .btn-status:hover { background: #f0e2c4; }
        .btn-secondary { background: #e8ecef; border-color: #c5cdd3; color: #394a56; }
        .btn-secondary:hover { background: #dce2e6; }
        .card { background: #fff; border: 1px solid #cbd3da; padding: 22px; }
        .card h2 { margin: 0; color: #263f59; font-size: 19px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 13px 12px; text-align: left; border-bottom: 1px solid #dfe4e8; }
        th { color: #4f6271; background: #edf1f4; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; }
        tbody tr:hover { background: #f8fafb; }
        .badge { display: inline-block; padding: 5px 9px; border: 1px solid transparent; font-size: 12px; font-weight: bold; }
        .propietario { background: #eee9f5; border-color: #d4c7e6; color: #563c79; }
        .administrador { background: #e4edf4; border-color: #c4d5e2; color: #315f85; }
        .caja { background: #e8efe8; border-color: #cbd9cb; color: #3d633d; }
        .activo { color: #356b3a; font-weight: bold; }
        .inactivo { color: #a32e2e; font-weight: bold; }
        .mensaje { padding: 11px 13px; margin-bottom: 15px; border: 1px solid; }
        .exito { background: #edf5ed; border-color: #c8dcc8; color: #356b3a; }
        .error { background: #faeeee; border-color: #e6c6c6; color: #a32e2e; }
        .acciones { display: flex; gap: 7px; flex-wrap: wrap; align-items: center; }
        .acciones form { margin: 0; }
        .actual { color: #65727e; font-size: 12px; font-style: italic; }
        .empty { padding: 20px 10px; color: #65727e; text-align: center; }
        @media(max-width: 750px) {
            .topbar { align-items: flex-start; flex-direction: column; padding: 16px 18px; }
            .page-heading { align-items: flex-start; flex-direction: column; }
            .card { overflow-x: auto; padding: 15px; }
            table { min-width: 700px; }
        }
    </style>
<link rel="stylesheet" href="<?= base_url('assets/css/app-sidebar.css') ?>">
</head>
<body class="app-layout">
<?php $this->load->view('partials/sidebar'); ?>
<header class="topbar">
    <h1 class="brand">Deportes Total</h1>
    <nav class="top-actions">
        <a class="top-link" href="<?= site_url('inicio') ?>">Panel principal</a>
        
    </nav>
</header>

<main class="container">
    <div class="page-heading">
        <div>
            <h1>Gestión de usuarios</h1>
            <p class="subtitle">Administra las cuentas de acceso de Deportes Total.</p>
        </div>
        <a href="<?= site_url('usuarios/nuevo') ?>" class="btn btn-primary">+ Crear usuario</a>
    </div>

    

    

    <section class="card">
        <h2>Usuarios registrados</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($usuarios)): ?>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= (int) $usuario->id ?></td>
                            <td><?= html_escape($usuario->nombre) ?></td>
                            <td><?= html_escape($usuario->usuario) ?></td>
                            <td>
                                <span class="badge <?= html_escape($usuario->rol) ?>">
                                    <?= ucfirst(html_escape($usuario->rol)) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ((int) $usuario->estado === 1): ?>
                                    <span class="activo">Activo</span>
                                <?php else: ?>
                                    <span class="inactivo">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="acciones">
                                    <?php if ((int) $usuario->id !== (int) $this->session->userdata('id')): ?>
                                        <a href="<?= site_url('usuarios/editar/'.$usuario->id) ?>" class="btn btn-edit">Editar</a>
                                        <form method="post"
                                              action="<?= site_url('usuarios/cambiar_estado/'.$usuario->id) ?>"
                                              onsubmit="return confirm('¿Deseas cambiar el estado de este usuario?');">
                                            <input type="hidden"
                                                   name="<?= $this->security->get_csrf_token_name() ?>"
                                                   value="<?= $this->security->get_csrf_hash() ?>">
                                            <button type="submit" class="btn btn-status">
                                                <?= (int) $usuario->estado === 1 ? 'Desactivar' : 'Activar' ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="actual">Tu cuenta actual</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="empty">No hay usuarios registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
