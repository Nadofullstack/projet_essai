# 📱 Fonctionnalité de Messagerie Complète

## 🎯 Objectif
Permettre aux utilisateurs connectés de:
1. **Rechercher** d'autres utilisateurs connectés par nom ou email
2. **Envoyer des messages** à n'importe quel utilisateur connecté
3. **Recevoir des messages** de n'importe quel utilisateur
4. **Gérer les conversations** (affichage chronologique, statut de lecture)

## 📋 Architecture

### 1. **Store Pinia** (`frontend/src/stores/messages.js`)
Gère l'état global des messages et des conversations

**État principal:**
- `conversations`: Liste des conversations actives
- `availableUsers`: Liste de tous les utilisateurs connectés
- `messages`: Messages de la conversation actuelle
- `searchQuery`: Requête de recherche
- `currentConversation`: Conversation en cours

**Actions principales:**
```javascript
// Charger les utilisateurs disponibles
await messagesStore.fetchAvailableUsers()

// Rechercher un utilisateur
const results = await messagesStore.searchUsers('jean')

// Envoyer un message
await messagesStore.sendMessage(userId, content)

// Récupérer les conversations
await messagesStore.fetchConversations()

// Démarrer une nouvelle conversation
messagesStore.startConversationWithUser(user)
```

### 2. **Composants Vue**

#### `Messages.vue` (Vue principale)
- Affichage des conversations
- Barre de recherche d'utilisateurs
- Zone de chat
- Gestion des appels audio/vidéo

**Fonctionnalités:**
- Recherche en temps réel (debounced)
- Affichage des résultats de recherche en dropdown
- Envoi/réception de messages
- Indicateurs de statut (en ligne, dernière activité)
- Appels audio et vidéo

#### `MessagesList.vue` (Nouveau)
- Affiche la liste des conversations et contacts
- Recherche intégrée
- Filtrage par nom/email
- Sélection de conversation

#### `ChatWindow.vue` (Nouveau)
- Interface de chat
- Affichage des messages
- Input pour écrire les messages
- Statut de livraison

### 3. **API Backend Requise**

#### Endpoints utilisés:

**GET `/api/messages/users/list`**
Retourne la liste de tous les utilisateurs connectés
```json
{
  "success": true,
  "users": [
    {
      "id": 1,
      "name": "Jean Dupont",
      "email": "jean@example.com",
      "avatar": "url_avatar",
      "isOnline": true
    }
  ]
}
```

**GET `/api/messages/conversations`**
Retourne les conversations de l'utilisateur
```json
{
  "success": true,
  "conversations": [
    {
      "id": 2,
      "name": "Alice Martin",
      "email": "alice@example.com",
      "avatar": "url_avatar",
      "lastMessage": "Salut, comment ça va ?",
      "lastMessageAt": "2024-01-15T10:30:00",
      "unreadCount": 0
    }
  ]
}
```

**GET `/api/messages/{userId}`**
Retourne les messages avec un utilisateur spécifique
```json
{
  "success": true,
  "messages": [
    {
      "id": 1,
      "sender_id": 1,
      "receiver_id": 2,
      "content": "Bonjour !",
      "created_at": "2024-01-15T10:25:00",
      "is_read": true
    }
  ]
}
```

**POST `/api/messages`**
Envoie un message
```json
{
  "receiver_id": 2,
  "content": "Salut, comment ça va ?"
}
```

Réponse:
```json
{
  "success": true,
  "message": {
    "id": 1,
    "sender_id": 1,
    "receiver_id": 2,
    "content": "Salut, comment ça va ?",
    "created_at": "2024-01-15T10:30:00"
  }
}
```

**PUT `/api/messages/{messageId}/read`**
Marque un message comme lu

## 🚀 Comment l'utiliser

### Pour l'utilisateur final:

1. **Rechercher un utilisateur:**
   - Cliquer sur le champ "Trouver un utilisateur..."
   - Taper le nom ou l'email
   - Sélectionner l'utilisateur dans la liste

2. **Envoyer un message:**
   - Cliquer sur un utilisateur/conversation
   - Taper le message dans la zone de chat
   - Appuyer sur Entrée ou cliquer sur "Envoyer"

3. **Voir les conversations:**
   - Les conversations apparaissent dans la liste gauche
   - Les messages non lus sont barrés avec un badge
   - Cliquer pour ouvrir la conversation

### Pour le développeur:

```vue
<template>
  <MainLayout>
    <!-- Utiliser le composant Messages directement -->
    <Messages />
  </MainLayout>
</template>

<script setup>
import Messages from '@/views/Messages.vue'
</script>
```

