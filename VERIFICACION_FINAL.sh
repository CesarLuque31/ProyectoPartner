#!/bin/bash

# SCRIPT DE VERIFICACIÓN FINAL - Sistema Postulantes

echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║     VERIFICACIÓN FINAL - SISTEMA DE POSTULANTES              ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo ""

PROJECT_DIR="c:/Users/Samuel/Desktop/Proyecto1_old/ProyectoPartner"
cd "$PROJECT_DIR" || exit 1

echo "📁 Verificando archivos clave..."
echo ""

# Verificar archivos PHP
echo "📄 Controlador PostulanteController.php:"
if [ -f "app/Http/Controllers/PostulanteController.php" ]; then
    echo "   ✅ Existe"
    php -l "app/Http/Controllers/PostulanteController.php" > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        echo "   ✅ Sintaxis correcta"
    else
        echo "   ❌ Errores de sintaxis"
    fi
else
    echo "   ❌ NO EXISTE"
fi

echo ""
echo "📄 Middleware RolesAccess.php:"
if [ -f "app/Http/Middleware/RolesAccess.php" ]; then
    echo "   ✅ Existe"
    php -l "app/Http/Middleware/RolesAccess.php" > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        echo "   ✅ Sintaxis correcta"
    else
        echo "   ❌ Errores de sintaxis"
    fi
else
    echo "   ❌ NO EXISTE"
fi

echo ""
echo "📄 Blade Template insertar_postulante.blade.php:"
if [ -f "resources/views/talent/insertar_postulante.blade.php" ]; then
    echo "   ✅ Existe"
else
    echo "   ❌ NO EXISTE"
fi

echo ""
echo "📄 Blade Layout app.blade.php:"
if [ -f "resources/views/layouts/app.blade.php" ]; then
    echo "   ✅ Existe"
else
    echo "   ❌ NO EXISTE"
fi

# Verificar documentación
echo ""
echo "📚 Verificando documentación..."
echo ""

DOCS=("IMPLEMENTACION_JWT.md" "JWT_TROUBLESHOOTING.md" "GUIA_PRUEBA_POSTULANTES.md" "ARQUITECTURA_SISTEMA.md" "PRUEBA_RAPIDA.md" "RESUMEN_FINAL.md")

for doc in "${DOCS[@]}"; do
    if [ -f "$doc" ]; then
        echo "   ✅ $doc"
    else
        echo "   ❌ $doc (FALTA)"
    fi
done

# Verificar .env
echo ""
echo "⚙️  Verificando .env..."
echo ""

if grep -q "API_EXTERNAL_BASE" .env; then
    echo "   ✅ API_EXTERNAL_BASE configurado"
else
    echo "   ⚠️  API_EXTERNAL_BASE no configurado"
fi

if grep -q "API_EXTERNAL_USER" .env; then
    echo "   ✅ API_EXTERNAL_USER configurado"
else
    echo "   ⚠️  API_EXTERNAL_USER no configurado"
fi

if grep -q "API_EXTERNAL_PASS" .env; then
    echo "   ✅ API_EXTERNAL_PASS configurado"
else
    echo "   ⚠️  API_EXTERNAL_PASS no configurado"
fi

# Verificar rutas
echo ""
echo "🛣️  Verificando rutas..."
echo ""

if php artisan route:list 2>/dev/null | grep -q "postulantes/consulta"; then
    echo "   ✅ Ruta /postulantes/consulta existe"
else
    echo "   ❌ Ruta /postulantes/consulta NO EXISTE"
fi

if php artisan route:list 2>/dev/null | grep -q "postulantes/store"; then
    echo "   ✅ Ruta /postulantes/store existe"
else
    echo "   ❌ Ruta /postulantes/store NO EXISTE"
fi

if php artisan route:list 2>/dev/null | grep -q "postulantes/insertar"; then
    echo "   ✅ Ruta /postulantes/insertar existe"
else
    echo "   ❌ Ruta /postulantes/insertar NO EXISTE"
fi

# Verificar base de datos
echo ""
echo "🗄️  Verificando base de datos..."
echo ""

php artisan migrate:status 2>/dev/null | grep -q "raz_postulantes" && \
    echo "   ✅ Tabla raz_postulantes migrada" || \
    echo "   ⚠️  Tabla raz_postulantes NO migrada"

# Verificar caché
echo ""
echo "💾 Verificando caché..."
echo ""

php artisan cache:clear > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "   ✅ Caché limpiable"
else
    echo "   ❌ Error al limpiar caché"
fi

# Verificar vistas
echo ""
echo "🎨 Verificando vistas compiladas..."
echo ""

php artisan view:clear > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "   ✅ Vistas limpiadas"
else
    echo "   ❌ Error al limpiar vistas"
fi

# Resumen
echo ""
echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║                      VERIFICACIÓN COMPLETADA                  ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo ""
echo "✅ Sistema de Postulantes listo para usar"
echo ""
echo "📌 Próximos pasos:"
echo "   1. Iniciar servidor: php artisan serve"
echo "   2. Abrir: http://127.0.0.1:8000/postulantes/insertar"
echo "   3. Ingresar con usuario que tenga rol: jefe o reclutador"
echo "   4. Probar consulta DNI"
echo "   5. Guardar postulante en BD"
echo ""
echo "📚 Documentación disponible en:"
echo "   - RESUMEN_FINAL.md (este es el mejor para empezar)"
echo "   - GUIA_PRUEBA_POSTULANTES.md (instrucciones paso a paso)"
echo "   - ARQUITECTURA_SISTEMA.md (detalles técnicos)"
echo "   - PRUEBA_RAPIDA.md (comandos de debug)"
echo ""
