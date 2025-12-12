# Sistema de Resolución de Alertas - Documentación Técnica

## 📋 Reglas de Negocio

### Regla Principal
**Una alerta solo puede considerarse "resuelta" mediante confirmación explícita del usuario.**

### Estados de Alerta
1. **activa** - Alerta recién creada, pendiente de atención
2. **en_proceso** - Alerta siendo trabajada
3. **resuelta** - Alerta completada y cerrada formalmente
4. **cancelada** - Alerta cancelada sin resolución

### Restricciones de Eliminación

#### ❌ NO SE PUEDE ELIMINAR si existe al menos una alerta NO resuelta
```
Estados que BLOQUEAN eliminación:
- activa
- en_proceso  
- cancelada
```

#### ✅ SÍ SE PUEDE ELIMINAR cuando:
- Todas las alertas asociadas están en estado "resuelta"
- O no existen alertas asociadas

---

## 🔄 Diagrama de Flujo

```
┌─────────────────────────────────────┐
│ Usuario intenta eliminar            │
│ Conductor o Vehículo                │
└───────────┬─────────────────────────┘
            │
            ▼
┌─────────────────────────────────────┐
│ Sistema: Verificar alertas          │
│ SELECT COUNT(*) FROM alert          │
│ WHERE (cond_id = X OR               │
│        regis_vehic_id = Y)          │
│ AND estado != 'resuelta'            │
└───────────┬─────────────────────────┘
            │
            ▼
      ┌─────┴─────┐
      │ COUNT > 0? │
      └─────┬─────┘
            │
     ┌──────┴──────┐
     │             │
    SÍ            NO
     │             │
     ▼             ▼
┌─────────┐   ┌──────────┐
│BLOQUEAR │   │PERMITIR  │
│con      │   │eliminación│
│mensaje  │   │          │
│de error │   │+ Desvincular
└─────────┘   │alertas   │
              │resueltas │
              └──────────┘
```

---

## 🗃️ Modificaciones a la Base de Datos

### 1. Agregar columnas para rastreo de resolución

```sql
ALTER TABLE alert 
ADD COLUMN fecha_resolucion DATETIME NULL COMMENT 'Fecha y hora en que se resolvió la alerta' AFTER estado,
ADD COLUMN usuario_resuelve_id INT NULL COMMENT 'ID del usuario que resolvió la alerta' AFTER fecha_resolucion,
ADD COLUMN notas_resolucion TEXT NULL COMMENT 'Notas o comentarios al resolver la alerta' AFTER usuario_resuelve_id,
ADD CONSTRAINT fk_alert_usuario_resuelve FOREIGN KEY (usuario_resuelve_id) REFERENCES users(id) ON DELETE SET NULL;
```

### 2. Índices para optimización de consultas

```sql
-- Índice compuesto para búsqueda rápida de alertas no resueltas por conductor
CREATE INDEX idx_alert_conductor_estado ON alert(cond_id, estado);

-- Índice compuesto para búsqueda rápida de alertas no resueltas por vehículo
CREATE INDEX idx_alert_vehiculo_estado ON alert(regis_vehic_id, estado);

-- Índice para filtrar por estado rápidamente
CREATE INDEX idx_alert_estado ON alert(estado);

-- Índice para fecha de resolución (útil para reportes)
CREATE INDEX idx_alert_fecha_resolucion ON alert(fecha_resolucion);
```

### 3. Modificar Foreign Keys para permitir desvinculación

```sql
-- Permite que al eliminar un conductor/vehículo, las alertas resueltas se mantengan pero se desvinculen
ALTER TABLE alert DROP FOREIGN KEY alert_ibfk_2;
ALTER TABLE alert DROP FOREIGN KEY alert_ibfk_3;

ALTER TABLE alert 
ADD CONSTRAINT fk_alert_conductor FOREIGN KEY (cond_id) REFERENCES cond(id) ON DELETE SET NULL;

ALTER TABLE alert 
ADD CONSTRAINT fk_alert_vehiculo FOREIGN KEY (regis_vehic_id) REFERENCES regis_vehic(id) ON DELETE SET NULL;
```

---

## 🔌 Endpoints API (PHP)

### 1. Resolver Alerta
**POST** `/controllers/AlertController.php?action=resolve`

```php
// Request Body
{
    "alert_id": 123,
    "notas_resolucion": "Llanta reemplazada y verificada"
}

// Response Success
{
    "success": true,
    "message": "Alerta resuelta exitosamente. Ahora puedes eliminar el conductor/vehículo si lo deseas.",
    "data": {
        "alert_id": 123,
        "fecha_resolucion": "2025-12-04 15:30:00",
        "usuario_resuelve": "Juan Pérez"
    }
}

// Response Error
{
    "success": false,
    "message": "No se pudo resolver la alerta. Intenta nuevamente."
}
```

### 2. Verificar si se puede eliminar Conductor
**GET** `/controllers/AlertController.php?action=canDeleteConductor&id=5`

