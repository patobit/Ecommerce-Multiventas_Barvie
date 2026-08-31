CREATE DATABASE multiventas;

USE multiventas;

CREATE TABLE usuarios (
id_usuario INT AUTO_INCREMENT PRIMARY KEY,
nombre VARCHAR(50) NOT NULL,
apellido VARCHAR(50) NOT NULL,
email VARCHAR(100) NOT NULL UNIQUE,
telefono VARCHAR(20),
direccion VARCHAR(200),
rol VARCHAR(50),
);

CREATE TABLE categorias (
id_categoria INT AUTO_INCREMENT PRIMARY KEY,
nombre VARCHAR(100) NOT NULL,
descripcion VARCHAR(255),

id_categoria_padre INT NULL,

CONSTRAINT fk_categoria_padre
    FOREIGN KEY (id_categoria_padre)
    REFERENCES categorias(id_categoria)

);

CREATE TABLE productos (
id_producto INT AUTO_INCREMENT PRIMARY KEY,
nombre VARCHAR(100) NOT NULL,
descripcion VARCHAR(255),
imagen VARCHAR(255),
stock INT NOT NULL DEFAULT 0,
precio DECIMAL(10,2) NOT NULL,

id_categoria INT NOT NULL,

CONSTRAINT fk_producto_categoria
    FOREIGN KEY (id_categoria)
    REFERENCES categorias(id_categoria)

);

CREATE TABLE compras (
id_compra INT AUTO_INCREMENT PRIMARY KEY,
fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
total DECIMAL(10,2) NOT NULL DEFAULT 0,

id_usuario INT NOT NULL,

CONSTRAINT fk_compra_usuario
    FOREIGN KEY (id_usuario)
    REFERENCES usuarios(id_usuario)

);

CREATE TABLE detalle_compra (
id_detalle_compra INT AUTO_INCREMENT PRIMARY KEY,

id_compra INT NOT NULL,
id_producto INT NOT NULL,

cantidad INT NOT NULL,
precio_unitario DECIMAL(10,2) NOT NULL,

CONSTRAINT fk_detalle_compra
    FOREIGN KEY (id_compra)
    REFERENCES compras(id_compra),

CONSTRAINT fk_detalle_producto
    FOREIGN KEY (id_producto)
    REFERENCES productos(id_producto)

);

CREATE TABLE envios (
id_envio INT AUTO_INCREMENT PRIMARY KEY,

estado VARCHAR(50) NOT NULL,
direccion_entrega VARCHAR(200) NOT NULL,
tracking_number VARCHAR(100),

id_compra INT NOT NULL UNIQUE,

CONSTRAINT fk_envio_compra
    FOREIGN KEY (id_compra)
    REFERENCES compras(id_compra)

);

CREATE TABLE carritos (
id_carrito INT AUTO_INCREMENT PRIMARY KEY,

id_usuario INT NOT NULL,

id_compra INT UNIQUE NULL,

id_envio INT UNIQUE NULL,

fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

estado VARCHAR(30) NOT NULL DEFAULT 'Activo',

CONSTRAINT fk_carrito_usuario
    FOREIGN KEY (id_usuario)
    REFERENCES usuarios(id_usuario),

CONSTRAINT fk_carrito_compra
    FOREIGN KEY (id_compra)
    REFERENCES compras(id_compra),

CONSTRAINT fk_carrito_envio
    FOREIGN KEY (id_envio)
    REFERENCES envios(id_envio)

);

CREATE TABLE detalle_carrito (
id_detalle_carrito INT AUTO_INCREMENT PRIMARY KEY,

id_carrito INT NOT NULL,
id_producto INT NOT NULL,

cantidad INT NOT NULL DEFAULT 1,

CONSTRAINT fk_detalle_carrito
    FOREIGN KEY (id_carrito)
    REFERENCES carritos(id_carrito),

CONSTRAINT fk_detalle_carrito_producto
    FOREIGN KEY (id_producto)
    REFERENCES productos(id_producto),

CONSTRAINT uq_carrito_producto
    UNIQUE (id_carrito, id_producto)

);

