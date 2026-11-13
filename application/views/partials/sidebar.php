<?php
$rolSidebar = $this->session->userdata('rol');
$segmentoSidebar = $this->uri->segment(1);
$nombreSidebar = $this->session->userdata('nombre');
$itemsSidebar = array();

if ($rolSidebar === 'propietario') {
    $itemsSidebar = array(
        array('ruta' => 'inicio', 'texto' => 'Panel de análisis', 'icono' => '▣'),
        array('ruta' => 'productos', 'texto' => 'Productos', 'icono' => '▤'),
        array('ruta' => 'inventario', 'texto' => 'Inventario', 'icono' => '▦'),
        array('ruta' => 'clientes', 'texto' => 'Clientes (CRM)', 'icono' => '♙'),
        array('ruta' => 'compras', 'texto' => 'Compras y proveedores', 'icono' => '▣'),
        array('ruta' => 'reportes', 'texto' => 'Reportes', 'icono' => '▥'),
        array('ruta' => 'usuarios', 'texto' => 'Gestión de usuarios', 'icono' => '♧')
    );
} elseif ($rolSidebar === 'administrador') {
    $itemsSidebar = array(
        array('ruta' => 'ventas/registrar', 'texto' => 'Punto de venta', 'icono' => '▣'),
        array('ruta' => 'caja', 'texto' => 'Cierre de caja', 'icono' => '▧'),
        array('ruta' => 'ventas/historial', 'texto' => 'Historial de ventas', 'icono' => '▤'),
        array('ruta' => 'productos', 'texto' => 'Productos', 'icono' => '▥'),
        array('ruta' => 'inventario', 'texto' => 'Inventario', 'icono' => '▦'),
        array('ruta' => 'clientes', 'texto' => 'Clientes (CRM)', 'icono' => '♙'),
        array('ruta' => 'compras', 'texto' => 'Compras y proveedores', 'icono' => '▣'),
        array('ruta' => 'reportes', 'texto' => 'Reportes', 'icono' => '▥')
    );
} else {
    $itemsSidebar = array(
        array('ruta' => 'ventas/registrar', 'texto' => 'Punto de venta', 'icono' => '▣'),
        array('ruta' => 'caja', 'texto' => 'Cierre de caja', 'icono' => '▧'),
        array('ruta' => 'ventas/historial', 'texto' => 'Mis ventas', 'icono' => '▤'),
        array('ruta' => 'productos', 'texto' => 'Consultar productos', 'icono' => '▥'),
        array('ruta' => 'clientes', 'texto' => 'Clientes', 'icono' => '♙')
    );
}
?>
<aside class="app-sidebar">
    <div class="app-sidebar-brand">
        <strong>DEPORTES TOTAL</strong>
        <span>Gestión de tienda deportiva</span>
    </div>
    <div class="app-sidebar-user">
        <small>Sesión iniciada</small>
        <strong><?= html_escape($nombreSidebar) ?></strong>
        <span><?= ucfirst(html_escape($rolSidebar)) ?></span>
    </div>
    <div class="app-sidebar-title">Menú principal</div>
    <nav class="app-sidebar-nav" aria-label="Navegación principal">
        <?php foreach ($itemsSidebar as $item): ?>
            <?php $segmentoItem = explode('/', $item['ruta'])[0]; ?>
            <a href="<?= site_url($item['ruta']) ?>" class="<?= $segmentoSidebar === $segmentoItem ? 'active' : '' ?>">
                <span class="app-sidebar-icon"><?= $item['icono'] ?></span>
                <span><?= html_escape($item['texto']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="app-sidebar-bottom">
        <form method="post" action="<?= site_url('login/cerrar_sesion') ?>" onsubmit="return confirm('¿Seguro que deseas cerrar sesión?')">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <button type="submit">Cerrar sesión</button>
        </form>
    </div>
</aside>
<?php $this->load->view('partials/notifications'); ?>
