# Chatbot integrado — Multiventas Barvie

## Qué se implementó

Un asistente de atención básica basado en el apunte de la materia. Usa PHP,
JavaScript y SQLite para responder consultas por palabras clave y guardar las
conversaciones. Es un chatbot de reglas: no utiliza un modelo de IA, no aprende
de los mensajes y no requiere claves ni servicios pagos.

La ventana se incluye en las páginas que usan el pie común de la tienda,
en login y registro, y en el perfil. Tiene botones de consultas sugeridas,
envío con Enter, cierre con Escape y un diseño adaptable a pantallas pequeñas.

## Cómo ejecutarlo

1. Iniciar Apache y MySQL en XAMPP para ejecutar la tienda.
2. Abrir http://localhost/Mvbarvie/ y pulsar «¿Necesitás ayuda?».
3. PHP debe tener habilitadas las extensiones `pdo_sqlite` y `mbstring`.
   Ambas se encontraron disponibles en el XAMPP utilizado para las pruebas.
4. Apache debe poder escribir en la carpeta `storage`.
   El archivo `storage/chat.db` y su tabla se crean automáticamente.

Usar Apache con soporte para `.htaccess`: `storage/.htaccess` impide descargar
la base de datos. El servidor integrado `php -S` no interpreta esa protección;
para usar otro servidor hay que bloquear `/storage/` en su configuración o
ubicar la base fuera de la carpeta pública y ajustar la ruta en el controlador.

## Archivos y responsabilidades

- `src/views/_layouts/chatbot.php`: componente visual compartido.
- `assets/css/chatbot.css`: presentación del chat.
- `assets/js/chatbot.js`: abrir, cerrar, cargar historial y enviar con `fetch`.
- `src/controllers/chatbot.php`: validación, reglas de respuesta y consultas PDO.
- `storage/.htaccess`: protección del historial frente al acceso web.
- `.gitignore`: evita incorporar conversaciones privadas al repositorio.

El ejemplo del apunte usa `SQLite3`; esta adaptación utiliza PDO SQLite, que
está disponible en este entorno. También corrige `$SESSION` por `$_SESSION`,
usa identificadores aleatorios y consultas preparadas tanto para leer como
para guardar mensajes. El navegador muestra los mensajes como texto, sin
ejecutar HTML enviado por el visitante.

## Recorrido de un mensaje

1. Al abrir el chat, JavaScript solicita el historial mediante GET.
2. PHP identifica al visitante por la sesión y devuelve sus últimos 100 mensajes
   y un token para validar los envíos.
3. El formulario envía el texto y el token mediante POST, sin recargar la página.
4. PHP valida el mensaje, normaliza mayúsculas y acentos y busca una regla.
5. Guarda el mensaje y la respuesta juntos en una transacción de SQLite.
6. JavaScript muestra ambos mensajes y desplaza la conversación hacia abajo.

La tabla `mensajes` contiene `id`, `chat_id`, `remitente`, `mensaje` y `fecha`.
Las fechas se guardan en UTC. Se conserva todo el historial, aunque la ventana
carga solamente los últimos 100 mensajes. La separación se hace por sesión,
no por cuenta: al perder o cerrar la sesión no se recupera automáticamente la
conversación anterior. El administrador local puede consultar la base con una
herramienta compatible con SQLite; no se agregó un panel público de historial.

## Guion breve para presentar el trabajo

1. Abrir el chat y escribir «Hola».
2. Pulsar «Catálogo» y mostrar la respuesta específica de la tienda.
3. Escribir «¿Cómo estás?» para mostrar el reconocimiento de acentos.
4. Escribir una consulta desconocida y mostrar la respuesta de orientación.
5. Recargar, abrir el chat y comprobar que los mensajes siguen presentes.
6. Abrir la tienda en una ventana privada: la conversación anterior no aparece.

Explicación para la exposición: «Integramos un chatbot de reglas al proyecto
anual. El navegador envía consultas con JavaScript; PHP las procesa y SQLite
guarda el historial por sesión. Es una base para un asistente virtual, que en
una etapa posterior podría conectarse a un modelo de inteligencia artificial».

## Verificación realizada

- Sintaxis PHP de los archivos agregados y los puntos de integración.
- Envío y respuesta reales en Apache y en el navegador.
- Historial persistente y separación entre sesiones.
- Rechazo de token inválido (403) y de mensajes vacíos (422).
- Acceso directo a `storage/chat.db` bloqueado por Apache (403).

El chat orienta sobre catálogo, carrito, registro y privacidad. No consulta
stock o precios en tiempo real ni inventa condiciones de envío o pago.
