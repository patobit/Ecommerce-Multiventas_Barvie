(() => {
    const root = document.getElementById('mv-chat');
    if (!root || root.dataset.ready) return;
    root.dataset.ready = 'true';
    const panel = root.querySelector('section');
    const toggle = root.querySelector('.mv-chat-toggle');
    const log = root.querySelector('.mv-chat-log');
    const form = root.querySelector('form');
    const input = root.querySelector('input');
    const status = root.querySelector('.mv-chat-status');
    const sendButtons = root.querySelectorAll('.mv-chat-form button, .mv-chat-topics button');
    let token = null;
    let busy = false;

    function message(text, sender) {
        const item = document.createElement('p');
        item.className = 'mv-chat-message ' + (sender === 'usuario' ? 'usuario' : 'bot');
        item.textContent = text;
        log.appendChild(item);
        log.scrollTop = log.scrollHeight;
    }
    function pending(value) {
        busy = value;
        sendButtons.forEach(button => { button.disabled = value; });
        input.disabled = value;
    }
    async function request(options) {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), 15000);
        try {
            const response = await fetch(root.dataset.endpoint, { ...options, signal: controller.signal, credentials: 'same-origin' });
            const data = await response.json();
            if (!response.ok) throw new Error(data.error || 'No se pudo conectar el chat.');
            return data;
        } finally { clearTimeout(timer); }
    }
    async function history() {
        pending(true);
        status.textContent = 'Cargando conversación…';
        try {
            const data = await request({ method: 'GET' });
            token = data.token;
            log.replaceChildren();
            data.mensajes.forEach(item => message(item.mensaje, item.remitente));
            if (!data.mensajes.length) message('¡Hola! Soy el asistente de Multiventas Barvie. Elegí un tema o escribí tu consulta.', 'bot');
            status.textContent = '';
        } catch (error) {
            status.textContent = 'No se pudo cargar el chat. Cerralo y abrilo para reintentar.';
        } finally { pending(false); if (!panel.hidden) input.focus(); }
    }
    function close() {
        panel.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus();
    }
    toggle.addEventListener('click', () => {
        if (!panel.hidden) return close();
        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        if (!busy) history();
    });
    root.querySelector('.mv-chat-close').addEventListener('click', close);
    root.addEventListener('keydown', event => { if (event.key === 'Escape' && !panel.hidden) close(); });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const text = input.value.trim();
        if (!text || busy) return;
        if (!token) { await history(); return; }
        pending(true);
        status.textContent = 'Consultando…';
        try {
            const data = await request({ method: 'POST', body: new URLSearchParams({ mensaje: text, token }) });
            message(text, 'usuario');
            message(data.respuesta, 'bot');
            input.value = '';
            status.textContent = '';
        } catch (error) {
            status.textContent = error.name === 'AbortError' ? 'La conexión tardó demasiado. Reabrí el chat para verificar si se guardó.' : (error.message || 'No se pudo enviar. Intentá nuevamente.');
        } finally { pending(false); if (!panel.hidden) input.focus(); }
    });
    root.querySelectorAll('.mv-chat-topics button').forEach(button => {
        button.addEventListener('click', () => { input.value = button.textContent; form.requestSubmit(); });
    });
})();
