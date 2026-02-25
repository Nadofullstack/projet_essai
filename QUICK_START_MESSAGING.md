# 🚀 Guide Rapide d'Intégration - Système de Messagerie Avancé

## ⚡ Installation en 5 minutes

### Étape 1: Backend (2 min)

```bash
cd backend

# Migrer la base de données
php artisan migrate

# Vérifier que tout fonctionne
php artisan tinker
>>> User::first()->markAsOnline()
>>> User::first()->online_status
# devrait retourner 'online'
```

### Étape 2: Frontend (3 min)

```bash
cd frontend

# Les composants sont déjà créés:
# - src/components/Messages/UserStatusBadge.vue
# - src/components/Messages/UsersList.vue
# - src/components/Messages/MessageItem.vue
# - src/components/Messages/MessagesCleanup.vue
# - src/components/Messages/ChatWindowV2.vue
```

---

## 💡 Utilisation Quick Start

### Dans votre vue (ex: MessagingPage.vue)

```vue
<template>
  <div class="messaging-page">
    <!-- Interface complète de chat -->
    <ChatWindowV2 @close="goBack" />
  </div>
</template>

<script>
import { defineComponent } from 'vue';
import ChatWindowV2 from '@/components/Messages/ChatWindowV2.vue';

export default defineComponent({
  name: 'MessagingPage',
  components: {
    ChatWindowV2
  },
  methods: {
    goBack() {
      this.$router.back();
    }
  }
});
</script>

<style scoped>
.messaging-page {
  height: 100vh;
  width: 100%;
}
</style>
```

---

## 📱 Fonctionnalités Principales

### 1. Voir les utilisateurs en ligne/offline
```
ChatWindowV2 
  ↓
UsersList affiche:
  • Utilisateurs en ligne (● VERT)
  • Utilisateurs hors ligne (● GRIS)
  • Heure de dernière déconnexion
  • Filtres (Tous, En ligne, Hors ligne)
```

### 2. Envoyer un message et voir les nouveaux
```
Saisie de message
  ↓
Bouton Envoyer
  ↓
Message affiché avec:
  • ● Indicateur VERT si nouveau
  • ✓ ou ✓✓ statut de lecture
  • Heure du message
```

### 3. Marquer comme lu
```
Bouton 📖 dans MessageItem
  ↓
Message marqué is_read = true
  ↓
is_new = false (disparaît l'indicateur)
```

### 4. Supprimer des messages
```
Bouton 🗑️ dans MessageItem
  ↓
OU
  ↓
Cliquer 🗑️ en haut du ChatWindow
  ↓
Ouvrir MessagesCleanup
  ↓
Choisir:
  • Supprimer tous les messages
  • Supprimer les messages de plus de 30 jours
```

---

## 🔧 Configuration

### Routes API nécessaires

Vérifier que ces routes existent dans `backend/routes/api.php`:

```php
// Status utilisateur
Route::get('/messages/users/status', [MessagesController::class, 'getUsersStatus']);
Route::post('/messages/user/status', [MessagesController::class, 'updateUserStatus']);

// Suppression
Route::delete('/messages/delete-all', [MessagesController::class, 'deleteAllMessages']);
Route::delete('/messages/delete-old', [MessagesController::class, 'deleteOldMessages']);

// Autres (déjà existantes)
Route::get('/messages', [MessagesController::class, 'index']);
Route::post('/messages', [MessagesController::class, 'store']);
Route::get('/messages/conversation/{userId}', [MessagesController::class, 'getConversation']);
```

### Service API (frontend)

Vérifier que votre `src/services/api.js` supporte:

```javascript
export default {
  get: (url) => axios.get(url),
  post: (url, data) => axios.post(url, data),
  put: (url, data) => axios.put(url, data),
  delete: (url, data) => axios.delete(url, { data }),
};
```

---

## 🎨 Personnalisation

### Couleurs des indicateurs

**Nouveau message (VERT):**
```css
/* src/components/Messages/MessageItem.vue */
.new-badge {
  color: #22c55e;  /* Vert */
}
```

**Utilisateur en ligne (VERT):**
```css
/* src/components/Messages/UserStatusBadge.vue */
.online-dot {
  background: #22c55e;  /* Vert */
}
```

### Dimensionnement

