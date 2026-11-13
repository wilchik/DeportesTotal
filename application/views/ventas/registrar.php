<?php
$csrfName = $this->security->get_csrf_token_name();
$csrfHash = $this->security->get_csrf_hash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de venta | Deportes Total</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app-sidebar.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app-pos.css') ?>">
</head>
<body class="app-layout">
<?php $this->load->view('partials/sidebar'); ?>
<header class="pos-topbar">
    <div><span class="pos-kicker">DEPORTES TOTAL</span><h1>Punto de venta</h1></div>
    <div class="pos-topbar-actions"><div class="pos-user"><?= html_escape($this->session->userdata('nombre')) ?> <span><?= ucfirst(html_escape($rol)) ?></span></div><a class="pos-history-link" href="<?= site_url('caja') ?>">CIERRE DE CAJA</a><a class="pos-history-link" href="<?= site_url('ventas/historial') ?>">Historial</a></div>
</header>

<main class="pos-container">
    <form method="post" action="<?= site_url('ventas/guardar') ?>" id="formVenta" novalidate>
        <input type="hidden" name="<?= html_escape($csrfName) ?>" value="<?= html_escape($csrfHash) ?>">
        <input type="hidden" name="canal" id="canalVenta" value="">
        <input type="hidden" name="metodo_pago" id="metodoPago" value="">
        <input type="hidden" name="cliente_id" id="clienteId" value="">
        <div id="carritoCampos"></div>

        <div class="pos-work-grid">
        <section class="pos-panel pos-products" aria-labelledby="tituloProductos">
            <div class="pos-panel-heading"><span class="pos-step">1</span><div><h2 id="tituloProductos">Selecciona los productos</h2><p>Busca por nombre o referencia. Solo verás productos activos con existencias.</p></div></div>
            <div class="pos-search-grid">
                <div class="pos-field">
                    <label for="buscarNombre">Buscar producto por nombre</label>
                    <input type="search" id="buscarNombre" autocomplete="off" placeholder="Ej. Camiseta Adidas Colombia" aria-controls="resultadosNombre">
                    <div class="pos-results" id="resultadosNombre" role="listbox" aria-label="Resultados por nombre"></div>
                </div>
                <div class="pos-field">
                    <label for="buscarReferencia">Buscar por referencia</label>
                    <input type="search" id="buscarReferencia" autocomplete="off" placeholder="Ej. AD-COL-01" aria-controls="resultadosReferencia">
                    <div class="pos-results" id="resultadosReferencia" role="listbox" aria-label="Resultados por referencia"></div>
                </div>
            </div>

            <p class="pos-help" id="ayudaBusqueda">Escribe al menos 2 caracteres. Usa ↑ y ↓ para navegar, ENTER para agregar y ESC para cerrar resultados.</p>
        </section>

        <div class="pos-left-column">
        <section class="pos-panel" aria-labelledby="tituloCarrito">
            <div class="pos-panel-heading"><span class="pos-step">2</span><div><h2 id="tituloCarrito">Carrito de la venta</h2><p>La cantidad de cada línea se puede ajustar antes de finalizar.</p></div><span class="pos-count" id="cantidadLineas">0 productos</span></div>
            <div class="pos-cart-wrap">
                <table class="pos-cart-table">
                    <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th><th><span class="sr-only">Eliminar</span></th></tr></thead>
                    <tbody id="carritoBody"></tbody>
                </table>
                <div class="pos-empty-cart" id="carritoVacio">Aún no agregas productos. Usa los buscadores de arriba para empezar.</div>
            </div>
            <div class="pos-cart-total"><span>Total de productos: <strong id="unidadesTotal">0</strong></span><span>Total de la venta <strong id="totalVenta">$0</strong></span></div>
        </section>
        </div>

        <aside class="pos-right-column">
        <section class="pos-panel" aria-labelledby="tituloCliente">
            <div class="pos-panel-heading"><span class="pos-step">3</span><div><h2 id="tituloCliente">Selecciona el cliente</h2><p>Busca por nombre o número de teléfono.</p></div></div>
            <div class="pos-client-search">
                <div class="pos-field"><label for="buscarCliente">Buscar cliente</label><input type="search" id="buscarCliente" autocomplete="off" placeholder="Nombre o teléfono" aria-controls="resultadosCliente"><div class="pos-results" id="resultadosCliente" role="listbox" aria-label="Resultados de clientes"></div></div>
                <button type="button" class="pos-button secondary" id="abrirModalCliente">+ AGREGAR NUEVO CLIENTE</button>
            </div>
            <div class="pos-client-card" id="clienteSeleccionado" hidden>
                <div class="pos-avatar" aria-hidden="true">&#128100;</div>
                <div class="pos-client-copy"><small>CLIENTE DE ESTA VENTA</small><strong id="clienteSeleccionadoNombre"></strong><span id="clienteSeleccionadoMeta"></span></div>
                <button type="button" class="pos-link-button" id="quitarCliente">Cambiar cliente</button>
            </div>
        </section>

        <section class="pos-panel" aria-labelledby="tituloCanal">
            <div class="pos-panel-heading"><span class="pos-step">4</span><div><h2 id="tituloCanal">SELECCIONA EL CANAL DE VENTA</h2><p>Indica dónde se originó el pedido.</p></div></div>
            <div class="pos-choice-grid three">
                <button type="button" class="pos-choice" data-choice="canal" data-value="Tienda" aria-pressed="false"><span class="pos-choice-icon" aria-hidden="true">&#127978;</span><strong>TIENDA FÍSICA</strong></button>
                <button type="button" class="pos-choice" data-choice="canal" data-value="WhatsApp" aria-pressed="false"><span class="pos-choice-icon whatsapp" aria-hidden="true">&#9742;</span><strong>WHATSAPP</strong></button>
                <button type="button" class="pos-choice" data-choice="canal" data-value="Instagram" aria-pressed="false"><span class="pos-choice-icon instagram" aria-hidden="true">&#128247;</span><strong>INSTAGRAM</strong></button>
            </div>
        </section>

        <section class="pos-panel" aria-labelledby="tituloPago">
            <div class="pos-panel-heading"><span class="pos-step">5</span><div><h2 id="tituloPago">SELECCIONA EL MÉTODO DE PAGO</h2><p>El canal y el pago son datos independientes.</p></div></div>
            <div class="pos-choice-grid payment">
                <button type="button" class="pos-choice" data-choice="pago" data-value="Efectivo" aria-pressed="false"><span class="pos-choice-icon" aria-hidden="true">&#128176;</span><strong>EFECTIVO</strong></button>
                <button type="button" class="pos-choice" data-choice="pago" data-value="Transferencia" aria-pressed="false"><span class="pos-choice-icon" aria-hidden="true">&#127974;</span><strong>TRANSFERENCIA</strong></button>
                <button type="button" class="pos-choice" data-choice="pago" data-value="Tarjeta" aria-pressed="false"><span class="pos-choice-icon" aria-hidden="true">&#128179;</span><strong>TARJETA</strong></button>
            </div>
        </section>
        </aside>

        <section class="pos-confirmation" aria-labelledby="tituloResumen">
            <div><span class="pos-confirmation-kicker">RESUMEN DE LA VENTA</span><h2 id="tituloResumen">Verifica y finaliza</h2><div class="pos-summary-grid"><span>Cliente<strong id="resumenCliente">Pendiente</strong></span><span>Canal<strong id="resumenCanal">Pendiente</strong></span><span>Método de pago<strong id="resumenPago">Pendiente</strong></span><span>Cantidad<strong id="resumenUnidades">0 productos</strong></span></div></div>
            <div class="pos-confirmation-action"><div class="pos-grand-total"><span>Total</span><strong id="resumenTotal">$0</strong></div><button type="submit" class="pos-button finish" id="finalizarVenta">FINALIZAR VENTA</button></div>
        </section>
        </div>
    </form>
