CREATE DATABASE IF NOT EXISTS trucksisx;
USE trucksisx;

-- Usuarios
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

-- Categoría y subcategoría de vehículos
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

-- Registro de vehículos
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
    cond_id INT,
    estado ENUM('Sin conductor','Asignado') DEFAULT 'Sin conductor'
);

    -- Conductores
    CREATE TABLE cond (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cargo VARCHAR(50),
        horas_trabajadas INT,
        tareas_completadas INT,
        efeciencia DECIMAL(5,2),
        descripcion TEXT,
        regis_vehic_id INT
    );

    -- Agregar claves foráneas después de crear ambas tablas
    ALTER TABLE regis_vehic
        ADD FOREIGN KEY (subcat_vehic_id) REFERENCES subcat_vehic(id);
    ALTER TABLE regis_vehic
        ADD FOREIGN KEY (cond_id) REFERENCES cond(id);
    ALTER TABLE cond
        ADD FOREIGN KEY (regis_vehic_id) REFERENCES regis_vehic(id);

-- Categoría y subcategoría de repuestos
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

-- Proveedores
CREATE TABLE proveedor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nit_num_identi DECIMAL(20,0),
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
    cuen_bancaria VARCHAR(50)
);

-- Repuestos
CREATE TABLE repue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    marca_repuesto VARCHAR(100),
    proveedor_id INT NULL,
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
    FOREIGN KEY (proveedor_id) REFERENCES proveedor(id) ON DELETE SET NULL
);

-- Órdenes de trabajo
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
    FOREIGN KEY (users_id) REFERENCES users(id)
    -- FOREIGN KEY (alert_id) se agrega después de crear alert
);

-- Alertas
CREATE TABLE alert (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    prioridad ENUM('baja','media','alta','critica') DEFAULT 'media',
    estado ENUM('activa','en_proceso','resuelta','cancelada') DEFAULT 'activa',
    descripcion TEXT,
    tipo_alerta ENUM('llanta','motor','frenos','general') DEFAULT 'general',
    posicion_llanta ENUM(
        'direccion_izquierda','direccion_derecha',
        'traccion1_izquierda','traccion1_derecha',
        'traccion1_izquierda2','traccion1_derecha2',
        'traccion2_izquierda','traccion2_derecha'
    ),
    codigo_conductor VARCHAR(20),
    observaciones TEXT,
    imagen_evidencia VARCHAR(255),
    ord_trabj_id INT,
    cond_id INT,
    regis_vehic_id INT,
    FOREIGN KEY (ord_trabj_id) REFERENCES ord_trabj(id),
    FOREIGN KEY (cond_id) REFERENCES cond(id),
    FOREIGN KEY (regis_vehic_id) REFERENCES regis_vehic(id)
);

-- Ahora sí, agregamos la relación circular de alert en ord_trabj
ALTER TABLE ord_trabj
    ADD FOREIGN KEY (alert_id) REFERENCES alert(id);

-- Salida de repuestos
CREATE TABLE sali_repue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha_salida DATE,
    cantidad INT,
    repue_id INT,
    ord_trabj_id INT,
    repor_id INT,
    alerta_id INT,
    sali_vehi_id INT,
    FOREIGN KEY (repue_id) REFERENCES repue(id),
    FOREIGN KEY (ord_trabj_id) REFERENCES ord_trabj(id)
    -- FOREIGN KEY (repor_id) se agrega después de crear repor
    -- FOREIGN KEY (alerta_id) se agrega después de crear alert
    -- FOREIGN KEY (sali_vehi_id) se agrega después de crear sali_vehi
);

-- Salida de vehículos
CREATE TABLE sali_vehi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_flotas INT,
    segui_monitoreo VARCHAR(100),
    control_combustible VARCHAR(100),
    cump_regulaciones VARCHAR(100),
    protocolo_seguridad VARCHAR(100),
    gest_conductores VARCHAR(100),
    repor_id INT,
    ord_trabj_id INT,
    alerta_id INT,
    sali_repue_id INT,
    FOREIGN KEY (ord_trabj_id) REFERENCES ord_trabj(id)
    -- FOREIGN KEY (repor_id) se agrega después de crear repor
    -- FOREIGN KEY (alerta_id) se agrega después de crear alert
    -- FOREIGN KEY (sali_repue_id) se agrega después de crear sali_repue
);