```php
// Response - Puede eliminar
{
    "success": true,
    "can_delete": true,
    "message": "El conductor puede ser eliminado.",
    "pending_alerts": 0
}

// Response - NO puede eliminar
{
    "success": true,
    "can_delete": false,
    "message": "No se puede eliminar el conductor porque tiene 3 alertas pendientes o en proceso.",
    "pending_alerts": 3,
    "alerts": [
        {"id": 45, "descripcion": "Llanta desgastada", "estado": "activa", "fecha": "2025-12-01"},
        {"id": 46, "descripcion": "Revisión de frenos", "estado": "en_proceso", "fecha": "2025-12-02"}
    ]
}
```

### 3. Verificar si se puede eliminar Vehículo
**GET** `/controllers/AlertController.php?action=canDeleteVehicle&id=10`

```php
// Misma estructura que canDeleteConductor
```

---

## 💻 Pseudocódigo Lógico

### Función: Resolver Alerta

```
FUNCIÓN resolverAlerta(alert_id, user_id, notas):
    INICIO_TRANSACCIÓN
    
    TRY:
        // 1. Verificar que la alerta existe y NO está ya resuelta
        alerta = SELECT * FROM alert WHERE id = alert_id
        
        SI alerta.estado == 'resuelta':
            RETORNAR error("La alerta ya fue resuelta anteriormente")
        FIN_SI
        
        // 2. Actualizar la alerta
        UPDATE alert SET
            estado = 'resuelta',
            fecha_resolucion = AHORA(),
            usuario_resuelve_id = user_id,
            notas_resolucion = notas
        WHERE id = alert_id
        
        // 3. Actualizar orden de trabajo relacionada
        SI alerta.ord_trabj_id NO ES NULL:
            UPDATE ord_trabj SET estado = 'completada' 
            WHERE id = alerta.ord_trabj_id
        FIN_SI
        
        COMMIT_TRANSACCIÓN
        RETORNAR success("Alerta resuelta exitosamente")
        
    CATCH error:
        ROLLBACK_TRANSACCIÓN
        RETORNAR error("Error al resolver la alerta")
    FIN_TRY
FIN_FUNCIÓN
```

### Función: Verificar si se puede eliminar

```
FUNCIÓN puedeEliminarEntidad(tipo, entidad_id):
    // tipo = 'conductor' o 'vehiculo'
    
    SI tipo == 'conductor':
        columna = 'cond_id'
    SINO:
        columna = 'regis_vehic_id'
    FIN_SI
    
    // Contar alertas no resueltas
    query = "SELECT COUNT(*) as total FROM alert 
             WHERE " + columna + " = " + entidad_id + "
             AND estado IN ('activa', 'en_proceso', 'cancelada')"
    
    resultado = EJECUTAR(query)
    
    SI resultado.total > 0:
        // Obtener detalles de las alertas pendientes
        alertas = SELECT id, descripcion, estado, fecha_hora 
                  FROM alert 
                  WHERE columna = entidad_id 
                  AND estado != 'resuelta'
                  ORDER BY fecha_hora DESC
        
        RETORNAR {
            puede_eliminar: false,
            alertas_pendientes: resultado.total,
            detalles_alertas: alertas
        }
    SINO:
        RETORNAR {
            puede_eliminar: true,
            alertas_pendientes: 0
        }
    FIN_SI
FIN_FUNCIÓN
```

---

## 🎨 Mensajes de Usuario

### Mensajes de Éxito
- ✅ **Alerta resuelta**: "Alerta resuelta exitosamente. Ahora puedes eliminar el conductor/vehículo asociado si lo deseas."
- ✅ **Eliminación exitosa**: "Conductor/Vehículo eliminado exitosamente."

### Mensajes de Error
- ❌ **No se puede eliminar**: "No se puede eliminar el conductor/vehículo porque tiene {N} alertas pendientes o en proceso. Por favor, resuélvelas primero."
- ❌ **Alerta ya resuelta**: "Esta alerta ya fue resuelta anteriormente el {fecha} por {usuario}."
- ❌ **Error de sistema**: "Ocurrió un error al procesar la solicitud. Por favor, intenta nuevamente."

### Modal de Confirmación
```
Título: "Confirmar Resolución de Alerta"

Mensaje: 
"¿Estás seguro de que deseas marcar esta alerta como resuelta?

Una vez resuelta:
✓ La alerta quedará registrada en el historial
✓ Podrás eliminar el conductor y/o vehículo asociado si lo deseas
✓ Esta acción no se puede deshacer

¿Deseas continuar?"

Botones: [Cancelar] [Sí, Resolver Alerta]
```

---

## 📁 Archivos a Modificar

1. **Base de Datos**
   - `migration_alert_resolution.sql` - Agregar columnas y índices

2. **Modelos**
   - `models/Alert.php` - Agregar métodos `resolve()`, `canDeleteConductor()`, `canDeleteVehicle()`
   
3. **Controladores**
   - `controllers/AlertController.php` - Nuevos endpoints
   - `controllers/ConductorVehiculoController.php` - Validación en delete

4. **Vistas**
   - `views/truck_alerts.php` - Botón resolver, modal confirmación
   - `views/cond.php` - Validación antes de eliminar

5. **Assets**
   - `assets/js/truck-alerts.js` - Funciones JS para resolver/validar

---

**Fecha de Creación**: 4 de Diciembre, 2025  
**Sistema**: TruckSisx
