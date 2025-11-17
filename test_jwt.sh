#!/bin/bash

# Script de prueba para verificar autenticación JWT con API del amigo

API_BASE="http://10.182.18.70:8421"
USUARIO="razzluk"
PASSWORD="CesarPartner*"
DNI="73076473"

echo "=== PRUEBA DE AUTENTICACIÓN JWT ==="
echo ""

# Paso 1: Obtener token
echo "1. Obteniendo JWT token..."
echo "   POST $API_BASE/auth/login"
echo ""

TOKEN_RESPONSE=$(curl -s -X POST "$API_BASE/auth/login" \
  -H "Content-Type: application/json" \
  -d "{\"usuario\":\"$USUARIO\",\"password\":\"$PASSWORD\"}")

echo "Respuesta:"
echo "$TOKEN_RESPONSE" | jq '.' 2>/dev/null || echo "$TOKEN_RESPONSE"
echo ""

# Extraer token (si existe)
TOKEN=$(echo "$TOKEN_RESPONSE" | jq -r '.token // empty' 2>/dev/null)

if [ -z "$TOKEN" ]; then
    echo "❌ Error: No se obtuvo token"
    echo ""
    echo "Verifica:"
    echo "- El servidor está en http://10.182.18.70:8421"
    echo "- Las credenciales son correctas: usuario=$USUARIO, password=$PASSWORD"
    exit 1
fi

echo "✓ Token obtenido:"
echo "   ${TOKEN:0:50}..."
echo ""

# Paso 2: Consultar DNI
echo "2. Consultando DNI con JWT token..."
echo "   GET $API_BASE/consulta?dni=$DNI"
echo "   Header: Authorization: Bearer $TOKEN"
echo ""

DNI_RESPONSE=$(curl -s -X GET "$API_BASE/consulta?dni=$DNI" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

echo "Respuesta:"
echo "$DNI_RESPONSE" | jq '.' 2>/dev/null || echo "$DNI_RESPONSE"
echo ""

# Verificación final
if echo "$DNI_RESPONSE" | grep -q "success.*true\|data"; then
    echo "✓ ¡API funcionando correctamente!"
else
    echo "⚠ Verifica la respuesta de DNI arriba"
fi