-- Reportes
CREATE TABLE repor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_reporte VARCHAR(100),
    tipo_reporte VARCHAR(50),
    costo_individual_vehiculo DECIMAL(10,2),
    frecuencia VARCHAR(50),
    fecha_creacion DATE,
    activo BOOLEAN,
    sali_repue_id INT,
    sali_vehi_id INT,
    FOREIGN KEY (sali_repue_id) REFERENCES sali_repue(id),
    FOREIGN KEY (sali_vehi_id) REFERENCES sali_vehi(id)
);

-- Ahora sí, agregamos las relaciones circulares en sali_repue y sali_vehi
ALTER TABLE sali_repue
    ADD FOREIGN KEY (repor_id) REFERENCES repor(id);

ALTER TABLE sali_vehi
    ADD FOREIGN KEY (repor_id) REFERENCES repor(id);

-- Relaciones nuevas para mejoras
ALTER TABLE sali_repue
    ADD FOREIGN KEY (alerta_id) REFERENCES alert(id),
    ADD FOREIGN KEY (sali_vehi_id) REFERENCES sali_vehi(id);

ALTER TABLE sali_vehi
    ADD FOREIGN KEY (alerta_id) REFERENCES alert(id),
    ADD FOREIGN KEY (sali_repue_id) REFERENCES sali_repue(id);



-- Usuarios iniciales con contraseñas encriptadas (ejemplo usando SHA2)
INSERT INTO users (num_documento, tipo_documento, nombre, apellido, num_celular, correo, rol, contrasena) VALUES
('1001', 'CC', 'Admin', 'Principal', '3000000000', 'admin@trucksisx.com', 'admin', SHA2('admin123',256)),
('1002', 'CC', 'Tecnico', 'Soporte', '3000000001', 'tecnico@trucksisx.com', 'tecnico', SHA2('tecn123',256)),
('1003', 'CC', 'Conductor', 'Operador', '3000000002', 'conduc@trucksisx.com', 'conductor', SHA2('conduc123',256));

-- --------------------------------------------------
-- Datos de ejemplo (5 registros por tabla excepto users)
-- --------------------------------------------------

-- Categorías de vehículos (5)
INSERT INTO cat_vehic (nombre) VALUES
('Volquetas'),
('Camiones'),
('Dobletroque'),
('Tractores'),
('Furgones');

-- Subcategorías de vehículos (5)
INSERT INTO subcat_vehic (nombre, cat_vehic_id) VALUES
('Volqueta 10m3', 1),
('Camión Mediano 10T', 2),
('Doble Troque 4x2', 3),
('Tractor 6x4', 4),
('Furgón Refrigerado', 5);

-- Proveedores (5)
INSERT INTO proveedor (nit_num_identi, nom_proveedor, tel_contacto, carg_contacto, correo, direccion, ciudad_depar, pais, tip_repuesto, mar_distribuye, tiem_entrega, zon_cobertura, for_pago, cred_disponible, cuen_bancaria) VALUES
(900100200,'Repuestos SAS','601-5550101','Carlos Ruiz','ventas@repuestossas.com','Calle 10 #20-30','Bogotá','Colombia','Frenos','MarcaA','3 días','Nacional','Transferencia','50M','123456789'),
(900200300,'AutoParts Ltda','601-5550202','Laura Gómez','info@autoparts.local','Carrera 5 #10-20','Medellín','Colombia','Motor','MarcaB','5 días','Regional','Crédito','20M','987654321'),
(900300400,'Distribuciones XYZ','601-5550303','Andrés López','contacto@xyz.com','Av. 1 #1-01','Cali','Colombia','Neumáticos','MarcaC','7 días','Nacional','Contado','30M','1122334455'),
(900400500,'Suministros LTDA','601-5550404','Marta Ruiz','ventas@suministros.com','Cll 50 #40-10','Barranquilla','Colombia','Eléctricos','MarcaD','4 días','Regional','Transferencia','10M','5566778899'),
(900500600,'GlobalParts','601-5550505','Pedro Martín','global@parts.com','Zona Industrial','Bucaramanga','Colombia','Hidráulicos','MarcaE','6 días','Internacional','Crédito','100M','6677889900');

