# Multiventas Barvie

Proyecto Final — PDISC 7° Año 4° División.

Integrantes: Autalan Patricio, Uriel Barvie, Priscila Perdomo y Naila Galarza.

Tienda PHP con catálogo y autenticación mediante Strapi y MySQL. Incluye un asistente con respuestas programadas e historial local en SQLite.

## Instalación del equipo

1. Clonar el proyecto en la carpeta `htdocs` de XAMPP e iniciar Apache y MySQL.
2. Usar PHP 8 con las extensiones cURL, pdo_sqlite y mbstring habilitadas. Apache debe permitir las reglas `.htaccess` del proyecto.
3. Instalar Node.js compatible con `mi-proyecto-strapi/package.json`. Esta instalación se verificó con Node 24.21.0 y Strapi 5.52.3. Conservar `package-lock.json` para instalar las mismas dependencias en el equipo.
4. Copiar `.env.example` a `.env` en la raíz. Para Strapi local, usar `STRAPI_URL=http://127.0.0.1:1337`.
5. Crear una base MySQL vacía para Strapi (por ejemplo, `mvbarvie_strapi`). Conservar la base anterior si contiene datos que todavía necesitan migrarse.
6. Copiar `mi-proyecto-strapi/.env.example` a `mi-proyecto-strapi/.env`. Configurar el nombre de la base y las credenciales de MySQL de cada computadora; generar los secretos propios que pide el ejemplo.
7. Desde la carpeta del proyecto, ejecutar:

   ```powershell
   cd mi-proyecto-strapi
   npm ci
   npm run build
   npm run develop
   ```

8. Abrir http://localhost:1337/admin y crear el administrador si es una instalación nueva. La cuenta administrativa es distinta de las cuentas de clientes de la tienda.
9. Revisar en Strapi los permisos de lectura de Producto y Categoria para los roles Public y Authenticated. No habilitar indiscriminadamente permisos para pedidos, carritos o usuarios.
10. Abrir http://localhost/Mvbarvie/ (adaptar el nombre si la carpeta local es diferente).

`schema.sql` corresponde a la base anterior de la aplicación: no es una exportación de Strapi. Para compartir también los productos y las imágenes hace falta transferir los datos y los medios de Strapi; Git comparte el código y los modelos, no el contenido de la base.

## Arranque habitual

Iniciar Apache y MySQL en XAMPP. Desde la raíz del proyecto:

```powershell
cd mi-proyecto-strapi
npm run develop
```

Mantener abierta esa terminal; Ctrl+C detiene Strapi. No hace falta reinstalar dependencias cada vez. Como alternativa, `npm run build` prepara el panel para `npm run start`; no ejecutar `develop` y `start` simultáneamente en el puerto 1337.

## Organización del código

- `src/config/database.php`: carga la configuración `.env`; no abre una conexión PDO a MySQL.
- `src/config/strapi_client.php`: comunicación HTTP con Strapi.
- `src/config/bootstrap.php`: inicio de sesión PHP y funciones compartidas de sesión.
- `src/controllers/auth/`: acciones de la tienda y autenticación.
- `src/views/`: páginas PHP; `_layouts` reúne las partes compartidas.
- `assets/css/style.css` y `assets/js/app.js`: estilos y comportamiento de la tienda, incluido el asistente.
- `mi-proyecto-strapi/`: servidor, modelos y configuración de Strapi. Conservar sus archivos de rutas, controladores y servicios, aunque algunos sean cortos: son parte de su estructura.

Los únicos archivos adicionales de la funcionalidad de chat y perfil que se mantienen separados son:

- `src/controllers/chatbot.php`: recibe las peticiones del chat y responde JSON.
- `src/views/_layouts/chatbot.php`: componente visual compartido entre varias páginas, para no copiar el mismo HTML.
- `src/views/user/profile.php`: página de perfil.
- `storage/.htaccess`: bloquea el acceso web al historial privado del chat.

## Asistente de la tienda

El asistente tiene respuestas programadas; no utiliza un servicio externo de inteligencia artificial. Ayuda con catálogo, carrito y registro. Guarda el historial por sesión en `storage/chat.db` y muestra los últimos 100 mensajes; ese límite de visualización no borra los anteriores.

PHP necesita permiso de escritura sobre `storage` y la extensión pdo_sqlite. Este uso de PDO corresponde únicamente a SQLite del chat; los datos de la tienda se consultan mediante Strapi. No publicar `chat.db` ni compartir conversaciones en Git. La protección de `storage/.htaccess` requiere Apache configurado para respetarla; el servidor integrado de PHP no aplica esas reglas.

Las peticiones de escritura usan un token de sesión y los mensajes se muestran como texto. Para comprobar el asistente: abrirlo, escribir un saludo, consultar una categoría, probar una consulta desconocida y recargar para ver el historial. Una sesión privada del navegador debe tener su propio historial.

## Archivos que se comparten

Subir el código PHP, CSS y JavaScript, modelos y configuración de Strapi, los ejemplos `.env.example`, `package.json`, `package-lock.json`, este README y los tipos generados que ya están versionados.

No subir `.env`, `mi-proyecto-strapi/.env`, `node_modules`, `build`, `.strapi`, registros locales ni `storage/chat.db`. Es normal que estas carpetas y archivos existan en cada computadora aunque no aparezcan en GitHub. Los secretos y contraseñas locales no tienen que ser iguales entre compañeros.

## Funcionalidades pendientes

- El formulario de perfil todavía no guarda modificaciones en Strapi.
- Carrito y compras necesitan autorización por propietario y una operación de compra transaccional antes de habilitar sus permisos generales en la API.
- El bloque de más vendidos consulta detalles de compra, cuya lectura pública no está habilitada.

Estos pendientes de la aplicación son independientes de la instalación de Strapi y de la consolidación de archivos.