</main>

<div class="pos-modal-backdrop" id="modalCliente" hidden>
    <section class="pos-modal" role="dialog" aria-modal="true" aria-labelledby="tituloModalCliente">
        <button type="button" class="pos-modal-close" id="cerrarModalCliente" aria-label="Cerrar ventana">×</button>
        <span class="pos-confirmation-kicker">NUEVO REGISTRO</span>
        <h2 id="tituloModalCliente">Agregar cliente</h2>
        <p>Al guardar, el cliente quedará seleccionado para esta venta.</p>
        <div class="pos-modal-error" id="errorModalCliente" role="alert" hidden></div>
        <form id="formClienteNuevo" action="<?= site_url('clientes/crear_ajax') ?>" method="post" novalidate>
            <input type="hidden" name="<?= html_escape($csrfName) ?>" value="<?= html_escape($csrfHash) ?>">
            <div class="pos-field"><label for="nuevoNombre">Nombre completo</label><input id="nuevoNombre" name="nombre" maxlength="100" required autocomplete="name"></div>
            <div class="pos-field"><label for="nuevoTelefono">Número de teléfono</label><input id="nuevoTelefono" name="telefono" maxlength="20" required autocomplete="tel" inputmode="tel"></div>
            <div class="pos-field"><label for="nuevoCorreo">Correo electrónico <span>(opcional)</span></label><input id="nuevoCorreo" name="correo" type="email" maxlength="100" autocomplete="email"></div>
            <div class="pos-field"><label for="nuevoDeporte">Deporte de interés</label><select id="nuevoDeporte" name="deporte_interes"><option>No especificado</option><option>Fútbol</option><option>Baloncesto</option><option>Running</option><option>Gimnasio</option><option>Ciclismo</option><option>Voleibol</option><option>Otro</option></select></div>
            <div class="pos-modal-actions"><button type="button" class="pos-button secondary" id="cancelarModalCliente">Cancelar</button><button type="submit" class="pos-button primary" id="guardarCliente">Guardar cliente</button></div>
        </form>
    </section>
</div>

<script>
window.DeportesTotalPOS = {
    productSearchUrl: <?= json_encode(site_url('productos/buscar_para_venta'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    clientSearchUrl: <?= json_encode(site_url('clientes/buscar_para_venta'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    saveSaleUrl: <?= json_encode(site_url('ventas/guardar'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    csrfName: <?= json_encode($csrfName, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    channels: {Tienda: 'Tienda física', WhatsApp: 'WhatsApp', Instagram: 'Instagram'},
    payments: {Efectivo: 'Efectivo', Transferencia: 'Transferencia', Tarjeta: 'Tarjeta'}
};
</script>
<script src="<?= base_url('assets/js/app-pos.js') ?>"></script>
</body>
</html>