-- Categorías de repuestos (5)
INSERT INTO cat_repu (tipo_repuesto, nombre, caracteristicas) VALUES
('Freno','Frenos','Componentes para sistema de frenado'),
('Motor','Motor','Piezas del motor'),
('Neumático','Neumáticos','Llantas y cámaras'),
('Eléctrico','Eléctricos','Componentes eléctricos y sensores'),
('Hidráulico','Hidráulicos','Bombas y mangueras');

-- Subcategorías de repuestos (5)
INSERT INTO subcat_repu (tipo_sub_repuesto, nombre, caracteristicas, cat_repu_id) VALUES
('Pastillas','Pastillas freno','Pastillas cerámicas',1),
('Filtros','Filtros aceite','Filtro para motores diesel',2),
('Llantas','Llantas 22.5','Llantas reforzadas',3),
('Sensores','Sensores velocidad','Sensor ABS',4),
('Bombas','Bombas agua','Bomba de agua motor',5);

-- Repuestos (5)
INSERT INTO repue (nombre, marca_repuesto, proveedor_id, cat_repu_id, subcat_repu_id, modelo, medidas_espe, norma_estan, numero_parte, des_tecnica, veh_compatible, cantidad, estado_repus, fecha_ingreso, num_factura, ubi_almacen, pre_unitario, costo_total, garantia, res_ingreso, cant_stock, fecha_venci, dest_area, firma_verificacion) VALUES
('Pastilla Freno Delantera','MarcaA',1,1,1,'V1','100x50','ISO9001','PF-001','Pastilla para eje delantero','HINO,ISUZU',120,'Disponible','2025-01-01','FAC-001','Alm-A',45.50,5460.00,'6 meses','Compra2025',120,'2027-01-01','Taller','Admin'),
('Filtro Aceite MWM','MarcaB',2,2,2,'M2','30x20','ISO14001','FA-002','Filtro aceite para motor MWM','HINO,VOLVO',60,'Disponible','2025-02-15','FAC-002','Alm-B',12.75,765.00,'12 meses','Compra2025',60,'2026-02-15','Almacén','Admin'),
('Llanta 22.5 / 16PR','MarcaC',3,3,3,'L22','22.5','DOT','LL-003','Llanta reforzada para doble troque','VOLVO,SCANIA',40,'Disponible','2025-03-10','FAC-003','Alm-C',220.00,8800.00,'24 meses','Compra2025',40,'2028-03-10','Neumáticos','Admin'),
('Bomba Agua','MarcaE',5,5,5,'B1','N/A','OEM','BA-004','Bomba de agua para motor 6 cilindros','MERCEDES,HINO',15,'Disponible','2025-04-05','FAC-004','Alm-D',150.00,2250.00,'12 meses','Compra2025',15,'2027-04-05','Taller','Admin'),
('Sensor ABS','MarcaD',4,4,4,'S-ABS','N/A','ISO9001','SN-005','Sensor de velocidad ABS','VARIOS',25,'Disponible','2025-05-20','FAC-005','Alm-E',85.00,2125.00,'18 meses','Compra2025',25,'2027-05-20','Eléctrico','Admin');

-- Vehículos (5) -- dejar cond_id NULL inicialmente (se asignará luego)
INSERT INTO regis_vehic (num_cha, placa, distru_ejes, marca_vehiculo, modelo, color, cilindraje, cap_carga, linea_marca, tecnomecanica, soat, tipo_unidad, tipo_combustible, RUNT, cert_homologacion, cert_matricula, tarje_propiedad, subcat_vehic_id, cond_id, estado) VALUES
('CHASSIS-A1','ABC-101','doble','HINO','500','Blanco','6000cc','12T','Serie A','2025-06-10','2025-06-10','Camión','Diesel','RUNT-A1','HOMO-A1','MAT-A1','TARJ-A1',1,NULL,'Sin conductor'),
('CHASSIS-A2','DEF-202','simple','ISUZU','NQR','Rojo','4500cc','7T','Serie B','2024-12-20','2024-12-20','Camión','Diesel','RUNT-A2','HOMO-A2','MAT-A2','TARJ-A2',2,NULL,'Sin conductor'),
('CHASSIS-A3','GHI-303','doble','VOLVO','FMX','Azul','7000cc','15T','Serie C','2025-01-15','2025-01-15','Camión','Diesel','RUNT-A3','HOMO-A3','MAT-A3','TARJ-A3',3,NULL,'Sin conductor'),
('CHASSIS-A4','JKL-404','simple','MERCEDES','Atego','Blanco','5200cc','9T','Serie D','2024-11-30','2024-11-30','Camión','Diesel','RUNT-A4','HOMO-A4','MAT-A4','TARJ-A4',4,NULL,'Sin conductor'),
('CHASSIS-A5','MNO-505','doble','SCANIA','P360','Gris','6500cc','13T','Serie E','2025-03-22','2025-03-22','Camión','Diesel','RUNT-A5','HOMO-A5','MAT-A5','TARJ-A5',5,NULL,'Sin conductor');