/* CREACION DE CATEGORIAS PRINCIPALES */

INSERT INTO categorias
(nombre, descripcion, id_categoria_padre)
VALUES
(
'Detailing',
'Productos para limpieza y cuidado de vehículos',
NULL
),
(
'Herramientas',
'Herramientas para mantenimiento y reparación',
NULL
),
(
'Accesorios',
'Accesorios y complementos para vehículos',
NULL
),
(
'Seguridad y Emergencia',
'Productos de seguridad y emergencia para vehículos',
NULL
);

/* SUBCATEGORIAS DE DETAILING */

INSERT INTO categorias
(nombre, descripcion, id_categoria_padre)
VALUES
(
'Shampoos',
'Shampoos para limpieza de vehículos',
1
),

(
'Siliconas',
'Siliconas y productos para interiores',
1
),

(
'Jabones',
'Jabones y limpiadores para vehículos',
1
),

(
'Ceras',
'Ceras para protección y brillo',
1
),

(
'Pulidores',
'Productos para pulido y restauración',
1
),

(
'Desengrasantes',
'Productos desengrasantes para vehículos',
1
),

(
'Fragancias',
'Fragancias para el interior de tu vehiculo',
1
),

(
'Limpieza de interiores',
'Productos para limpieza de tapizados y plásticos',
1
),

(
'Cepillos',
'Cepillos de interior y exterior',
1
),

(
'Paños',
'Paños y toallas de microfibra',
1
),

(
'Kits de limpieza',
'Contiene paños, esponja, etc.',
1
),
(
'Varios',
'Variedad de productos de detailing',
1
);

/* SUBCATEGORIAS DE HERRAMIENTAS */

INSERT INTO categorias
(nombre, descripcion, id_categoria_padre)
VALUES
(
'Compresores',
'Compresores de aire',
2
),
(
'Calibres',
'Calibres para medir presion de neumaticos',
2
),
(
'Discos de corte',
'Discos para moladora',
2
),
(
'Caballetes',
'Caballetes para complementar tu criquet hidraulico',
2
),
(
'Lingas',
'Lingas',
2
),
(
'Llaves',
'Llaves para mantenimiento y reparación',
2
),
(
'Aspiradoras',
'Aspiradoras para tu vehiculo',
2
),
(
'Criquet hidraulico',
'Criquet para levantar vehículos',
2
),
(
'Hidro lavadoras',
'Ideal para limpiar tu vehiculo',
2
),
(
'Varios',
'Variedad de productos de herramientas',
2
);

/* SUBCATEGORIAS DE ACCESORIOS */

INSERT INTO categorias
(nombre, descripcion, id_categoria_padre)
VALUES
(
'Fundas',
'Fundas para vehículos',
3
),
(
'Alfombras',
'Alfombras y pisos para vehículos',
3
),
(
'Soportes para celular',
'Soportes para teléfonos móviles',
3
),
(
'Cargadores',
'Cargadores y adaptadores para vehículos',
3
),
(
'Accesorios interiores',
'Accesorios para el interior del vehículo',
3
),
(
'Cubrevolantes',
'Cubrevolantes para tu vehiculo',
3
),
(
'Varios',
'Varios productos de accesorios',
3
);

/* SUBCATEGORIAS DE SEGURIDAD Y EMERGENCIA */

INSERT INTO categorias
(nombre, descripcion, id_categoria_padre)
VALUES
(
'Cierres de seguridad',
'Sistemas y accesorios de seguridad',
4
),
(
'Botiquines',
'Botiquines para vehículos',
4
),
(
'Balizas',
'Balizas y elementos de señalización',
4
),
(
'Cables de batería',
'Cables para arranque de batería',
4
),
(
'Varios',
'Varios productos de seguridad y emergencia',
4
);
