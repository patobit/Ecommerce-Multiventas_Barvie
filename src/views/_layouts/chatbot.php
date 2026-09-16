<?php
require_once __DIR__ . '/../../config/rutas.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/chatbot.css">
<div id="mv-chat" data-endpoint="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/src/controllers/chatbot.php">
    <button class="mv-chat-toggle" type="button" aria-expanded="false" aria-controls="mv-chat-panel">¿Necesitás ayuda?</button>
    <section id="mv-chat-panel" aria-label="Asistente de Multiventas Barvie" hidden>
        <div class="mv-chat-header">
            <div><strong>Asistente Barvie</strong><small>Ayuda para recorrer la tienda</small></div>
            <button class="mv-chat-close" type="button" aria-label="Cerrar chat">×</button>
        </div>
        <p class="mv-chat-notice">Asistente con respuestas programadas. Guardamos tu conversación. No compartas datos sensibles.</p>
        <div class="mv-chat-log" role="log" aria-live="polite" aria-label="Mensajes"></div>
        <div class="mv-chat-topics" aria-label="Consultas sugeridas">
            <button type="button">Catálogo</button><button type="button">Carrito</button><button type="button">Registro</button>
        </div>
        <p class="mv-chat-status" role="status"></p>
        <form class="mv-chat-form">
            <label class="mv-chat-label" for="mv-chat-message">Tu mensaje</label>
            <input id="mv-chat-message" name="mensaje" placeholder="Escribí tu consulta…" maxlength="1000" autocomplete="off" required>
            <button type="submit">Enviar</button>
        </form>
    </section>
</div>
<script src="<?= BASE_URL ?>/assets/js/chatbot.js" defer></script>