-- Conductores (5) linking to vehicles via regis_vehic_id
INSERT INTO cond (cargo, horas_trabajadas, tareas_completadas, efeciencia, descripcion, regis_vehic_id) VALUES
('Conductor Senior',1200,480,95.50,'Experto en rutas largas',1),
('Conductor Junior',400,120,80.00,'Rutas urbanas',2),
('Conductor',800,300,87.50,'Cobertura regional',3),
('Conductor Nuev',200,60,75.00,'Nueva incorporación',4),
('Conductor Senior 2',950,360,90.00,'Especialista en mantenimiento',5);

-- Actualizar regis_vehic para asignar cond_id ahora que existen conductores
UPDATE regis_vehic SET cond_id = 1 WHERE id = 1;
UPDATE regis_vehic SET cond_id = 2 WHERE id = 2;
UPDATE regis_vehic SET cond_id = 3 WHERE id = 3;
UPDATE regis_vehic SET cond_id = 4 WHERE id = 4;
UPDATE regis_vehic SET cond_id = 5 WHERE id = 5;

-- Órdenes de trabajo (5) - dejar alert_id NULL inicialmente
INSERT INTO ord_trabj (nombre_trabajo, descripcion, nombre_repuesto, fecha_creacion, fecha_estimada, estado, prioridad, cond_id, users_id, alert_id) VALUES
('Revisión frenos','Cambio de pastillas y ajuste','Pastilla Freno Delantera','2025-09-01','2025-09-03','pendiente','alta',1,1,NULL),
('Cambio filtro aceite','Sustitución filtro aceite','Filtro Aceite MWM','2025-08-20','2025-08-22','en_proceso','media',2,2,NULL),
('Revisión llantas','Rotación y ajuste','Llanta 22.5 / 16PR','2025-07-15','2025-07-16','resuelta','alta',3,1,NULL),
('Instalación bomba agua','Sustitución bomba y pruebas','Bomba Agua','2025-09-10','2025-09-12','pendiente','media',4,2,NULL),
('Verificación documentos','Actualización certificados','Certificado Homologación','2025-09-05','2025-09-07','pendiente','baja',5,3,NULL);

-- Alertas (5) referencing ord_trabj, cond and regis_vehic
INSERT INTO alert (fecha_hora, prioridad, estado, descripcion, tipo_alerta, posicion_llanta, codigo_conductor, observaciones, imagen_evidencia, ord_trabj_id, cond_id, regis_vehic_id) VALUES
('2025-09-01 09:20:00','alta','activa','Temperatura anormal en freno delantero','frenos',NULL,'C-001','Revisar conjunto delantero',NULL,1,1,1),
('2025-08-21 10:45:00','media','activa','Consumo excesivo de aceite detectado','motor',NULL,'C-002','Requiere diagnóstico',NULL,2,2,2),
('2025-07-14 07:50:00','baja','resuelta','Presión baja en eje trasero','llanta','traccion1_izquierda','C-003','Inflado y verificado',NULL,3,3,3),
('2025-09-02 12:30:00','media','activa','SOAT próximo a vencer','general',NULL,'C-004','Notificar a oficina',NULL,4,4,4),
('2025-09-03 15:00:00','alta','activa','Fallo en sensor de velocidad','general',NULL,'C-005','Sensor intermitente',NULL,5,5,5);

