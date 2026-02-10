#!/bin/bash

echo "╔══════════════════════════════════════════════════════════════════════════════╗"
echo "║              TEST DU CHARGEMENT DES CONVERSATIONS                             ║"
echo "╚══════════════════════════════════════════════════════════════════════════════╝"
echo ""

# Configuration
API_URL="http://localhost:8000/api"

echo "🔍 Étape 1: Vérification des routes..."
echo ""

# Récupérer le token depuis le localStorage (simulé)
echo "📝 Note: Assurez-vous d'être connecté avant ce test"
echo ""

echo "📋 Test de la route /messages/conversations"
echo ""

# Créer un fichier temporaire pour stocker le token
TOKEN_FILE="/tmp/auth_token.txt"

echo "⚠️  Veuillez entrer votre token d'authentification:"
read -p "Token: " TOKEN

if [ -z "$TOKEN" ]; then
    echo "❌ Erreur: Token vide"
    exit 1
fi

echo ""
echo "🧪 Envoi de la requête..."
echo ""

# Tester la requête
RESPONSE=$(curl -s -X GET "$API_URL/messages/conversations" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -w "\n%{http_code}")

# Extraire le body et le code HTTP
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
BODY=$(echo "$RESPONSE" | head -n-1)

echo "📊 Réponse:"
echo "  HTTP Code: $HTTP_CODE"
echo ""
echo "  Body:"
echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"

echo ""

# Vérifier la réponse
if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ Succès! Les conversations ont été chargées"
    echo ""
    echo "Conversations reçues:"
    echo "$BODY" | jq '.data | length' 2>/dev/null && echo "conversations"
elif [ "$HTTP_CODE" = "404" ]; then
    echo "❌ Erreur 404: Route non trouvée"
    echo ""
    echo "Solutions:"
    echo "  1. Vérifiez l'ordre des routes dans routes/api.php"
    echo "  2. Videz le cache: php artisan route:clear"
    echo "  3. Vérifiez que la route existe: php artisan route:list | grep conversations"
elif [ "$HTTP_CODE" = "401" ]; then
    echo "❌ Erreur 401: Non authentifié"
    echo ""
    echo "Solutions:"
    echo "  1. Vérifiez que le token est valide"
    echo "  2. Vérifiez que le header Authorization est correct"
    echo "  3. Reconectez-vous"
else
    echo "❌ Erreur $HTTP_CODE: Erreur serveur"
fi

echo ""
echo "╚══════════════════════════════════════════════════════════════════════════════╝"
