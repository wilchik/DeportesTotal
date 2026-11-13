<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear usuario | Deportes Total</title>
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
        .container { max-width: 760px; margin: 30px auto; padding: 0 18px; }
        .card { background: #fff; border: 1px solid #cbd3da; padding: 25px; }
        h1 { margin: 0; color: #263f59; font-size: 25px; }
        .subtitle { color: #65727e; margin: 8px 0 22px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 7px; font-weight: bold; color: #394a56; }
        input, select { width: 100%; padding: 11px; border: 1px solid #c3ccd3; border-radius: 2px; background: #fff; color: #263238; font-size: 15px; }
        input:focus, select:focus { outline: 2px solid #c3d4e1; border-color: #315f85; }
        .buttons { display: flex; flex-wrap: wrap; gap: 9px; margin-top: 23px; }
        .btn { display: inline-block; padding: 11px 16px; border: 1px solid transparent; border-radius: 3px; cursor: pointer; font-size: 14px; font-weight: bold; text-decoration: none; }
        .btn-primary { background: #315f85; border-color: #264b69; color: #fff; }
        .btn-primary:hover { background: #264b69; }
        .btn-secondary { background: #e8ecef; border-color: #c5cdd3; color: #394a56; }
        .btn-secondary:hover { background: #dce2e6; }
        .error { color: #a32e2e; margin-bottom: 12px; }
        .mensaje { padding: 11px 13px; margin-bottom: 15px; background: #faeeee; border: 1px solid #e6c6c6; color: #a32e2e; }
        @media(max-width: 600px) { .topbar { align-items: flex-start; flex-direction: column; padding: 16px 18px; } }
    </style>
<link rel="stylesheet" href="<?= base_url('assets/css/app-sidebar.css') ?>">
</head>
<body class="app-layout">
<?php $this->load->view('partials/sidebar'); ?>
<header class="topbar">
    <h1 class="brand">Deportes Total</h1>
    <nav class="top-actions">
        <a class="top-link" href="<?= site_url('usuarios') ?>">Volver a usuarios</a>
        <a class="top-link" href="<?= site_url('inicio') ?>">Panel principal</a>
        
    </nav>
</header>

<main class="container">
    <section class="card">
        <h1>Crear nuevo usuario</h1>
        <p class="subtitle">Registra una nueva cuenta para acceder a Deportes Total.</p>

        

        <?= validation_errors('<div class="error">', '</div>') ?>

        <form method="post" action="<?= site_url('usuarios/guardar') ?>">
            <input type="hidden"
                   name="<?= $this->security->get_csrf_token_name() ?>"
                   value="<?= $this->security->get_csrf_hash() ?>">

            <div class="form-group">
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre"
                       value="<?= set_value('nombre') ?>"
                       placeholder="Ej. Carlos Pérez" required>
            </div>

            <div class="form-group">
                <label for="usuario">Nombre de usuario</label>
                <input type="text" id="usuario" name="usuario"
                       value="<?= set_value('usuario') ?>"
                       placeholder="Ej. carlos123" minlength="3" maxlength="50" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" minlength="6" required>
            </div>

            <div class="form-group">
                <label for="confirmar_password">Confirmar contraseña</label>
                <input type="password" id="confirmar_password" name="confirmar_password" minlength="6" required>
            </div>

            <div class="form-group">
                <label for="rol">Rol del usuario</label>
                <select id="rol" name="rol" required>
                    <option value="">Seleccionar rol</option>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?= html_escape($rol) ?>" <?= set_select('rol', $rol) ?>>
                            <?= ucfirst(html_escape($rol)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="buttons">
                <button type="submit" class="btn btn-primary">Crear usuario</button>
                <a href="<?= site_url('usuarios') ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>