-- Actualizar ord_trabj para enlazar alert_id con las alertas creadas
UPDATE ord_trabj SET alert_id = 1 WHERE id = 1;
UPDATE ord_trabj SET alert_id = 2 WHERE id = 2;
UPDATE ord_trabj SET alert_id = 3 WHERE id = 3;
UPDATE ord_trabj SET alert_id = 4 WHERE id = 4;
UPDATE ord_trabj SET alert_id = 5 WHERE id = 5;

-- Reportes (5) - inicialmente sin relaciones cruzadas
INSERT INTO repor (nombre_reporte, tipo_reporte, costo_individual_vehiculo, frecuencia, fecha_creacion, activo, sali_repue_id, sali_vehi_id) VALUES
('Inventario Mensual','Inventario',0.00,'mensual','2025-09-01',1,NULL,NULL),
('Salidas Diarias','Salidas',0.00,'diaria','2025-09-01',1,NULL,NULL),
('Costos Mantenimiento','Costos',0.00,'mensual','2025-09-01',1,NULL,NULL),
('Eficiencia Conductores','Operacional',0.00,'trimestral','2025-09-01',1,NULL,NULL),
('Reportes Alertas','Alertas',0.00,'semanal','2025-09-01',1,NULL,NULL);

-- Salidas de repuestos (5)
INSERT INTO sali_repue (fecha_salida, cantidad, repue_id, ord_trabj_id, repor_id, alerta_id, sali_vehi_id) VALUES
('2025-09-01',2,1,1,NULL,1,NULL),
('2025-08-21',1,2,2,NULL,2,NULL),
('2025-07-15',4,3,3,NULL,3,NULL),
('2025-09-10',1,4,4,NULL,4,NULL),
('2025-09-05',1,5,5,NULL,5,NULL);

-- Salidas de vehículos (5)
INSERT INTO sali_vehi (id_flotas, segui_monitoreo, control_combustible, cump_regulaciones, protocolo_seguridad, gest_conductores, repor_id, ord_trabj_id, alerta_id, sali_repue_id) VALUES
(101,'GPS Activo','Control normal','Cumple','Protocolo A','Gestión A',NULL,1,1,NULL),
(102,'GPS Activo','Control normal','Cumple','Protocolo B','Gestión B',NULL,2,2,NULL),
(103,'GPS Activo','Control normal','Cumple','Protocolo C','Gestión C',NULL,3,3,NULL),
(104,'GPS Activo','Control normal','Cumple','Protocolo D','Gestión D',NULL,4,4,NULL),
(105,'GPS Activo','Control normal','Cumple','Protocolo E','Gestión E',NULL,5,5,NULL);

-- Ahora enlazamos sali_repue y sali_vehi entre sí y con repor
UPDATE sali_repue SET sali_vehi_id = 1 WHERE id = 1;
UPDATE sali_repue SET sali_vehi_id = 2 WHERE id = 2;
UPDATE sali_repue SET sali_vehi_id = 3 WHERE id = 3;
UPDATE sali_repue SET sali_vehi_id = 4 WHERE id = 4;
UPDATE sali_repue SET sali_vehi_id = 5 WHERE id = 5;

UPDATE sali_vehi SET sali_repue_id = 1 WHERE id = 1;
UPDATE sali_vehi SET sali_repue_id = 2 WHERE id = 2;
UPDATE sali_vehi SET sali_repue_id = 3 WHERE id = 3;
UPDATE sali_vehi SET sali_repue_id = 4 WHERE id = 4;
UPDATE sali_vehi SET sali_repue_id = 5 WHERE id = 5;

-- Actualizar repor para relacionar con salidas
UPDATE repor SET sali_repue_id = 1, sali_vehi_id = 1 WHERE id = 1;
UPDATE repor SET sali_repue_id = 2, sali_vehi_id = 2 WHERE id = 2;
UPDATE repor SET sali_repue_id = 3, sali_vehi_id = 3 WHERE id = 3;
UPDATE repor SET sali_repue_id = 4, sali_vehi_id = 4 WHERE id = 4;
UPDATE repor SET sali_repue_id = 5, sali_vehi_id = 5 WHERE id = 5;


-- Fin de la estructura 