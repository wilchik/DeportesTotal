<?php
$avisos = array();
$exito = $this->session->flashdata('exito');
$errorPersistente = $this->session->flashdata('error_persistente');
$error = $this->session->flashdata('error');
$this->session->unset_userdata(array('exito', 'error_persistente', 'error'));

if ($exito) {
    $avisos[] = array('tipo' => 'success', 'mensaje' => $exito, 'persistente' => FALSE);
} elseif ($errorPersistente) {
    $avisos[] = array('tipo' => 'error', 'mensaje' => $errorPersistente, 'persistente' => TRUE);
} elseif ($error) {
    $avisos[] = array('tipo' => 'error', 'mensaje' => $error, 'persistente' => FALSE);
}
?>
<div class="app-notifications" id="appNotifications" aria-live="polite" aria-relevant="additions text">
    <?php foreach ($avisos as $aviso): ?>
        <div class="app-notification <?= html_escape($aviso['tipo']) ?>" role="<?= $aviso['tipo'] === 'error' ? 'alert' : 'status' ?>" data-notification="true"<?= $aviso['persistente'] ? ' data-persistent="true"' : '' ?>>
            <span class="app-notification-message"><?= html_escape($aviso['mensaje']) ?></span>
            <button class="app-notification-close" type="button" aria-label="Cerrar notificación">×</button>
        </div>
    <?php endforeach; ?>
</div>
<link rel="stylesheet" href="<?= base_url('assets/css/app-notifications.css') ?>">
<script src="<?= base_url('assets/js/app-notifications.js') ?>"></script>
