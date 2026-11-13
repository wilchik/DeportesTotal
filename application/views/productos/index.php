<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Productos | Deportes Total</title>
<style>
*{box-sizing:border-box;font-family:Arial,Helvetica,sans-serif}body{margin:0;background:#eef1f3;color:#263238}.topbar{background:#263f59;color:#fff;padding:15px 25px;border-bottom:4px solid #1b3045;display:flex;justify-content:space-between;align-items:center;gap:12px}.topbar h1{font-size:21px;margin:0}.topbar a{color:#fff;text-decoration:none;background:#a93636;padding:9px 13px;border-radius:3px}.container{max-width:1150px;margin:28px auto;padding:0 18px}.head{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:18px}.head h2{margin:0;color:#263f59}.card{background:#fff;border:1px solid #cbd3da;padding:22px;margin-bottom:20px}.btn{display:inline-block;border:0;padding:10px 14px;background:#315f85;color:#fff;text-decoration:none;border-radius:3px;cursor:pointer;font-weight:bold}.btn.gray{background:#687783}.btn.orange{background:#d86b2b}.btn.red{background:#a93636}.msg{padding:12px;margin-bottom:15px;background:#e8f2e8;border-left:4px solid #4b8a4b}.msg.error{background:#ffeded;border-color:#b33}.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:700px}th,td{text-align:left;padding:12px;border-bottom:1px solid #e3e7ea}th{background:#e9eef2;color:#34495e;font-size:13px;text-transform:uppercase}.tag{padding:4px 8px;background:#e4edf4;border-radius:3px;font-size:12px}.muted{color:#6c7a86;font-size:13px}@media(max-width:650px){.head{align-items:flex-start;flex-direction:column}.topbar{align-items:flex-start;flex-direction:column}}
</style><link rel="stylesheet" href="<?= base_url('assets/css/app-sidebar.css') ?>">
</head><body class="app-layout">
<?php $this->load->view('partials/sidebar'); ?>
<header class="topbar"><h1>DEPORTES TOTAL | Productos</h1><a href="<?= site_url('inicio') ?>">Volver al panel</a></header>
<main class="container">
<div class="head"><div><h2>Catálogo de productos</h2><p class="muted">Cada registro representa una referencia en una talla específica.</p></div><?php if ($puede_administrar): ?><a class="btn" href="<?= site_url('productos/nuevo') ?>">+ Registrar producto</a><?php endif; ?></div>


<section class="card"><div class="table-wrap"><table><thead><tr><th>Producto</th><th>Referencia</th><th>Talla</th><?php if ($puede_administrar): ?><th>Costo de adquisición</th><?php endif; ?><th>Precio</th><th>Existencias</th><th>Estado</th><?php if ($puede_administrar): ?><th>Acciones</th><?php endif; ?></tr></thead><tbody>
<?php if (empty($productos)): ?><tr><td colspan="<?= $puede_administrar ? 8 : 6 ?>">Todavía no hay productos registrados.</td></tr><?php else: foreach ($productos as $p): ?><tr>
<td><?= html_escape($p->nombre) ?></td><td><strong><?= html_escape($p->referencia) ?></strong></td><td><?= html_escape($p->talla) ?></td><?php if ($puede_administrar): ?><td><?= $p->costo_adquisicion !== NULL ? '$'.number_format((float)$p->costo_adquisicion,0,',','.') : 'Sin costo histórico' ?></td><?php endif; ?><td>$<?= number_format((float)$p->precio,0,',','.') ?></td><td><?= (int)$p->stock ?></td><td><?= $p->estado ? 'Activo' : 'Inactivo' ?></td>
<?php if ($puede_administrar): ?><td><a class="btn" href="<?= site_url('productos/editar/'.$p->id) ?>">Editar</a> <form style="display:inline" method="post" action="<?= site_url('productos/cambiar_estado/'.$p->id) ?>" onsubmit="return confirm('¿Cambiar el estado de este producto?')"><input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>"><button class="btn <?= $p->estado ? 'red' : 'orange' ?>" type="submit"><?= $p->estado ? 'Desactivar' : 'Activar' ?></button></form></td><?php endif; ?>
</tr><?php endforeach; endif; ?></tbody></table></div></section>
</main></body></html>
