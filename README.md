# TruckSISX - Sistema de Gestión de Flotas

Sistema integral de gestión para flotas de camiones con enfoque en monitoreo de alertas, órdenes de trabajo automáticas y control de inventario.

## 🚛 Características Principales

- **Sistema de Alertas Interactivo**: Diagrama visual de camión doble troque con alertas por posición de llanta
- **Órdenes de Trabajo Automáticas**: Generación automática de órdenes al crear alertas
- **Control de Inventario**: Gestión completa de repuestos con seguimiento de stock
- **Roles de Usuario**: Admin, Técnico y Conductor con permisos diferenciados
- **Dashboard Estadístico**: Métricas en tiempo real y visualización de datos
- **Sistema de Reportes**: Reportes de salidas, órdenes de trabajo y estadísticas generales
- **Seguridad Avanzada**: Protección CSRF, control de sesiones y validación de acceso

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 7.4+ con PDO
- **Base de Datos**: MySQL 8.0+
- **Frontend**: Bootstrap 5.3, JavaScript ES6+
- **Iconos**: Bootstrap Icons, Font Awesome
- **Arquitectura**: Patrón MVC (Model-View-Controller)

## 📥 Instalación y Configuración

### Requisitos Previos
- XAMPP (Apache + MySQL + PHP)
- Navegador web moderno
- Editor de texto (recomendado: VS Code)

### Pasos de Instalación

1. **Configurar la base de datos**
   - Iniciar XAMPP (Apache + MySQL)
   - Acceder a phpMyAdmin: `http://localhost/phpmyadmin`
   - Ejecutar el archivo `db.sql` para crear la estructura completa

2. **Configurar conexión (opcional)**
   - Editar `config/db.php` si es necesario cambiar credenciales de BD
   
3. **Acceder al sistema**
   - Navegar a: `http://localhost/trucksisx`
   - Usar credenciales por defecto (ver abajo)

## 👥 Usuarios por Defecto

| Usuario | Contraseña | Rol | Permisos |
|---------|------------|-----|----------|
| `admin` | `admin123` | Administrador | CRUD completo, gestión usuarios |
| `tecnico` | `tecn123` | Técnico | CRUD completo, sin gestión usuarios |
| `conduc` | `conduc123` | Conductor | Crear, modificar, consultar |

## 📁 Estructura del Proyecto

```
trucksisx/
├── 📄 index.php              # Página de login principal
├── 📄 db.sql                 # Estructura de base de datos
├── 📄 README.md              # Este archivo
├── 📄 bitacora.txt           # Log de desarrollo
├── 📁 config/
│   └── db.php                # Configuración de conexión BD
├── 📁 controllers/           # Controladores MVC
│   ├── LoginController.php   # Autenticación y sesiones
│   ├── AlertController.php   # Sistema de alertas
│   ├── UserController.php    # Gestión de usuarios
│   └── ...                   # Otros controladores
├── 📁 models/                # Modelos de datos
│   ├── User.php              # Modelo de usuarios
│   ├── Alert.php             # Modelo de alertas
│   ├── OrdTrabj.php          # Modelo de órdenes de trabajo
│   └── ...                   # Otros modelos
├── 📁 views/                 # Interfaces de usuario
│   ├── dashboard.php         # Panel principal
│   ├── truck_alerts.php     # Sistema de alertas
│   ├── login.php            # Página de inicio de sesión
│   └── ...                   # Otras vistas
├── 📁 assets/               # Recursos estáticos
│   ├── css/                 # Hojas de estilo personalizadas
│   ├── js/                  # JavaScript personalizado
│   └── images/              # Imágenes del sistema
├── 📁 api/                  # API REST endpoints
│   └── dashboard_stats.php  # Estadísticas del dashboard
├── 📁 uploads/              # Archivos subidos
│   └── alertas/             # Evidencias de alertas
└── 📁 docs/                 # Documentación adicional
```

## 🚀 Estado del Proyecto

### ✅ Completamente Funcional
- [x] Sistema de login con seguridad avanzada
- [x] Dashboard con estadísticas en tiempo real  
- [x] Sistema de alertas de llantas con diagrama interactivo
- [x] Gestión de usuarios por roles
- [x] Órdenes de trabajo automáticas
- [x] Control de sesiones y protección CSRF
- [x] Gestión de vehículos y conductores
- [x] Sistema de repuestos y salidas

### 🔧 Próximas Mejoras Sugeridas
- [ ] Sistema completo de reportes avanzados
- [ ] Notificaciones push en tiempo real
- [ ] Módulo de mantenimiento preventivo
- [ ] Dashboard de métricas avanzadas
- [ ] Optimización de performance

## 📊 Módulos Principales

### 1. Sistema de Alertas (`truck_alerts.php`)
- Diagrama interactivo de camión doble troque
- Alertas por posición de llanta (8 posiciones)
- Generación automática de órdenes de trabajo
- Carga de evidencias fotográficas
- Filtros por estado y prioridad

### 2. Gestión de Órdenes (`orden_trabajo.php`)
- Visualización de órdenes generadas automáticamente
- Estados: Pendiente, En Proceso, Completada, Cancelada
- Asignación a técnicos y conductores
- Seguimiento de repuestos utilizados

### 3. Control de Inventario (`repue.php`, `salida_repuesto.php`)
- Gestión completa de repuestos
- Control de stock y ubicaciones
- Registro de entradas y salidas
- Integración con órdenes de trabajo

## 🔐 Seguridad Implementada

- **Autenticación**: Contraseñas hasheadas con SHA256
- **Sesiones**: Regeneración de ID, validación de IP, timeout automático
- **CSRF Protection**: Tokens únicos por sesión
- **Validación de Entrada**: Sanitización de datos y prepared statements
- **Control de Acceso**: Permisos basados en roles
- **Protección contra Fuerza Bruta**: Límite de intentos de login

## 🔄 Cómo Continuar el Desarrollo

1. **Revisar la bitacora.txt** para entender el historial completo
2. **Seguir el patrón MVC** establecido en la estructura
3. **Mantener consistencia** en estilos CSS y JavaScript
4. **Probar exhaustivamente** nuevas funcionalidades
5. **Documentar cambios** importantes

## 🐛 Solución de Problemas Comunes

### Error de Base de Datos
- Verificar que MySQL esté ejecutándose en XAMPP
- Confirmar que la base de datos `trucksisx` existe
- Revisar credenciales en `config/db.php`

### Error de Permisos
- Verificar que el usuario tenga el rol correcto
- Comprobar que la sesión no haya expirado
- Reiniciar sesión si persisten problemas

---

**TruckSISX v1.0** - Sistema desarrollado para gestión integral de flotas de transporte
