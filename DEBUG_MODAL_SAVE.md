# Pasos para Verificar la Corrección

## 1. Limpiar Caché del Navegador

### Opción A: Hard Refresh
- Presiona `Ctrl + Shift + R` (o `Ctrl + F5`)
- Esto fuerza al navegador a recargar todos los archivos

### Opción B: Limpiar Caché Completa
1. Abre DevTools (F12)
2. Click derecho en el botón de refrescar del navegador
3. Selecciona "Vaciar caché y volver a cargar de manera forzada"

## 2. Verificar que los Cambios se Aplicaron

1. Abre la consola del navegador (F12) → pestaña "Console"
2. Navega a: Gestión de Talento → Listado Convocatorias
3. Aplica filtros para mostrar convocatorias
4. Haz clic en "Detalles" de una convocatoria
5. Llena al menos un campo
6. **ANTES de hacer clic en "Guardar"**, asegúrate que la consola esté visible
7. Haz clic en "Guardar"

## 3. Qué Deberías Ver en la Consola

Si los cambios están aplicados correctamente, verás:
```
Enviando datos al servidor: {razon_social_interna: "...", ...}
Respuesta del servidor - Status: 200
Datos recibidos: {success: true, message: "...", ...}
```

## 4. Si Sigue Apareciendo el Error Antiguo

Si ves el error `Unexpected end of JSON input` SIN ver los mensajes anteriores:
1. El navegador sigue usando la versión cacheada
2. Intenta cerrar completamente el navegador y volver a abrirlo
3. O prueba en modo incógnito (Ctrl + Shift + N)

## 5. Si Ves los Nuevos Mensajes pero Hay un Error

Si ves los mensajes de consola pero hay un error:
1. Copia TODO el contenido de la consola
2. Copia el mensaje de error completo
3. Revisa la pestaña "Network" en DevTools para ver la respuesta del servidor
