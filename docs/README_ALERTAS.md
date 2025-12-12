# Sistema de Alertas de Llantas - TruckSISX

## Descripción General

El Sistema de Alertas de Llantas es una funcionalidad avanzada diseñada para monitorear en tiempo real el estado de las llantas de camiones doble troque. Permite a los conductores reportar problemas específicos en cualquier posición de llanta y genera automáticamente órdenes de trabajo para su resolución.

## Características Principales

### 🚛 Diagrama Interactivo del Camión
- **Vista SVG interactiva** del camión doble troque con 8 posiciones de llantas:
  - 2 llantas direccionales (frontales)
  - 4 llantas de tracción primera (doble eje)
  - 2 llantas de tracción segunda
- **Estados visuales** con código de colores para cada llanta
- **Interacción por clic** en cualquier llanta para crear alertas

### 📱 Sistema de Alertas
- **Registro rápido** de alertas con ventana emergente modal
- **Validación de conductor** en tiempo real por código/documento
- **Categorización por prioridad**: Baja, Media, Alta, Crítica
- **Evidencia fotográfica** opcional para cada alerta
- **Generación automática** de órdenes de trabajo

### 📊 Dashboard y Estadísticas
- **Panel de control** con estadísticas en tiempo real
- **Filtrado por vehículo** específico
- **Gráficos de tendencias** por posición de llanta
- **Histórico completo** de alertas y resoluciones

### ⚙️ Integración con Órdenes de Trabajo
- **Creación automática** de órdenes de trabajo al registrar alertas
- **Asignación inteligente** de técnicos y prioridades
- **Seguimiento completo** desde alerta hasta resolución
- **Conexión bidireccional** entre alertas y órdenes

## Estructura del Sistema

### Base de Datos

#### Tabla `alert` (Extendida)
```sql
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
    regis_vehic_id INT
);
```

### Archivos del Sistema

#### Backend (PHP)
- `models/Alert.php` - Modelo de datos para alertas
- `controllers/AlertController.php` - Lógica de negocio y API REST
- `models/User.php` - Modelo actualizado con validación de conductores

#### Frontend (HTML/CSS/JS)
- `views/truck_alerts.php` - Interfaz principal del sistema
- `assets/css/truck-alerts.css` - Estilos específicos del sistema
- `assets/js/truck-alerts.js` - Lógica interactiva y AJAX

#### Diagramas y Documentación
- `docs/truck-diagram.d2` - Diagrama D2 del sistema
- `docs/truck-diagram.gv` - Diagrama GraphViz del sistema
- `assets/images/truck-diagram.svg` - Diagrama SVG estático

### API Endpoints

#### Alertas
- `GET /controllers/AlertController.php?action=getAll` - Obtener todas las alertas
- `GET /controllers/AlertController.php?action=getTireAlerts` - Alertas específicas de llantas
- `POST /controllers/AlertController.php?action=create` - Crear nueva alerta
- `POST /controllers/AlertController.php?action=updateStatus` - Actualizar estado
- `POST /controllers/AlertController.php?action=validateConductor` - Validar conductor

#### Dashboard
- `GET /controllers/AlertController.php?action=getDashboard` - Estadísticas generales

## Posiciones de Llantas

El sistema maneja 8 posiciones específicas de llantas:

1. **Direccionales** (Frontales)
   - `direccion_izquierda` - Dirección Izquierda
   - `direccion_derecha` - Dirección Derecha

2. **Tracción Primera** (Doble Eje)
   - `traccion1_izquierda` - Tracción 1 - Izquierda
   - `traccion1_derecha` - Tracción 1 - Derecha
   - `traccion1_izquierda2` - Tracción 1 - Izquierda 2
   - `traccion1_derecha2` - Tracción 1 - Derecha 2

3. **Tracción Segunda**
   - `traccion2_izquierda` - Tracción 2 - Izquierda
   - `traccion2_derecha` - Tracción 2 - Derecha

## Estados y Prioridades

### Estados de Alertas
- **Activa**: Alerta recién creada, pendiente de atención
- **En Proceso**: Alerta siendo atendida por un técnico
- **Resuelta**: Problema solucionado exitosamente
- **Cancelada**: Alerta cancelada o descartada

### Niveles de Prioridad
- **Baja**: Problemas menores, no urgentes
- **Media**: Problemas que requieren atención en días
- **Alta**: Problemas que requieren atención inmediata
- **Crítica**: Problemas que comprometen la seguridad

