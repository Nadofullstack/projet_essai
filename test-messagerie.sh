#!/bin/bash

# Script de test pour la fonctionnalité de messagerie complète

echo "🧪 TEST: Fonctionnalité de Messagerie Complète"
echo "=============================================="
echo ""

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Test 1: Vérifier que les fichiers existent
echo -e "${BLUE}Test 1: Vérifier les fichiers créés${NC}"
echo "Fichiers frontend:"

files_frontend=(
  "frontend/src/stores/messages.js"
  "frontend/src/views/Messages.vue"
  "frontend/src/components/Messages/MessagesList.vue"
  "frontend/src/components/Messages/ChatWindow.vue"
  "frontend/src/components/Messages/MessagesContainer.vue"
)

for file in "${files_frontend[@]}"; do
  if [ -f "$file" ]; then
    echo -e "  ${GREEN}✓${NC} $file"
  else
    echo -e "  ${RED}✗${NC} $file (MANQUANT)"
  fi
done

echo ""
echo "Fichiers documentation:"

docs=(
  "FONCTIONNALITE_MESSAGERIE_COMPLETE.md"
  "GUIDE_INTEGRATION_MESSAGERIE.md"
)

for doc in "${docs[@]}"; do
  if [ -f "$doc" ]; then
    echo -e "  ${GREEN}✓${NC} $doc"
  else
    echo -e "  ${RED}✗${NC} $doc (MANQUANT)"
  fi
done

echo ""

# Test 2: Vérifier la configuration
echo -e "${BLUE}Test 2: Vérifier la configuration${NC}"
echo ""

echo "Frontend:"
echo "  - Port: http://localhost:5173"
echo "  - Store: useMessagesStore() depuis '@/stores/messages'"
echo "  - Route: /messages"
echo ""

echo "Backend:"
echo "  - Port: http://localhost:8000"
echo "  - Endpoints requis:"
echo "    - GET  /api/messages/users/list"
echo "    - GET  /api/messages/conversations"
echo "    - GET  /api/messages/{userId}"
echo "    - POST /api/messages"
echo "    - PUT  /api/messages/{messageId}/read"
echo ""

# Test 3: Instruction de test manuel
echo -e "${BLUE}Test 3: Instructions de test manuel${NC}"
echo ""

echo "1. Démarrer le serveur backend:"
echo -e "   ${YELLOW}cd backend && php artisan serve${NC}"
echo ""

echo "2. Démarrer le serveur frontend:"
echo -e "   ${YELLOW}cd frontend && npm run dev${NC}"
echo ""

echo "3. Ouvrir dans le navigateur:"
echo -e "   ${YELLOW}http://localhost:5173/login${NC}"
echo "   (Authentifiez-vous avec vos identifiants)"
echo ""

echo "4. Accéder à la page Messages:"
echo -e "   ${YELLOW}http://localhost:5173/messages${NC}"
echo ""

echo "5. Test de recherche d'utilisateurs:"
echo "   - Cliquer sur le champ 'Trouver un utilisateur...'"
echo "   - Taper un nom ou email"
echo "   - Vérifier que les résultats s'affichent"
echo ""

echo "6. Test d'envoi de message:"
echo "   - Cliquer sur un utilisateur"
echo "   - Taper un message"
echo "   - Appuyer sur Entrée ou cliquer sur 'Envoyer'"
echo ""

# Test 4: Vérifications en console
echo -e "${BLUE}Test 4: Vérifications en console (F12)${NC}"
echo ""

echo "Logs attendus:"
echo -e "  ${GREEN}✓ Utilisateurs disponibles chargés: X${NC}"
echo -e "  ${GREEN}✓ Conversations chargées: X${NC}"
echo -e "  ${GREEN}✓ Message envoyé avec succès${NC}"
echo ""

echo "Commandes de débogage:"
echo "  localStorage.getItem('auth_token')"
echo "  (Devrait retourner un token valide)"
echo ""

# Test 5: Checklist
echo -e "${BLUE}Test 5: Checklist de vérification${NC}"
echo ""

echo "Backend:"
echo "  ☐ MessagesController implémenté"
echo "  ☐ Routes API configurées"
echo "  ☐ Modèle Message créé"
echo "  ☐ Migration messages exécutée"
echo "  ☐ Relations utilisateur/message configurées"
echo ""

echo "Frontend:"
echo "  ☐ Axios installé (npm list axios)"
echo "  ☐ Pinia installé (npm list pinia)"
echo "  ☐ .env.local avec VITE_API_URL défini"
echo "  ☐ Route /messages dans le router"
echo "  ☐ Token d'authentification dans localStorage"
echo ""

echo "Fonctionnalités:"
echo "  ☐ Recherche d'utilisateurs fonctionne"
echo "  ☐ Envoi de message fonctionne"
echo "  ☐ Réception de message fonctionne"
echo "  ☐ Conversations se chargent"
echo "  ☐ Messages s'affichent correctement"
echo ""

# Test 6: Commandes utiles
echo -e "${BLUE}Test 6: Commandes utiles${NC}"
echo ""

echo "Tester l'endpoint /api/messages/users/list:"
echo -e "${YELLOW}curl -X GET http://localhost:8000/api/messages/users/list \\${NC}"
echo -e "${YELLOW}  -H 'Authorization: Bearer YOUR_TOKEN' \\${NC}"
echo -e "${YELLOW}  -H 'Content-Type: application/json'${NC}"
echo ""

echo "Vérifier les migrations:"
echo -e "${YELLOW}php artisan migrate:status${NC}"
echo ""

echo "Voir les logs:"
echo -e "${YELLOW}tail -f storage/logs/laravel.log${NC}"
echo ""

# Résumé
echo -e "${GREEN}═══════════════════════════════════════${NC}"
echo -e "${GREEN}Tests complètement listés!${NC}"
echo ""
echo "Statut: ✅ Prêt à tester"
echo "Date: $(date '+%d/%m/%Y %H:%M:%S')"
echo -e "${GREEN}═══════════════════════════════════════${NC}"