## 🔧 Configuration requise

### Backend Laravel

1. **Routes à implémenter** dans `routes/api.php`:
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/messages/users/list', [MessagesController::class, 'listUsers']);
    Route::get('/messages/conversations', [MessagesController::class, 'conversations']);
    Route::get('/messages/{userId}', [MessagesController::class, 'show']);
    Route::post('/messages', [MessagesController::class, 'store']);
    Route::put('/messages/{messageId}/read', [MessagesController::class, 'markAsRead']);
});
```

2. **Modèles Eloquent:**
- `User`: Modèle utilisateur avec relation `messages()`
- `Message`: Modèle message avec relations `sender()` et `receiver()`

3. **Migrations:**
```php
Schema::create('messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sender_id')->constrained('users');
    $table->foreignId('receiver_id')->constrained('users');
    $table->text('content');
    $table->boolean('is_read')->default(false);
    $table->timestamp('read_at')->nullable();
    $table->timestamps();
});
```

### Frontend

✅ Déjà configuré:
- Pinia pour la gestion d'état
- Axios pour les appels API
- Vue 3 avec Composition API
- Tailwind CSS pour le styling

## 📊 Flux de données

```
Utilisateur cherche → Input → searchUsers()
                                   ↓
                            API /api/messages/users/list
                                   ↓
                            Store availableUsers
                                   ↓
                            Affichage dropdown
                                   ↓
Utilisateur sélectionne → startConversationWithUser()
                                   ↓
                            API /api/messages/{userId}
                                   ↓
                            Store messages
                                   ↓
                            Affichage chat
                                   ↓
Utilisateur envoie → sendMessage()
                                   ↓
                            API POST /api/messages
                                   ↓
                            Store met à jour
                                   ↓
                            Affichage du message envoyé
```

## 🧪 Tests

### Test manuel:

1. **Ouvrir le page Messages**
   ```
   http://localhost:5173/messages
   ```

2. **Vérifier les logs console (F12):**
   ```
   ✓ Utilisateurs disponibles chargés: 5
   ✓ Conversations chargées: 3
   ✓ Message envoyé avec succès
   ```

3. **Test de recherche:**
   - Taper un nom partiel (ex: "jean")
   - Vérifier que les résultats s'affichent
   - Cliquer pour ouvrir la conversation

4. **Test d'envoi:**
   - Sélectionner une conversation
   - Taper un message
   - Appuyer sur Entrée
   - Vérifier que le message s'affiche

## 🐛 Dépannage

### Les utilisateurs ne s'affichent pas:

1. **Vérifier que le backend est running:**
   ```bash
   php artisan serve
   ```

2. **Vérifier le token d'authentification:**
   ```javascript
   // Dans la console du navigateur
   localStorage.getItem('auth_token')
   ```

3. **Tester l'endpoint directement:**
   ```bash
   curl -X GET http://localhost:8000/api/messages/users/list \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json"
   ```

4. **Vérifier les logs du serveur:**
   ```bash
   # Dans le terminal Laravel
   tail -f storage/logs/laravel.log
   ```

### Les messages ne s'envoient pas:

1. **Vérifier la base de données:**
   - Table `messages` existe
   - Relations utilisateur correctement configurées

2. **Vérifier les permissions:**
   - L'utilisateur est authentifié
   - L'utilisateur destinataire existe

3. **Vérifier le payload:**
   ```javascript
   // Devrait avoir:
   {
     "receiver_id": 2,
     "content": "message"
   }
   ```

## 📈 Améliorations futures

- [ ] Notifications en temps réel (WebSocket/Pusher)
- [ ] Typing indicators ("en train d'écrire...")
- [ ] Suppression/édition de messages
- [ ] Partage de fichiers
- [ ] Appels audio/vidéo réels (WebRTC)
- [ ] Groupes de messages
- [ ] Messages épinglés
- [ ] Recherche dans l'historique

## 📝 Fichiers modifiés/créés

```
✓ frontend/src/stores/messages.js (amélioré)
✓ frontend/src/views/Messages.vue (existant)
✓ frontend/src/components/Messages/MessagesList.vue (NOUVEAU)
✓ frontend/src/components/Messages/ChatWindow.vue (NOUVEAU)
✓ frontend/src/components/Messages/MessagesContainer.vue (NOUVEAU)
✓ FONCTIONNALITE_MESSAGERIE_COMPLETE.md (NOUVEAU)
```

---

**Status**: ✅ Implémenté et prêt à tester
**Date**: 11 février 2026
