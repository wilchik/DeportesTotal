<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar usuario | Deportes Total</title>
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
        .info { background: #edf1f4; border: 1px solid #d0d9e0; color: #4f6271; padding: 11px 13px; margin-bottom: 18px; font-size: 13px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 7px; font-weight: bold; color: #394a56; }
        input, select { width: 100%; padding: 11px; border: 1px solid #c3ccd3; border-radius: 2px; background: #fff; color: #263238; font-size: 15px; }
        input:focus, select:focus { outline: 2px solid #c3d4e1; border-color: #315f85; }
        input:disabled { background: #e8ecef; color: #66737d; cursor: not-allowed; }
        .buttons { display: flex; flex-wrap: wrap; gap: 9px; margin-top: 23px; }
        .btn { display: inline-block; padding: 11px 16px; border: 1px solid transparent; border-radius: 3px; cursor: pointer; font-size: 14px; font-weight: bold; text-decoration: none; }
        .btn-primary { background: #315f85; border-color: #264b69; color: #fff; }
        .btn-primary:hover { background: #264b69; }
        .btn-secondary { background: #e8ecef; border-color: #c5cdd3; color: #394a56; }
        .btn-secondary:hover { background: #dce2e6; }
        .error { color: #a32e2e; margin-bottom: 12px; }
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
        <h1>Editar usuario</h1>
        <p class="subtitle">Modifica la información de la cuenta seleccionada.</p>

        <div class="info">Si no deseas cambiar la contraseña, deja ese campo vacío.</div>
        <?= validation_errors('<div class="error">', '</div>') ?>

        <form method="post" action="<?= site_url('usuarios/actualizar/'.$usuario->id) ?>">
            <input type="hidden"
                   name="<?= $this->security->get_csrf_token_name() ?>"
                   value="<?= $this->security->get_csrf_hash() ?>">

            <div class="form-group">
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre"
                       value="<?= html_escape($usuario->nombre) ?>" required>
            </div>

            <div class="form-group">
                <label for="usuario">Nombre de usuario</label>
                <input type="text" id="usuario" value="<?= html_escape($usuario->usuario) ?>" disabled>
            </div>

            <div class="form-group">
                <label for="password">Nueva contraseña</label>
                <input type="password" id="password" name="password" minlength="6"
                       placeholder="Dejar vacío para conservar la actual">
            </div>

            <div class="form-group">
                <label for="rol">Rol del usuario</label>
                <select id="rol" name="rol" required>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?= html_escape($rol) ?>" <?= $usuario->rol === $rol ? 'selected' : '' ?>>
                            <?= ucfirst(html_escape($rol)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="buttons">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a href="<?= site_url('usuarios') ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>
