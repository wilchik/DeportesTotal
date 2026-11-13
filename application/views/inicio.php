<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel de propietario | Deportes Total</title>
<style>
    * { box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
    body { margin: 0; background: #eef1f3; color: #263238; }
    .sidebar {
        position: fixed; top: 0; left: 0; bottom: 0; z-index: 20;
        width: 258px; padding: 22px 16px 18px; display: flex; flex-direction: column;
        overflow-y: auto; background: #263f59; color: #fff; border-right: 5px solid #1b3045;
    }
    .brand { padding: 3px 10px 20px; border-bottom: 1px solid rgba(255,255,255,.2); }
    .brand strong { display: block; font-size: 20px; letter-spacing: .5px; }
    .brand span { display: block; margin-top: 6px; color: #c8d5df; font-size: 12px; }
    .user-box { margin: 18px 4px 18px; padding: 12px; background: #1f354b; border: 1px solid rgba(255,255,255,.14); }
    .user-box small { display: block; margin-bottom: 5px; color: #c8d5df; font-size: 11px; text-transform: uppercase; }
    .user-box strong { display: block; overflow-wrap: anywhere; font-size: 14px; }
    .section-title { margin: 2px 10px 9px; color: #b9c9d6; font-size: 11px; font-weight: bold; letter-spacing: .7px; text-transform: uppercase; }
    .nav { display: flex; flex-direction: column; gap: 5px; }
    .nav a { display: flex; align-items: center; gap: 11px; padding: 12px 11px; color: #f3f6f8; text-decoration: none; border-left: 4px solid transparent; font-size: 14px; }
    .nav a:hover, .nav a.active { background: #355673; border-left-color: #d6e1e9; }
    .nav-icon { width: 20px; text-align: center; color: #d4e0e8; font-weight: bold; }
    .sidebar-bottom { margin-top: auto; padding-top: 22px; }
    .sidebar-bottom form { width: 100%; margin: 0; flex: 0 0 auto; }
    .logout { box-sizing: border-box; width: 100%; min-height: 42px; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; padding: 11px 12px; background: #a93636; color: #fff; text-align: center; text-decoration: none; border: 1px solid #8f2e2e; font: inherit; cursor: pointer; }
    .logout:hover { background: #912d2d; }
    .main-area { min-height: 100vh; margin-left: 258px; padding: 27px 30px 40px; }
    .content { max-width: 1450px; margin: 0 auto; }
    .page-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 15px; margin-bottom: 20px; }
    .page-head h1 { margin: 0; color: #263f59; font-size: 25px; }
    .page-head p { margin: 7px 0 0; color: #6c7a86; font-size: 13px; }
    .welcome { background: #fff; border: 1px solid #cbd3da; border-left: 5px solid #315f85; padding: 18px 20px; margin-bottom: 18px; }
    .welcome h2 { margin: 0 0 7px; color: #263f59; font-size: 19px; }
    .muted { color: #6c7a86; font-size: 13px; line-height: 1.45; }
    .metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(175px, 1fr)); gap: 13px; margin-bottom: 18px; }
    .metric { min-height: 104px; background: #fff; border: 1px solid #cbd3da; border-top: 4px solid #3976a8; padding: 16px; }
    .metric span { display: block; color: #687783; font-size: 13px; }
    .metric strong { display: block; margin-top: 8px; color: #263f59; font-size: 25px; overflow-wrap: anywhere; }
    .grid { display: grid; grid-template-columns: 1.15fr 1fr; gap: 17px; }
    .card { min-width: 0; background: #fff; border: 1px solid #cbd3da; padding: 19px; margin-bottom: 17px; }
    .card h3 { margin: 0 0 14px; color: #263f59; font-size: 16px; }
    .channel { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin: 9px 0; padding: 11px; background: #f6f8fa; border-left: 3px solid #3976a8; }
    .channel strong { color: #263f59; }
    .channel .muted { margin-top: 3px; }
    .low { color: #a33; font-weight: bold; white-space: nowrap; }
    .table-scroll { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 560px; }
    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e3e7ea; }
    th { background: #e9eef2; color: #526474; font-size: 11px; text-transform: uppercase; }
    .tag { display: inline-block; padding: 4px 7px; background: #e8eef3; border-radius: 3px; font-size: 12px; }
    .empty { color: #7b8790; font-size: 13px; }
    @media (max-width: 900px) {
        .sidebar { width: 225px; }
        .main-area { margin-left: 225px; padding: 22px 18px; }
        .grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 650px) {
        .sidebar { position: relative; width: 100%; max-height: none; min-height: auto; padding: 15px; border-right: 0; border-bottom: 5px solid #1b3045; }
        .brand { padding-bottom: 12px; }
        .user-box { margin: 12px 0; }
        .section-title { margin-top: 10px; }
        .nav { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 4px; }
        .nav a { padding: 10px 7px; font-size: 12px; }
        .nav-icon { width: 15px; }
        .sidebar-bottom { padding-top: 12px; }
        .main-area { margin-left: 0; padding: 20px 13px 30px; }
        .page-head h1 { font-size: 21px; }
    }
</style>
</head>
<body>
<aside class="sidebar">
    <div class="brand">
        <strong>DEPORTES TOTAL</strong>
        <span>Administración del negocio</span>
    </div>

    <div class="user-box">
        <small>Sesión iniciada</small>
        <strong><?= html_escape($this->session->userdata('nombre')) ?></strong>
        <small style="margin:6px 0 0;">Propietario</small>
    </div>

    <div class="section-title">Administración del negocio</div>
    <nav class="nav" aria-label="Menú principal">
        <a class="active" href="<?= site_url('inicio') ?>"><span class="nav-icon">▣</span>Panel de análisis</a>
        <a href="<?= site_url('productos') ?>"><span class="nav-icon">▤</span>Productos</a>
        <a href="<?= site_url('inventario') ?>"><span class="nav-icon">▦</span>Inventario</a>
        <a href="<?= site_url('clientes') ?>"><span class="nav-icon">♙</span>Clientes (CRM)</a>
        <a href="<?= site_url('compras') ?>"><span class="nav-icon">▣</span>Compras y proveedores</a>
        <a href="<?= site_url('reportes') ?>"><span class="nav-icon">▥</span>Reportes</a>
        <a href="<?= site_url('usuarios') ?>"><span class="nav-icon">♧</span>Gestión de usuarios</a>
    </nav>

    <div class="sidebar-bottom">
        <form method="post" action="<?= site_url('login/cerrar_sesion') ?>" onsubmit="return confirm('¿Seguro que deseas cerrar sesión?')">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <button class="logout" type="submit">Cerrar sesión</button>
        </form>
    </div>
</aside>

<main class="main-area">
<div class="content">
    <header class="page-head">
        <div>
            <h1>Panel de análisis</h1>
            <p>Resumen general de la actividad comercial de la tienda.</p>
        </div>
    </header>

    <section class="welcome">
        <h2>Bienvenido, <?= html_escape($this->session->userdata('nombre')) ?></h2>
        <div class="muted">Desde aquí puedes supervisar las ventas, el inventario, los clientes y el desempeño del negocio. Este perfil no registra ventas de caja.</div>
    </section>

    <div class="metrics">
        <div class="metric"><span>Ventas de hoy</span><strong><?= number_format($resumen['ventas_hoy'], 0, ',', '.') ?></strong></div>
        <div class="metric"><span>Ingresos de hoy</span><strong>$<?= number_format($resumen['ingresos_hoy'], 0, ',', '.') ?></strong></div>
        <div class="metric"><span>Productos activos</span><strong><?= number_format($resumen['productos'], 0, ',', '.') ?></strong></div>
        <div class="metric"><span>Unidades en inventario</span><strong><?= number_format($resumen['unidades'], 0, ',', '.') ?></strong></div>
        <div class="metric"><span>COSTO TOTAL DEL INVENTARIO<?= (int)$resumen['inventario_costos']->productos_sin_costo > 0 ? ' · conocido' : '' ?></span><strong>$<?= number_format((float)$resumen['inventario_costos']->total_conocido, 0, ',', '.') ?></strong><?php if ((int)$resumen['inventario_costos']->productos_sin_costo > 0): ?><small><?= (int)$resumen['inventario_costos']->productos_sin_costo ?> productos activos con existencias no tienen costo histórico.</small><?php endif; ?></div>
        <div class="metric"><span>Clientes registrados</span><strong><?= number_format($resumen['clientes'], 0, ',', '.') ?></strong></div>
    </div>

    <div class="grid">
        <section class="card">
            <h3>Ventas por canal · Mes actual</h3>
            <?php if (empty($resumen['canales'])): ?>
                <p class="empty">Todavía no hay ventas registradas este mes.</p>
            <?php else: ?>
                <?php foreach ($resumen['canales'] as $canal): ?>
                    <div class="channel">
                        <div><strong><?= html_escape($canal->canal) ?></strong><div class="muted"><?= (int)$canal->operaciones ?> operaciones</div></div>
                        <strong>$<?= number_format((float)$canal->total, 0, ',', '.') ?></strong>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <section class="card">
            <h3>Productos con pocas existencias</h3>
            <?php if (empty($resumen['bajo_stock'])): ?>
                <p class="empty">No hay productos activos con stock igual o inferior a 3.</p>
            <?php else: ?>
                <?php foreach ($resumen['bajo_stock'] as $p): ?>
                    <div class="channel">
                        <div><strong><?= html_escape($p->nombre) ?></strong><div class="muted">Ref. <?= html_escape($p->referencia) ?> · Talla <?= html_escape($p->talla) ?></div></div>
                        <span class="low"><?= (int)$p->stock ?> unidades</span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>

    <section class="card">
        <h3>Clientes por interés deportivo</h3>
        <div class="table-scroll">
            <table><thead><tr><th>Interés</th><th>Clientes</th><th>Compras</th><th>Total comprado</th></tr></thead><tbody>
            <?php if (empty($resumen['segmentos_clientes'])): ?>
                <tr><td colspan="4" class="empty">Todavía no hay clientes registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($resumen['segmentos_clientes'] as $segmento): ?>
                    <tr><td><?= html_escape($segmento->deporte) ?></td><td><?= (int)$segmento->clientes ?></td><td><?= (int)$segmento->compras ?></td><td>$<?= number_format((float)$segmento->total, 0, ',', '.') ?></td></tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody></table>
        </div>
    </section>

    <section class="card">
        <h3>Valor del inventario por producto</h3>
        <div class="table-scroll">
            <table><thead><tr><th>Producto</th><th>Referencia</th><th>Talla</th><th>Costo unitario</th><th>Existencias</th><th>Costo total</th></tr></thead><tbody>
            <?php foreach ($resumen['inventario_desglose'] as $producto): ?>
                <tr><td><?= html_escape($producto->nombre) ?></td><td><?= html_escape($producto->referencia) ?></td><td><?= html_escape($producto->talla) ?></td><td><?= $producto->costo_adquisicion !== NULL ? '$'.number_format((float)$producto->costo_adquisicion,0,',','.') : 'Sin costo histórico' ?></td><td><?= (int)$producto->stock ?></td><td><?= $producto->valor_inventario !== NULL ? '$'.number_format((float)$producto->valor_inventario,0,',','.') : 'No disponible' ?></td></tr>
            <?php endforeach; ?>
            <?php if (empty($resumen['inventario_desglose'])): ?><tr><td colspan="6" class="empty">No hay productos activos.</td></tr><?php endif; ?>
            </tbody></table>
        </div>
    </section>

    <section class="card">
        <h3>Últimas ventas registradas</h3>
        <div class="table-scroll">
            <table><thead><tr><th>Venta</th><th>Fecha</th><th>Cliente</th><th>Vendedor</th><th>Canal</th><th>Total</th></tr></thead><tbody>
            <?php if (empty($resumen['recientes'])): ?>
                <tr><td colspan="6" class="empty">Todavía no hay ventas registradas.</td></tr>
            <?php else: ?>
                <?php foreach ($resumen['recientes'] as $v): ?>
                    <tr><td><?= html_escape($v->codigo) ?></td><td><?= html_escape($v->fecha) ?></td><td><?= html_escape($v->cliente) ?></td><td><?= html_escape($v->vendedor) ?></td><td><span class="tag"><?= html_escape($v->canal) ?></span></td><td>$<?= number_format((float)$v->total, 0, ',', '.') ?></td></tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody></table>
        </div>
    </section>
</div>
</main>
</body>
</html>
