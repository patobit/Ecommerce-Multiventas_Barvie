# Testing MVB — cierre13/11/2026

## Resultado y alcance
99fichas HTTP comprobadas en la version PHP local corregida:99 correctas. Todas tienen titulo, precio, galeria completa en el orden de Strapi, cantidad maxima vinculada al stock y ruta de categorias. Sin errores PHP detectados. No significa que el nuevo frontend ya este publicado en InfinityFree.
597/597URLs originales de Cloudinary respondieron200 con Content-Type de imagen. Las tres portadas del index ahora salen de Strapi y se entregan optimizadas; ya no referencian fotos antiguas locales. Carrusel con12productos publicados y stock, controles probados.

## Jerarquia guardada en produccion
Cinco entradas con productos: Interior41, Exterior9, Detailing36, Herramientas9, Seguridad y transporte4. Total99.22categorias en Strapi:5nuevos padres,16anteriores conservadas como hojas salvo Recreacion sin productos publicados, y nueva Antenas. Separado mastil de antena de Audio. Precios, stock, textos y galerias de99productos contrastados antes/despues: conservados.
Interfaz corregida muestra categorias principales, luego solo las hijas del nivel elegido y despues productos. Ejemplo Todo el catalogo > Interior > Cubrevolantes > producto. Boton para ver todos los productos de una rama, migas de pan y regreso. Comprobadas las5ramas con sus cantidades, ausencia de ciclos y todos los productos categorizados. Filtros por descendientes y busqueda global correctos.

## Pruebas manuales
- PC y390px: inicio con3fotosactuales, carrusel12tarjetas, sin desborde horizontal una vez cargado.
- Menu movil abre/cierra; categorias principales y navegacion Interior>Cubrevolantes15productos.
- Buscar manopla desde una categoria busca globalmente y devuelve2. Orden mayor precio:9600,4500.
- Ficha manopla:7fotos, flecha y miniatura7 funcionan, una unica foto visible.
- Corregido ingreso manual de cantidad:999 se normaliza a5 con stock5;0 a1; boton+ respeta maximo. El boton de compra tambien limita antes de enviar.
- Agregar sin sesion deriva a login; no se creo pedido ni se hizo pago.
- Enlaces de categoria anteriores tienen alias por documentId (ej.categoria12 sigue mostrando Electricidad y fusibles). Se agrego respaldo similar para enlaces de productos cuyo ID numerico cambio al republicar.
- Categoria inexistente y producto inexistente devuelven404.
- Render dormido: reproducido timeout; la interfaz avisa que tarda y ofrece reintentar. Reintento con servicio disponible cargo2manoplas. El planFree sigue pudiendo demorar; no se contrato otroplan.
- Sintaxis de PHP y JavaScript comprobada. Login sin comentarios de codigo visibles.

## Publicacion pendiente
El dominio mvb.page.gd conserva el frontend anterior. Pato administra InfinityFree y no se tiene acceso FTP. Subir el paquete ACTUALIZAR-TIENDA-MVB-2026-11-13.zip respaldando primero los archivos reemplazados. No importar Strapi ni reemplazar datos para publicar el frontend. Repetir recorrido en el dominio despues de subir.
Los datos de categorias en Strapi SI se actualizaron; el codigo/frontend NO se publico en InfinityFree.

## Pendientes fuera de este arreglo
19borradores siguen pendientes de fotos. Retorno de MercadoPago404 y ciclo completo de pago/pedido/stock requieren revision y prueba separadas (informe9/11). No se certifica checkout ni compatibilidad fisica de cada producto por esta auditoria.
Las exportaciones Strapi entregadas aPato antes de este arreglo tienen el arbol anterior: no importarlas sobre la produccion actual, porque desharian estos cambios. Snapshot de categorias actual y scripts de registro se conservaron en la carpeta de continuidad privada.
