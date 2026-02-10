#!/bin/bash

echo "╔══════════════════════════════════════════════════════════════════════════════╗"
echo "║                       TEST DE L'ENVOI DE MESSAGE                             ║"
echo "╚══════════════════════════════════════════════════════════════════════════════╝"
echo ""

# Configuration
API_URL="http://localhost:8000/api"
TOKEN=$(curl -s "$API_URL/user" 2>/dev/null | grep -o '"api_token":"[^"]*' | cut -d'"' -f4)

echo "🔍 Vérification de la configuration..."
echo ""

# Vérifier le token
if [ -z "$TOKEN" ]; then
    echo "❌ Erreur: Pas de token d'authentification"
    echo "   Assurez-vous que:"
    echo "   1. Vous êtes connecté (POST /auth/login)"
    echo "   2. Le token est stocké dans le localStorage"
    exit 1
else
    echo "✅ Token trouvé: ${TOKEN:0:20}..."
fi

echo ""
echo "📝 Vérification de la base de données..."
echo ""

# Vérifier s'il y a des utilisateurs
USERS=$(curl -s -H "Authorization: Bearer $TOKEN" "$API_URL/user" 2>/dev/null)
if [ -z "$USERS" ]; then
    echo "❌ Erreur: Impossible de récupérer l'utilisateur"
    exit 1
else
    echo "✅ Utilisateur trouvé"
fi

echo ""
echo "📤 Test d'envoi de message..."
echo ""

# Envoyer un message test
RESPONSE=$(curl -s -X POST "$API_URL/messages" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "receiver_id": 2,
    "content": "Message de test",
    "type": "text"
  }')

echo "Réponse du serveur:"
echo "$RESPONSE" | jq '.' 2>/dev/null || echo "$RESPONSE"

echo ""

# Vérifier la réponse
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "✅ Message envoyé avec succès!"
    echo ""
    echo "Message ID: $(echo "$RESPONSE" | jq -r '.data.id' 2>/dev/null)"
    echo "Contenu: $(echo "$RESPONSE" | jq -r '.data.content' 2>/dev/null)"
    echo "Type: $(echo "$RESPONSE" | jq -r '.data.type' 2>/dev/null)"
    echo "Créé à: $(echo "$RESPONSE" | jq -r '.data.created_at' 2>/dev/null)"
elif echo "$RESPONSE" | grep -q '"errors"'; then
    echo "❌ Erreur de validation:"
    echo "$RESPONSE" | jq '.errors' 2>/dev/null || echo "$RESPONSE"
elif echo "$RESPONSE" | grep -q '422'; then
    echo "❌ Erreur 422: Données invalides"
    echo "Vérifiez:"
    echo "  • receiver_id est un utilisateur existant"
    echo "  • content n'est pas vide"
    echo "  • type est l'un de: text, image, file, audio"
else
    echo "❌ Erreur inconnue"
fi

echo ""
echo "╚══════════════════════════════════════════════════════════════════════════════╝"