```css
/* ChatWindowV2.vue */
.users-panel {
  width: 300px;  /* Largeur du panneau utilisateurs */
}

@media (max-width: 768px) {
  /* Mode responsive activé automatiquement */
}
```

---

## 🧪 Tests

### Tester le backend

```bash
cd backend
php artisan test tests/Feature/MessagingFeaturesTest.php

# Ou un test spécifique:
php artisan test tests/Feature/MessagingFeaturesTest.php --filter=test_message_is_marked_as_new_on_creation
```

### Tester manuellement

1. Créer deux utilisateurs
2. Se connecter avec le 1er
3. Cliquer sur le 2e dans UsersList
4. Envoyer un message
5. Vérifier l'indicateur VERT
6. Marquer comme lu
7. L'indicateur VERT disparaît

---

## 🐛 Dépannage

### Problème: Pas de WebSocket
**Solution:** Vérifier que Reverb est configuré dans `backend/.env`

```bash
REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_HOST=localhost
REVERB_PORT=8080
```

### Problème: Statut ne se met pas à jour
**Solution:** Vérifier que l'endpoint `/api/messages/user/status` est accessible

```bash
curl -X POST http://localhost:8000/api/messages/user/status \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"online_status":"online"}'
```

### Problème: Suppression ne fonctionne pas
**Solution:** Vérifier la migration a bien été exécutée

```bash
php artisan migrate:status
# Vérifier que 2026_02_25_000000_add_user_status_fields est "Ran"
```

---

## 📦 Structure des fichiers

```
frontend/src/components/Messages/
├── ChatWindowV2.vue           ← Interface principale
├── UserStatusBadge.vue        ← Affiche statut utilisateur
├── UsersList.vue              ← Liste des utilisateurs
├── MessageItem.vue            ← Un message (avec VERT)
├── MessagesCleanup.vue        ← Suppression des messages
├── ChatWindow.vue             ← Ancien (dépendances optionnelles)
├── MessagesContainer.vue
└── MessagesList.vue

frontend/src/composables/
├── useChat.js                 ← Original (peut être remplacé)
└── useChatV2.js               ← Nouveau avec statuts + suppression

backend/app/Http/Controllers/
└── MessagesController.php     ← 8 nouvelles méthodes

backend/app/Models/
├── User.php                   ← markAsOnline/markAsOffline
└── Messages.php               ← SoftDeletes + is_new

backend/database/migrations/
└── 2026_02_25_000000_add_user_status_fields.php

backend/tests/Feature/
└── MessagingFeaturesTest.php  ← 12 tests complets
```

---

## ✅ Checklist d'intégration

- [ ] Migration exécutée (`php artisan migrate`)
- [ ] Routes API vérifiées
- [ ] Service API configuré
- [ ] Composants Vue importés
- [ ] ChatWindowV2 intégré dans une page
- [ ] WebSocket/Reverb configuré
- [ ] Tests passants (`php artisan test`)
- [ ] Page de messagerie fonctionne
- [ ] Indicateur VERT visible
- [ ] Statut en ligne/offline fonctionne
- [ ] Suppression des messages fonctionne

---

## 🎓 Exemples d'utilisation avancée

### Charger une conversation spécifique

```javascript
// Dans un composant
const { loadMessages, messages } = useChat();
const userId = 42;

// Charger les messages avec un utilisateur spécifique
await api.get(`/messages/conversation/${userId}`);
```

### Mettre à jour le statut périodiquement

```javascript
// Dans mounted()
setInterval(() => {
  updateUserStatus('online');
}, 30000); // Toutes les 30 secondes
```

### Compter les nouveaux messages

```javascript
const newCount = computed(() => {
  return messages.value.filter(m => m.is_new && !m.is_read).length;
});

// Afficher dans un badge
{{ newCount > 0 ? newCount : '' }}
```

---

## 📞 Support

**Pour des questions:**
1. Vérifier [ADVANCED_MESSAGING_IMPLEMENTATION.md](ADVANCED_MESSAGING_IMPLEMENTATION.md)
2. Consulter les tests dans [MessagingFeaturesTest.php](backend/tests/Feature/MessagingFeaturesTest.php)
3. Vérifier les logs: `storage/logs/laravel.log`

---

**Version:** 1.0  
**Date:** 25 Février 2026  
**Status:** ✅ Production Ready
