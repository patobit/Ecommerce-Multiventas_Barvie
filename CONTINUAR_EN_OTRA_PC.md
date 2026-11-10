# Continuar MVB desde otra computadora

La tienda pública está en https://mvb.page.gd y el catálogo en
https://mvb.page.gd/src/views/catalogo.php.

## Qué guarda cada servicio

- GitHub: código de la tienda y de Strapi.
- InfinityFree: archivos PHP, estilos y logo de la tienda pública. Pato administra este alojamiento.
- Render: ejecuta Strapi en https://mvb-0ina.onrender.com/admin.
- Neon: base de datos de Strapi.
- Cloudinary: fotos de los productos, independientes del disco temporal de Render.
- Google Drive: fotos finales, fuentes y registro de continuidad.

Los servicios funcionan sin mantener encendida la computadora de desarrollo.
Descargar el código no descarga la base de datos ni las cuentas de los servicios.
Cada computadora debe iniciar sesión con su propio acceso autorizado.

## Descargar el proyecto

Repositorio: https://github.com/patobit/Ecommerce-Multiventas_Barvie

Usar GitHub **Code → Download ZIP**, o clonar el repositorio con Git.
Para trabajar con la tienda local se necesita PHP con cURL. La conexión de productos
apunta al Strapi de Render si no se define otra `STRAPI_URL`.
No es necesario instalar ni ejecutar un Strapi local para consultar el catálogo remoto.

## Estado verificado el 9 de noviembre de 2026

118 fichas creadas: 99 publicadas con 597 fotos y 19 borradores pendientes de fotos.
Las 99 galerías empiezan por su portada. Precios, stock, categorías y fotos fueron
contrastados con los registros de importación.

El logo aprobado está preparado en `assets/img/mvb-logo-aprobado.png`, con el
encabezado y estilos actualizados en este repositorio. Publicarlo en GitHub no
reemplaza automáticamente los archivos de InfinityFree: hay que subir el cambio
al alojamiento para verlo en el dominio.

Pendientes de revisión: retorno de Mercado Pago que da 404, selector público que
permite superar stock, tratamiento de demoras de Strapi y comentarios visibles
en la pantalla de ingreso. El informe detallado y los registros privados de
importación se conservan en el paquete de continuidad de Drive.

No subir contraseñas, archivos `.env`, tokens, copias de sesiones ni bases de datos
con información de clientes a este repositorio público.
