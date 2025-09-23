CREATE DATABASE IF NOT EXISTS trucksisx;
USE trucksisx;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    num_documento VARCHAR(20) NOT NULL,
    tipo_documento VARCHAR(20) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    num_celular VARCHAR(20),
    correo VARCHAR(100),
    rol ENUM('admin','tecnico','conductor') NOT NULL,
    contrasena VARCHAR(255) NOT NULL
);

CREATE TABLE cat_vehic (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

CREATE TABLE subcat_vehic (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    cat_vehic_id INT,
    FOREIGN KEY (cat_vehic_id) REFERENCES cat_vehic(id)
);

CREATE TABLE regis_vehic (
    id INT AUTO_INCREMENT PRIMARY KEY,
    num_cha VARCHAR(50),
    placa VARCHAR(20),
    distru_ejes VARCHAR(20),
    marca_vehiculo VARCHAR(50),
    modelo VARCHAR(50),
    color VARCHAR(30),
    cilindraje VARCHAR(20),
    cap_carga VARCHAR(20),
    linea_marca VARCHAR(50),
    tecnomecanica VARCHAR(50),
    soat VARCHAR(50),
    tipo_unidad VARCHAR(30),
    tipo_combustible VARCHAR(30),
    RUNT VARCHAR(50),
    cert_homologacion VARCHAR(50),
    cert_matricula VARCHAR(50),
    tarje_propiedad VARCHAR(50),
    subcat_vehic_id INT,
    FOREIGN KEY (subcat_vehic_id) REFERENCES subcat_vehic(id)
);

CREATE TABLE cond (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cargo VARCHAR(50),
    horas_trabajadas INT,
    tareas_completadas INT,
    efeciencia DECIMAL(5,2),
    descripcion TEXT,
    regis_vehic_id INT,
    FOREIGN KEY (regis_vehic_id) REFERENCES regis_vehic(id)
);

CREATE TABLE repor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_reporte VARCHAR(100),
    tipo_reporte VARCHAR(50),
    costo_individual_vehiculo DECIMAL(10,2),
    frecuencia VARCHAR(50),
    fecha_creacion DATE,
    activo BOOLEAN
);

CREATE TABLE cat_repu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_repuesto VARCHAR(50),
    nombre VARCHAR(100) NOT NULL,
    caracteristicas VARCHAR(255)
    
);

CREATE TABLE subcat_repu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_sub_repuesto VARCHAR(50),
    nombre VARCHAR(100) NOT NULL,
    caracteristicas VARCHAR(255),
    cat_repu_id INT,
    FOREIGN KEY (cat_repu_id) REFERENCES cat_repu(id)
);

CREATE TABLE proveedor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nit_num_identi VARCHAR(50),
    nom_proveedor VARCHAR(100),
    tel_contacto VARCHAR(30),
    carg_contacto VARCHAR(50),
    correo VARCHAR(100),
    direccion VARCHAR(100),
    ciudad_depar VARCHAR(50),
    pais VARCHAR(50),
    tip_repuesto VARCHAR(50),
    mar_distribuye VARCHAR(100),
    tiem_entrega VARCHAR(50),
    zon_cobertura VARCHAR(100),
    for_pago VARCHAR(50),
    cred_disponible VARCHAR(50),
    cuen_bancaria VARCHAR(50),
    FOREIGN KEY (cat_repu_id) REFERENCES cat_repu(id)
);

CREATE TABLE repue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    marca_repuesto VARCHAR(100),
    proveedor_id INT,
    cat_repu_id INT,
    subcat_repu_id INT,
    modelo VARCHAR(100),
    medidas_espe VARCHAR(100),
    norma_estan VARCHAR(100),
    numero_parte VARCHAR(100),
    des_tecnica TEXT,
    veh_compatible VARCHAR(100),
    cantidad INT,
    estado_repus VARCHAR(50),
    fecha_ingreso DATE,
    num_factura VARCHAR(50),
    ubi_almacen VARCHAR(100),
    pre_unitario DECIMAL(10,2),
    costo_total DECIMAL(10,2),
    garantia VARCHAR(100),
    res_ingreso VARCHAR(100),
    cant_stock INT,
    fecha_venci DATE,
    dest_area VARCHAR(100),
    firma_verificacion VARCHAR(100),
    FOREIGN KEY (cat_repu_id) REFERENCES cat_repu(id),
    FOREIGN KEY (subcat_repu_id) REFERENCES subcat_repu(id),
    FOREIGN KEY (proveedor_id) REFERENCES proveedor(id)
);

CREATE TABLE alert (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME,
    prioridad VARCHAR(20),
    estado VARCHAR(20),
    descripcion TEXT
);

CREATE TABLE ord_trabj (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_trabajo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    nombre_repuesto VARCHAR(100),
    fecha_creacion DATE,
    fecha_estimada DATE,
    estado VARCHAR(30),
    prioridad VARCHAR(20),
    cond_id INT,
    users_id INT,
    alert_id INT,
    FOREIGN KEY (cond_id) REFERENCES cond(id),
    FOREIGN KEY (users_id) REFERENCES users(id),
    FOREIGN KEY (alert_id) REFERENCES alert(id)
);

CREATE TABLE sali_repue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha_salida DATE,
    cantidad INT,
    repue_id INT,
    ord_trabj_id INT,
    repor_id INT,
    FOREIGN KEY (repue_id) REFERENCES repue(id),
    FOREIGN KEY (ord_trabj_id) REFERENCES ord_trabj(id),
    FOREIGN KEY (repor_id) REFERENCES repor(id)
);


-- Relación adicional: cond también se relaciona con repor (ya definida)
-- Relación adicional: sali_repue se conecta con ord_trabj y repor (ya definida)

-- Usuarios iniciales con contraseñas encriptadas (ejemplo usando SHA2)
INSERT INTO users (num_documento, tipo_documento, nombre, apellido, num_celular, correo, rol, contrasena) VALUES
('1001', 'CC', 'Admin', 'Principal', '3000000000', 'admin@trucksisx.com', 'admin', SHA2('admin123',256)),
('1002', 'CC', 'Tecnico', 'Soporte', '3000000001', 'tecnico@trucksisx.com', 'tecnico', SHA2('tecn123',256)),
('1003', 'CC', 'Conductor', 'Operador', '3000000002', 'conduc@trucksisx.com', 'conductor', SHA2('conduc123',256));

-- Fin de la estructura
