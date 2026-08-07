# Proyecto Base - PDISC 7° Año 4° División

Este es un proyecto base en PHP pensado para que los alumnos trabajen con una aplicación simple de autenticación. Incluye:

- Un formulario de registro.
- Un formulario de login.
- Un área privada protegida por sesión.
- Conexión a base de datos usando PDO y configuración desde un archivo `.env`.

El objetivo es comprender cómo se organiza un proyecto PHP con separación de vistas, controladores y configuración.

## Requisitos previos

- PHP instalado (versión 7.4+ recomendada).
- Extensión `pdo_mysql` habilitada en `php.ini`.
- MySQL/MariaDB local o remoto para crear la base de datos.
- Editor de código (por ejemplo Visual Studio Code).

## Primeros pasos

1. Clonar este repositorio en tu computadora.
2. Abrir la carpeta del proyecto con Visual Studio Code.
3. Copiar `.env.example` y renombrarlo a `.env`.
4. Crear una base de datos y completar los valores de conexión en `.env`.
5. Si usas PHP integrado, ejecutar desde la raíz del proyecto:

   ```bash
   php -S localhost:8000
   ```

6. Abrir en el navegador:

   ```
   http://localhost:8000
   ```

## Base de datos

- Van a tener que abrir la terminal de mySQL
- Dentro del archivo `schema.sql` se van a encontrar con contenido que pueden pegar dentro de la terminal
- Asegurense de luego configurar los valores en el .env

## Estructura del repositorio

```text
.
├── .env.example
├── .htaccess
├── index.php
├── README.md
├── schema.sql
├── assets/
│   ├── css/
│   ├── img/
│   └── js/
└── src/
    ├── config/
    ├── controllers/
    └── views/
```

### Archivos principales

- `index.php`
  - Punto de entrada público del proyecto.
  - Redirige directamente a `src/views/index.php`.

- `.env.example`
  - Ejemplo de archivo de configuración para la conexión a la base de datos.
  - No debe contener datos reales.

- `schema.sql`
  - Contiene la definición de tablas necesarias para el proyecto.

## Responsabilidad de cada sección

- `assets/`: contiene archivos estáticos como CSS, JS e imágenes.
- `src/config/`: configuración global y conexión a la base de datos.
- `src/controllers/`: código de backend que procesa formularios y cambios de estado.
- `src/views/`: vistas HTML/PHP que se muestran al usuario.
- `src/views/_layouts/`: layouts compartidos que envuelven las páginas.

## Flujo inicial de la aplicación

1. El navegador entra por `index.php`.
2. `index.php` redirige a `src/views/index.php`.
3. `src/views/index.php` incluye el layout privado `src/views/_layouts/layout.php`.
4. `layout.php` verifica si el usuario está logueado:
   - Si no hay sesión, redirige a `src/views/auth/login.php`.
   - Si hay sesión, muestra el contenido privado.
5. Desde `src/views/auth/login.php`, el usuario intenta iniciar sesión.
6. Desde `src/views/auth/register.php`, el usuario puede crear una cuenta.
7. `src/controllers/auth/logout.php` cierra la sesión y vuelve al login.

## Notas para los alumnos

- El flujo de la aplicación está pensado para que primero se entienda la separación entre vista, controlador y configuración.
- Los datos sensibles deben guardarse en `.env`, nunca en el repositorio.