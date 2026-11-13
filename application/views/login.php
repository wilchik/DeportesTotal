<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | Deportes Total</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #eef1f3; color: #263238; }
        .login-box { width: 100%; max-width: 410px; margin: 20px; padding: 30px; background: #fff; border: 1px solid #cbd3da; border-top: 5px solid #263f59; box-shadow: 0 3px 12px rgba(38,63,89,.08); }
        h1 { margin: 0 0 7px; color: #263f59; text-align: center; font-size: 26px; letter-spacing: .4px; }
        h2 { margin: 0 0 24px; color: #65727e; text-align: center; font-size: 16px; font-weight: normal; }
        label { display: block; margin: 16px 0 7px; font-weight: bold; font-size: 14px; }
        input { width: 100%; padding: 12px; border: 1px solid #bdc8d1; border-radius: 3px; font-size: 15px; }
        input:focus { outline: 2px solid #9db9ce; border-color: #315f85; }
        .password-wrap { position: relative; }
        .password-wrap input { padding-right: 82px; }
        .toggle-password { position: absolute; right: 5px; top: 5px; width: auto; margin: 0; padding: 7px 9px; border: 1px solid #bdc8d1; border-radius: 3px; background: #e9eef2; color: #263f59; font-size: 12px; font-weight: bold; cursor: pointer; }
        .toggle-password:hover { background: #dce5ec; }
        .submit { width: 100%; margin-top: 24px; padding: 12px; border: 0; border-radius: 3px; background: #315f85; color: #fff; font-size: 15px; font-weight: bold; cursor: pointer; }
        .submit:hover { background: #264b69; }
        .error { padding: 10px; margin-bottom: 14px; color: #9d2929; background: #fceaea; border: 1px solid #e7bcbc; }
        .foot { margin: 18px 0 0; color: #7b8790; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <?php $this->load->view('partials/notifications'); ?>
    <div class="login-box">
        <h1>DEPORTES TOTAL</h1>
        <h2>Inicio de sesión</h2>

        <form action="<?= site_url('login/validar') ?>" method="post">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" name="usuario" autocomplete="username" required autofocus>

            <label for="password">Contraseña</label>
            <div class="password-wrap">
                <input type="password" id="password" name="password" autocomplete="current-password" required>
                <button type="button" class="toggle-password" id="togglePassword" aria-controls="password" aria-pressed="false">Mostrar</button>
            </div>

            <button class="submit" type="submit">Ingresar</button>
        </form>
        <p class="foot">Tienda deportiva</p>
    </div>

    <script>
        (function () {
            var campo = document.getElementById('password');
            var boton = document.getElementById('togglePassword');
            boton.addEventListener('click', function () {
                var mostrar = campo.type === 'password';
                campo.type = mostrar ? 'text' : 'password';
                boton.textContent = mostrar ? 'Ocultar' : 'Mostrar';
                boton.setAttribute('aria-pressed', mostrar ? 'true' : 'false');
            });
        })();
    </script>
</body>
</html>