### Código de Colores
- 🔵 **Azul (#333)**: Llanta sin alertas (normal)
- 🟡 **Amarillo (#ffff00)**: Alerta de prioridad baja
- 🟠 **Naranja (#ffa500)**: Alerta de prioridad media  
- 🔴 **Rojo (#ff4444)**: Alerta de prioridad alta/crítica
- 🟢 **Verde (#28a745)**: Alerta resuelta

## Flujo de Trabajo

### 1. Detección del Problema
- El conductor identifica un problema en una llanta específica
- Accede al sistema de alertas desde el vehículo o dispositivo móvil

### 2. Registro de Alerta
- Hace clic en la llanta correspondiente en el diagrama interactivo
- Se abre ventana modal para registro de alerta
- Ingresa su código de conductor para validación
- Describe el problema y selecciona prioridad
- Opcional: Adjunta foto de evidencia

### 3. Validación y Procesamiento
- Sistema valida el código de conductor en tiempo real
- Al enviar, se crea la alerta en la base de datos
- Automáticamente se genera una orden de trabajo asociada
- La llanta se colorea según la prioridad en el diagrama

### 4. Seguimiento y Resolución
- Los técnicos reciben notificación de nueva orden de trabajo
- Pueden actualizar el estado de la alerta durante el proceso
- Al completar la reparación, marcan la alerta como resuelta
- El diagrama se actualiza mostrando el estado resuelto

## Instalación y Configuración

### Requisitos
- PHP 7.4 o superior con PDO
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Navegador web moderno con soporte SVG

### Pasos de Instalación

1. **Base de Datos**
   ```sql
   -- Ejecutar el script actualizado db.sql
   mysql -u usuario -p nombre_base < db.sql
   ```

2. **Archivos del Sistema**
   ```bash
   # Verificar que todos los archivos estén en su lugar
   - models/Alert.php
   - controllers/AlertController.php  
   - views/truck_alerts.php
   - assets/css/truck-alerts.css
   - assets/js/truck-alerts.js
   ```

3. **Permisos**
   ```bash
   # Crear directorio de uploads y dar permisos
   mkdir uploads/alertas
   chmod 755 uploads/alertas
   ```

4. **Acceso**
   - Navegar a: `http://tu-servidor/trucksisx/views/truck_alerts.php`
   - Seleccionar un vehículo del dropdown
   - Hacer clic en cualquier llanta para probar

## Uso del Sistema

### Para Conductores
1. Acceder al sistema de alertas
2. Seleccionar el vehículo correspondiente
3. Hacer clic en la llanta con problema
4. Llenar el formulario de alerta con:
   - Código de conductor
   - Descripción del problema
   - Prioridad estimada
   - Observaciones adicionales
   - Foto de evidencia (opcional)
5. Enviar la alerta

### Para Técnicos/Administradores
1. Revisar dashboard de alertas activas
2. Consultar estadísticas por posición
3. Gestionar órdenes de trabajo generadas
4. Actualizar estados de alertas
5. Analizar tendencias y patrones

## Características Técnicas

### Tecnologías Utilizadas
- **Backend**: PHP 7.4+, MySQL, PDO
- **Frontend**: HTML5, CSS3, JavaScript ES6
- **Gráficos**: Chart.js para estadísticas
- **UI**: Bootstrap 5 para interfaz responsive
- **Diagramas**: SVG interactivo nativo

### Características de Rendimiento
- **Carga asíncrona** de datos con AJAX
- **Validación en tiempo real** de conductores
- **Actualización automática** del diagrama
- **Interfaz responsive** para dispositivos móviles
- **Caching** de consultas frecuentes

### Seguridad
- **Validación de entrada** en cliente y servidor
- **Sanitización** de datos antes de almacenar
- **Validación de conductores** por código único
- **Subida segura** de archivos de imagen
- **Escape de datos** en consultas SQL

## Diagramas Incluidos

### 1. Diagrama D2 (`docs/truck-diagram.d2`)
Formato moderno para visualización de arquitecturas, incluye:
- Componentes del camión con estilos
- Sistema de alertas y flujos
- Leyenda de estados

### 2. Diagrama GraphViz (`docs/truck-diagram.gv`)
Formato DOT para diagramas técnicos:
- Estructura jerárquica del sistema
- Relaciones entre componentes
- Flujos de datos y procesos

### 3. Diagrama SVG (`assets/images/truck-diagram.svg`)
Versión estática del diagrama interactivo:
- Compatible con cualquier navegador
- Escalable y de alta calidad
- Útil para documentación impresa

## Extensibilidad

El sistema está diseñado para ser fácilmente extensible:

### Nuevos Tipos de Alertas
- Modificar enum `tipo_alerta` en la base de datos
- Agregar lógica en `Alert.php` y `AlertController.php`
- Actualizar interfaz según necesidades

### Integración con Otros Sistemas
- API REST completa para integración externa
- Webhooks configurables para notificaciones
- Exportación de datos en formatos estándar

### Personalización Visual
- Temas CSS configurables
- Diagrams SVG modificables
- Layouts responsive adaptables

## Soporte y Mantenimiento

### Logs del Sistema
- Errores se registran en logs de PHP
- Actividad de usuarios en base de datos
- Métricas de rendimiento disponibles

### Backup y Recuperación
- Backup regular de base de datos recomendado
- Archivos de evidencia en directorio `uploads/`
- Configuración en archivos PHP

### Actualizaciones
- Sistema modular para fácil actualización
- Migraciones de base de datos documentadas
- Compatibilidad con versiones anteriores

---

## Contacto y Soporte

Para soporte técnico o consultas sobre el sistema:
- Revisar logs en caso de errores
- Verificar permisos de archivos y directorios
- Consultar documentación de API para integraciones

**Sistema desarrollado para TruckSISX - Gestión Integral de Flotas**