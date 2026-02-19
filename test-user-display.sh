#!/bin/bash

# Script de test pour vérifier l'affichage de l'utilisateur connecté

echo "🧪 TEST: Affichage du nom et email utilisateur"
echo "================================================"
echo ""

# Couleurs pour les logs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test 1: Vérifier si le token existe
echo -e "${YELLOW}Test 1: Vérifier le token d'authentification${NC}"
echo "Clé à vérifier: 'auth_token' dans localStorage"
echo "Vous pouvez vérifier en ouvrant la console du navigateur (F12) et en tapant:"
echo "  localStorage.getItem('auth_token')"
echo ""

# Test 2: Vérifier l'endpoint API
echo -e "${YELLOW}Test 2: Vérifier l'endpoint /api/user${NC}"
echo "Commande curl à exécuter (remplacez YOUR_TOKEN):"
echo "  curl -X GET http://localhost:8000/api/user \\"
echo "    -H 'Authorization: Bearer YOUR_TOKEN' \\"
echo "    -H 'Content-Type: application/json' \\"
echo "    -H 'Accept: application/json'"
echo ""

# Test 3: Logs attendus
echo -e "${YELLOW}Test 3: Logs attendus dans la console (F12)${NC}"
echo "Vous devriez voir:"
echo -e "  ${GREEN}✓ Utilisateur connecté chargé: { id: ..., name: ..., email: ... }${NC}"
echo -e "  ${GREEN}✓ Header: Données utilisateur reçues: ...${NC}"
echo ""

# Test 4: Checker le store Pinia
echo -e "${YELLOW}Test 4: Vérifier le store Pinia${NC}"
echo "1. Ouvrir Vue DevTools (extension)"
echo "2. Aller dans l'onglet 'Pinia'"
echo "3. Vérifier que 'dashboard' contient:"
echo "   - currentUser.id"
echo "   - currentUser.name"
echo "   - currentUser.email"
echo ""

# Test 5: Vérifier les fichiers modifiés
echo -e "${YELLOW}Test 5: Fichiers modifiés${NC}"
echo "✓ frontend/src/stores/dashboard.js"
echo "✓ frontend/src/components/Dashboard/Layout/MainLayout.vue"
echo "✓ frontend/src/components/Dashboard/Layout/Header.vue"
echo "✓ frontend/src/composables/useUserDebug.js (NOUVEAU)"
echo ""

# Test 6: Configuration requise
echo -e "${YELLOW}Test 6: Checklist de configuration${NC}"
echo ""
echo "Vérifiez que:"
echo "☐ Le backend est running sur http://localhost:8000"
echo "☐ La route /api/user existe et fonctionne"
echo "☐ Vous êtes authentifié (token dans localStorage)"
echo "☐ VITE_API_URL est défini dans .env"
echo ""

echo -e "${GREEN}📋 Tests complètement listés. Consultez la console du navigateur!${NC}"
