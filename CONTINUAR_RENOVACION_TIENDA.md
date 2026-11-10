# Continuar MVB — prioridad actual, 10/11/2026

## Pedido vigente del usuario
Antes de seguir con los 19 productos, renovar TODA la tienda: index, encabezado, catálogo, fichas y presentación general. Usar las fotos finales que YA están cargadas y logo aprobado. El usuario pidió guardar esta información para continuar desde otra PC.

## Producción
https://mvb.page.gd — 99 productos publicados, 597 fotos, todas las portadas primero, verificado 10/11. 19 fichas siguen en borrador. NO se publicó la renovación de la web en InfinityFree.
Pato todavía administra InfinityFree/FTP. Usuario confirmó que aún no tiene acceso. Hacer push a GitHub NO publica el frontend PHP de InfinityFree. No confundir esto con Render/Strapi.

## Trabajo iniciado, NO listo para publicar
Rama trabajo/renovacion-tienda-2026-11-10 contiene borrador de renovación general. Main conserva la versión anterior. La rama requiere revisar diff, probar PHP con servidor y revisar visualmente desktop y móvil antes de mezclar/publicar.
- Index usa portadas de productos publicados en vez de fotos viejas locales. Selección variada por categorías, accesos, bloque asesoramiento.
- Tarjetas con enlaces accesibles, fotos completas, tamaños de entrega Cloudinary, carga diferida, precio/stock sin cambios.
- Archivo nuevo assets/css/storefront.css, incluido en header y login. Es borrador: revisar cruces con style.css antiguo, grillas y navegación.
- Catálogo con ordenamiento por precio/nombre y mensaje si falla API.
- Detalle con fotos de entrega optimizadas y selector limitado por stock.
- Footer con accesos y logo aprobado; login sin comentarios visibles ni promesas de descuentos/compatibilidad automática.
- Cliente Strapi memoriza únicamente lecturas públicas durante UNA solicitud PHP. No cachea precios/stock en disco.
La sintaxis PHP pasó. NO se hicieron pruebas visuales ni funcionales de este borrador. Las transformaciones de Cloudinary aún deben comprobarse con solicitudes reales. No se modificó backend, datos, precios, stock, autenticación ni pagos.

## Siguiente paso
1. git fetch y git switch trabajo/renovacion-tienda-2026-11-10.
2. Leer este archivo, revisar cambios y ejecutar vista local con PHP+cURL (en PC original D:/apps/xampp/php/php.exe).
3. Probar inicio, búsqueda, categorías, ordenamiento, galería, stock, ingreso, carrito, escritorio y móvil; corregir antes de publicar.
4. Conseguir acceso autorizado a InfinityFree/FTP de Pato, respaldar archivos públicos y subir cambios del frontend. Verificar dominio. No migrar servidores por este pedido.
5. Después retomar productos pendientes.

## Productos/fotos pendientes
El usuario confirmó HOY que todavía no tiene fotos reales de estos 8: VA-021N, CP-004, SG-998, COT-007, FP-011, RE330, RE440, LL-013. Mantener borradores hasta tener referencias suficientes; no inventar piezas o variantes.
Se recuperaron referencias del proveedor para otros productos en output/pendientes-web-19 (API pública exacta codigoparticular=SKU).
LM-050: seis PNG generados e inspeccionados, guardados en output/LM-050-MVB. NO subidos a Drive ni Strapi y NO publicado. Ver continuidad.json. Conservar originales de generación. Revisar logos antes de subir; algunas salieron mayores al objetivo12%. No repetir las 99 series terminadas.

## Datos y acceso
GitHub guarda código; InfinityFree web PHP; Render Strapi; Neon datos; Cloudinary fotos; Drive fuentes y finales. Servidores funcionan con PC apagada. No hace falta copiarlos.
Los manifiestos privados están en el paquete de Drive. No subir tokens/.env/clientes al repositorio público. Token temporal anterior no se incluye en traslado; revocarlo al finalizar importación y usar acceso autorizado vigente para retomar.
Ver informe previo TESTING_MVB_2026-11-09.md: pagos/retorno y despertar Render requieren atención separada; no se hizo compra real.
