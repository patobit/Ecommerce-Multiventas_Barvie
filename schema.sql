CREATE DATABASE multiventas;

USE multiventas;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    direccion VARCHAR(200)
);

CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255),

    -- Permite relacionar una categoría con su categoría padre
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

/*CREACION DE CATEGORIAS PRINCIPALES*/


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

/*SUBCATEGORIA DE DETAILING*/


INSERT INTO categorias
(nombre, descripcion, id_categoria_padre)
VALUES
(
    'Shampoos',
    'Shampoos para limpieza de vehículos',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Detailing')
),

(
    'Siliconas',
    'Siliconas y productos para interiores',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Detailing')
),

(
    'Jabones',
    'Jabones y limpiadores para vehículos',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Detailing')
),

(
    'Ceras',
    'Ceras para protección y brillo',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Detailing')
),

(
    'Pulidores',
    'Productos para pulido y restauración',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Detailing')
),

(
    'Desengrasantes',
    'Productos desengrasantes para vehículos',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Detailing')
),

(
    'Fragancias',
    'Fragancias para el interior de tu vehiculo',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Detailing')
),

(
    'Limpieza de interiores',
    'Productos para limpieza de tapizados y plásticos',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Detailing')
),

(
    'Cepillos',
    'cepillos de interior y exterior',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Detailing')
),

(
    'Paños',
    'Paños y toallas de microfibra',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Detailing')
),

(
    'kits de limpieza',
    'contiene paños, esponja, etc',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Detailing')
),

(
    'Varios',
    'Variedad de productos de detailing',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Detailing')
);

/*SUBCATEGORIA DE HERRAMIENTAS*/


INSERT INTO categorias
(nombre, descripcion, id_categoria_padre)
VALUES


(
    'Compresores',
    'Compresores de aire',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Herramientas')
),

(
    'calibres',
    'calibres para medir presion de neumaticos',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Herramientas')
),

(
    'discos de corte',
    'discos para moladora',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Herramientas')
),

(
    'Caballetes',
    'Caballetes para complementar tu criquet hidraulico ',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Herramientas')
),

(
    'Lingas',
    'Lingas',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Herramientas')
),

(
    'Llaves',
    'Llaves para mantenimiento y reparación',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Herramientas')
),

(
    'Aspiradoras',
    'aspiradoras para tu vehiculo',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Herramientas')
),

(
    'criquet hidraulico',
    'criquet para levantar vehículos',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Herramientas')
),


(
    'Hidro lavadoras',
    'Ideal para limpiar tu vehiculo',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Herramientas')
),

(
    'Compresores',
    'Compresores de aire',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Herramientas')
),


(
    'Varios',
    'variedad de productos de herramientas',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Herramientas')
);


/*SUBCATEGORIA DE ACCESORIOS*/

INSERT INTO categorias
(nombre, descripcion, id_categoria_padre)
VALUES
(
    'Fundas',
    'Fundas para vehículos',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Accesorios')
),

(
    'Alfombras',
    'Alfombras y pisos para vehículos',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Accesorios')
),

(
    'Soportes para celular',
    'Soportes para teléfonos móviles',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Accesorios')
),

(
    'Cargadores',
    'Cargadores y adaptadores para vehículos',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Accesorios')
),

(
    'Accesorios interiores',
    'Accesorios para el interior del vehículo',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Accesorios')
),

(
    'cubrevolantes',
    'cubrevolantes para tu vehiculo',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Accesorios')
),

(
    'Varios',
    'Varios productos de accesorios',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Accesorios')
);

/*SUB-CATEGORIA DE S.EMERGENCIA*/

INSERT INTO categorias
(nombre, descripcion, id_categoria_padre)
VALUES

(
    'Cierres de seguridad',
    'Sistemas y accesorios de seguridad',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Seguridad y Emergencia')
),

(
    'Botiquines',
    'Botiquines para vehículos',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Seguridad y Emergencia')
),

(
    'Balizas',
    'Balizas y elementos de señalización',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Seguridad y Emergencia')
),

(
    'Cables de batería',
    'Cables para arranque de batería',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Seguridad y Emergencia')
),

(
    'Varios',
    'Varios productos de seguridad y emergencia',
    (SELECT id_categoria FROM categorias WHERE nombre = 'Seguridad y Emergencia')
);
