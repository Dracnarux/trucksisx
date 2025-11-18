-- Migración para eliminar el campo imagen_evidencia de la tabla alert
-- Ejecutar en una base de datos existente para aplicar los cambios

USE trucksisx;

-- Eliminar el campo imagen_evidencia de la tabla alert
ALTER TABLE alert DROP COLUMN IF EXISTS imagen_evidencia;

-- Verificación: Mostrar la nueva estructura de la tabla
DESCRIBE alert;

-- Comentario: 
-- Esta migración elimina permanentemente el campo imagen_evidencia
-- Los datos existentes en ese campo se perderán
-- Asegúrate de hacer un backup antes de ejecutar si necesitas preservar las imágenes