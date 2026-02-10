# WebSocket Real-Time Messaging Implementation

Ce document explique comment utiliser la fonctionnalité de messages en temps réel avec WebSocket.

## Architecture

### Backend (Laravel + Reverb)
- **Event**: `App\Events\MessageSent` - Déclenché quand un message est créé
- **Channel**: `chat.{conversationKey}` - Canal privé pour chaque conversation
- **Broadcasting**: Configuration Reverb pour les WebSocket

### Frontend (Vue 3 + Echo)
- **Service**: `services/websocket.js` - Gestion de la connexion WebSocket
- **Composable**: `composables/useChat.js` - Logique métier des messages
- **Config**: `config/websocket.js` - Configuration environnement

## Configuration

### Backend

#### 1. Variables d'environnement (`.env`)

```env
# Broadcasting with Reverb
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=1
REVERB_APP_KEY=app-key
REVERB_APP_SECRET=app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

#### 2. Lancer Reverb

```bash
php artisan reverb:start
```

Cela démarrera le serveur WebSocket sur `localhost:8080`.

### Frontend

#### 1. Variables d'environnement (`.env.local`)

```env
VITE_API_URL=http://localhost:8000
VITE_REVERB_APP_KEY=app-key
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
```

#### 2. Dépendances requises

```bash
npm install laravel-echo pusher-js
```

## Utilisation

### Dans un composant Vue

```vue
<script setup>
import { useChat } from '@/composables/useChat'
import { onMounted, onBeforeUnmount } from 'vue'

const {
  messages,
  isLoading,
  error,
  isConnected,
  initChat,
  sendMessage,
  markMessageAsRead,
  disconnectWebSocket,
} = useChat()

const currentUserId = 1 // À récupérer depuis l'authentification
const recipientUserId = 2

onMounted(async () => {
  // Initialiser le chat et se connecter WebSocket
  await initChat(currentUserId, recipientUserId)
})

const handleSendMessage = async (content) => {
  try {
    await sendMessage(content, recipientUserId)
  } catch (err) {
    console.error('Erreur:', err)
  }
}

const handleMarkAsRead = async (messageId) => {
  await markMessageAsRead(messageId)
}

onBeforeUnmount(() => {
  // Se déconnecter proprement
  disconnectWebSocket()
})
</script>

<template>
  <div v-if="!isConnected" class="alert alert-warning">
    Connexion WebSocket en cours...
  </div>

  <div v-if="error" class="alert alert-error">
    {{ error }}
  </div>

  <div class="messages-list">
    <div v-for="message in messages" :key="message.id" class="message">
      <strong>{{ message.sender.name }}</strong>: {{ message.content }}
      <small>{{ message.created_at }}</small>
      
      <button 
        v-if="!message.is_read"
        @click="handleMarkAsRead(message.id)"
        class="btn-small"
      >
        Marquer comme lu
      </button>
    </div>
  </div>

  <div class="message-input">
    <input 
      type="text"
      @keyup.enter="handleSendMessage"
      placeholder="Votre message..."
    >
    <button @click="handleSendMessage">Envoyer</button>
  </div>
</template>
```

## Flux de communication

### Envoi d'un message

1. **Frontend** → Appelle `sendMessage()` via API
2. **Backend** → Crée le message, déclenche l'événement `MessageSent`
3. **Reverb** → Diffuse l'événement sur le canal `chat.{conversationKey}`
4. **Frontend** → Reçoit l'événement via WebSocket et met à jour la liste

```
Frontend          Backend         WebSocket Server (Reverb)
   |                |                      |
   |--POST /messages|                      |
   |                |                      |
   |                |--dispatch MessageSent|
   |                |                      |
   |                |--broadcast to channel-|
   |<--- receive via channel ---|
   |                |                      |
```

### Structure d'un message reçu

```javascript
{
  id: 1,
  content: "Bonjour!",
  type: "text",
  sender_id: 1,
  receiver_id: 2,
  sender: {
    id: 1,
    name: "Alice",
    avatar: "https://..."
  },
  has_attachment: false,
  attachment_type: null,
  attachment_path: null,
  is_read: false,
  created_at: "2026-01-26T10:30:00Z",
  updated_at: "2026-01-26T10:30:00Z"
}
```

## Clés de conversation

Les conversations sont identifiées par une clé unique qui combine les IDs des deux participants, toujours dans le même ordre :

```javascript
// Backend
private function getConversationKey(): string {
  $ids = [$this->message->sender_id, $this->message->receiver_id];
  sort($ids);
  return implode('_', $ids); // "1_2"
}

// Frontend
export function getConversationKey(userId1, userId2) {
  const ids = [userId1, userId2].sort((a, b) => a - b);
  return ids.join('_'); // "1_2"
}
```

Cela garantit que les messages entre Alice(1) et Bob(2) utilisent toujours le canal `chat.1_2`, peu importe qui envoie le message.

## Autorisations (Broadcasting)

Les autorisation sont gérées dans `routes/channels.php` :

```php
Broadcast::channel('chat.{conversationKey}', function ($user, $conversationKey) {
  $ids = explode('_', $conversationKey);
  
  if (count($ids) !== 2) {
    return false;
  }
  
  $userId = (int) $user->id;
  $id1 = (int) $ids[0];
  $id2 = (int) $ids[1];
  
  // L'utilisateur doit être l'un des deux participants
  return $userId === $id1 || $userId === $id2;
});
```

## Débogage

### Service WebSocket

```javascript
import { debugWebSocketInfo, isWebSocketConnected } from '@/services/websocket'

console.log(debugWebSocketInfo())
// {
//   isConnected: true,
//   hasEcho: true,
//   socketConnected: true,
//   reconnectAttempts: 0
// }
```

### Backend

Vérifiez les logs Reverb :

```bash
# Terminal 1 - Reverb
php artisan reverb:start

# Vous verrez les connexions et les messages reçus
[10:30:00] User 1 connected
[10:30:05] Broadcast to chat.1_2
[10:30:10] User 2 received message
```

## Dépannage

### "WebSocket non initialisé"

Appelez `initializeWebSocket(token)` avant d'utiliser le chat:

```javascript
import { initializeWebSocket } from '@/services/websocket'

const token = localStorage.getItem('auth_token')
initializeWebSocket(token)
```

### Messages ne s'affichent pas

1. Vérifiez que Reverb est en cours d'exécution
2. Vérifiez les variables d'environnement
3. Ouvrez la console et vérifiez `debugWebSocketInfo()`
4. Vérifiez les logs du serveur Reverb

### Reconnexion automatique

Le service WebSocket reconnecte automatiquement après une déconnexion:

```javascript
const MAX_RECONNECT_ATTEMPTS = 5
const RECONNECT_DELAY = 3000 // 3 secondes
```

## Performance

- Les messages sont chargés via l'API HTTP au démarrage
- Les nouveaux messages arrivent via WebSocket
- Un seul socket par conversation
- Auto-reconnexion avec backoff exponentiel

## Sécurité

- Les canaux utilisent `PrivateChannel` - authentification requise
- Les IDs utilisateur sont validés côté serveur
- Les tokens Sanctum assurent l'authentification WebSocket
- Les permissions sont vérifiées dans `channels.php`
