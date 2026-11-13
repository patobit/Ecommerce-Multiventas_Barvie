# Publicar la tienda corregida

Solo frontendPHP; no importar datos ni cambiar Render/Neon/Cloudinary.
1. Ingresar a InfinityFree/FTP autorizado de mvb.page.gd (Pato).
2. Respaldar los archivos actuales que se reemplazan en una carpeta privada fuera del directorio publico.
3. Descomprimir ACTUALIZAR-TIENDA-MVB-2026-11-13.zip. Copiar SOLO el contenido de web/ en la raiz publica que contiene index.php, conservando las rutas relativas. No subir informes/ a la web.
4. Mantener .env, configuracion del alojamiento, storage, sesiones y backend existentes. No borrar carpetas de productos, imagenes de Cloudinary ni base de datos.
5. Probar dominio en ventana privada: index3fotosactuales, carrusel12productos, Todoelcatalogo>Interior>Cubrevolantes, ficha/galeria, busqueda y orden, movil.
6. La jerarquia YA esta guardada en Strapi. No ejecutar una importacion de los paquetes anteriores: tienen la jerarquia anterior. SiPato trabaja en otra instancia debe actualizar sus datos de categorias con el estado actual.

El paquete no contiene credenciales. Se puede revertir el frontend restaurando los archivos respaldados, sin cambiar productos ni fotos.
