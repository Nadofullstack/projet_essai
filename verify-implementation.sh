#!/bin/bash

# 🔍 Script de Vérification - Système de Messagerie Avancé
# Vérifie que tout a été correctement implémenté

echo "═══════════════════════════════════════════════════════════════"
echo "🔍 VÉRIFICATION DU SYSTÈME DE MESSAGERIE AVANCÉ"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Compteurs
PASSED=0
FAILED=0
WARNINGS=0

# Fonction de test
test_file() {
    if [ -f "$1" ]; then
        echo -e "${GREEN}✓${NC} $1"
        ((PASSED++))
    else
        echo -e "${RED}✗${NC} $1"
        ((FAILED++))
    fi
}

test_dir() {
    if [ -d "$1" ]; then
        echo -e "${GREEN}✓${NC} $1 (directory)"
        ((PASSED++))
    else
        echo -e "${RED}✗${NC} $1 (directory)"
        ((FAILED++))
    fi
}

# ============== BACKEND ==============
echo "📦 BACKEND VERIFICATION"
echo "─────────────────────────────────────────────────────────────"

echo ""
echo "Models:"
test_file "backend/app/Models/User.php"
test_file "backend/app/Models/Messages.php"

echo ""
echo "Controllers:"
test_file "backend/app/Http/Controllers/MessagesController.php"

echo ""
echo "Routes:"
test_file "backend/routes/api.php"

echo ""
echo "Migrations:"
test_file "backend/database/migrations/2026_02_25_000000_add_user_status_fields.php"

echo ""
echo "Tests:"
test_file "backend/tests/Feature/MessagingFeaturesTest.php"

# ============== FRONTEND ==============
echo ""
echo "📱 FRONTEND VERIFICATION"
echo "─────────────────────────────────────────────────────────────"

echo ""
echo "Components:"
test_file "frontend/src/components/Messages/UserStatusBadge.vue"
test_file "frontend/src/components/Messages/UsersList.vue"
test_file "frontend/src/components/Messages/MessageItem.vue"
test_file "frontend/src/components/Messages/MessagesCleanup.vue"
test_file "frontend/src/components/Messages/ChatWindowV2.vue"

echo ""
echo "Composables:"
test_file "frontend/src/composables/useChatV2.js"
test_file "frontend/src/composables/useChat.js"

# ============== DOCUMENTATION ==============
echo ""
echo "📚 DOCUMENTATION"
echo "─────────────────────────────────────────────────────────────"

echo ""
test_file "ADVANCED_MESSAGING_IMPLEMENTATION.md"
test_file "QUICK_START_MESSAGING.md"
test_file "CHANGES_SUMMARY.md"

# ============== VÉRIFICATIONS DE CONTENU ==============
echo ""
echo "🔎 CONTENT VERIFICATION"
echo "─────────────────────────────────────────────────────────────"

# Vérifier que User.php a markAsOnline
if grep -q "markAsOnline" "backend/app/Models/User.php"; then
    echo -e "${GREEN}✓${NC} User.php contient markAsOnline()"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} User.php manque markAsOnline()"
    ((FAILED++))
fi

# Vérifier que Messages.php a is_new
if grep -q "is_new" "backend/app/Models/Messages.php"; then
    echo -e "${GREEN}✓${NC} Messages.php a le champ is_new"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} Messages.php manque is_new"
    ((FAILED++))
fi

# Vérifier que MessagesController a deleteAllMessages
if grep -q "deleteAllMessages" "backend/app/Http/Controllers/MessagesController.php"; then
    echo -e "${GREEN}✓${NC} MessagesController contient deleteAllMessages()"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} MessagesController manque deleteAllMessages()"
    ((FAILED++))
fi

# Vérifier les routes API
if grep -q "delete-all" "backend/routes/api.php"; then
    echo -e "${GREEN}✓${NC} Routes API contiennent delete-all"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} Routes API manquent delete-all"
    ((FAILED++))
fi

if grep -q "user/status" "backend/routes/api.php"; then
    echo -e "${GREEN}✓${NC} Routes API contiennent user/status"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} Routes API manquent user/status"
    ((FAILED++))
fi

# Vérifier que ChatWindowV2.vue existe
if grep -q "ChatWindow" "frontend/src/components/Messages/ChatWindowV2.vue" 2>/dev/null; then
    echo -e "${GREEN}✓${NC} ChatWindowV2.vue est implémenté"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} ChatWindowV2.vue problème"
    ((WARNINGS++))
fi

# ============== PRÉREQUIS ==============
echo ""
echo "📋 PRÉREQUIS"
echo "─────────────────────────────────────────────────────────────"

echo ""
echo "Backend Dependencies:"
if grep -q "laravel" "backend/composer.json"; then
    echo -e "${GREEN}✓${NC} Laravel installé"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} Laravel non trouvé"
    ((FAILED++))
fi

echo ""
echo "Frontend Dependencies:"
if grep -q "vue" "frontend/package.json"; then
    echo -e "${GREEN}✓${NC} Vue installé"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} Vue non trouvé"
    ((FAILED++))
fi

# ============== RÉSUMÉ ==============
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "📊 RÉSUMÉ DE VÉRIFICATION"
echo "═══════════════════════════════════════════════════════════════"

TOTAL=$((PASSED + FAILED + WARNINGS))

echo ""
echo -e "Tests réussis:    ${GREEN}$PASSED${NC}"
echo -e "Tests échoués:    ${RED}$FAILED${NC}"
echo -e "Avertissements:   ${YELLOW}$WARNINGS${NC}"
echo -e "Total:            $TOTAL"

echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}✅ TOUS LES FICHIERS SONT PRÉSENTS!${NC}"
    echo ""
    echo "Prochaines étapes:"
    echo "1. cd backend"
    echo "2. php artisan migrate"
    echo "3. php artisan test"
    echo "4. cd ../frontend"
    echo "5. npm run dev"
else
    echo -e "${RED}❌ CERTAINS FICHIERS MANQUENT (voir ci-dessus)${NC}"
    exit 1
fi

echo ""
echo "═══════════════════════════════════════════════════════════════"
