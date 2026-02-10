# Guide de Dépannage - WebSocket Messaging

## Problèmes Courants et Solutions

### 1. "WebSocket non initialisé"

**Erreur:**
```
Error: WebSocket non initialisé. Appelez initializeWebSocket() d'abord.
```

**Solution:**
```javascript
import { initializeWebSocket } from '@/services/websocket'

// Dans onMounted:
const token = localStorage.getItem('auth_token')
initializeWebSocket(token)
```

---

### 2. Messages n'apparaissent pas

**Checklist:**
- [ ] Reverb est-il en cours d'exécution ? `php artisan reverb:start`
- [ ] Les variables d'environnement frontend sont-elles correctes ?
- [ ] La console affiche-t-elle "WebSocket connecté" ?
- [ ] Êtes-vous dans une conversation valide ?

**Déboguer:**
```javascript
import { debugWebSocketInfo } from '@/services/websocket'

console.log(debugWebSocketInfo())
// Vérifiez que socketConnected === true
```

---

### 3. Erreur 403 "Non autorisé à accéder au canal"

**Cause:** L'utilisateur n'est pas autorisé à accéder au canal de conversation.

**Vérification:**
Assurez-vous que la clé de conversation est correcte:

```javascript
import { getConversationKey } from '@/composables/useChat'

const key = getConversationKey(userId1, userId2)
console.log('Conversation key:', key) // Devrait être quelque chose comme "1_2"
```

**Vérifier l'authentification:**
```php
// Dans routes/channels.php
Broadcast::channel('chat.{conversationKey}', function ($user, $conversationKey) {
    // Debugging
    \Log::debug('Accès au canal', [
        'user_id' => $user->id,
        'conversation_key' => $conversationKey
    ]);
    
    $ids = explode('_', $conversationKey);
    $userId = (int) $user->id;
    $id1 = (int) $ids[0];
    $id2 = (int) $ids[1];
    
    return $userId === $id1 || $userId === $id2;
});
```

---

### 4. Reverb ne démarre pas

**Erreur:**
```
Could not bind to 0.0.0.0:8080
```

**Solutions:**
- Le port 8080 est déjà utilisé : `netstat -tulpn | grep 8080` (Linux/Mac)
- Utilisez un port différent : `php artisan reverb:start --port=9000`

---

### 5. CORS Error

**Erreur:**
```
Access to XMLHttpRequest blocked by CORS policy
```

**Solution dans `backend/config/cors.php`:**
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => ['http://localhost:5173'], // Votre port frontend
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

---

### 6. "TypeError: Cannot read property 'sender' of undefined"

**Cause:** Le message reçu ne contient pas les données complètes du sender.

**Vérification dans `app/Events/MessageSent.php`:**
```php
public function broadcastWith(): array
{
    return [
        'id' => $this->message->id,
        'content' => $this->message->content,
        'sender' => $this->message->sender ? [
            'id' => $this->message->sender->id,
            'name' => $this->message->sender->name,
            'avatar' => $this->message->sender->profile_picture,
        ] : null,
        // ... autres champs
    ];
}
```

**Assurez-vous que le sender est chargé:**
```php
$message = Messages::with(['sender'])->create($data);
```

---

### 7. Disconnexion rapide du WebSocket

**Symptômes:** WebSocket se déconnecte quelques secondes après la connexion.

**Vérification:**
```javascript
// Dans la console:
import { debugWebSocketInfo, isWebSocketConnected } from '@/services/websocket'

// Exécutez toutes les secondes:
setInterval(() => {
  console.log(debugWebSocketInfo())
}, 1000)
```

**Causes possibles:**
- Token expiré : Vérifiez que le token est valide et présent dans le localStorage
- Reverb crache : Vérifiez les logs Reverb
- Configuration Reverb incorrecte : Vérifiez `.env`

---

### 8. Messages arrivent deux fois

**Cause:** Les messages sont reçus à la fois via WebSocket ET via API.

**Solution:**
Dedupliquez les messages dans `useChat.js`:

```javascript
const onMessageReceived = (messageData) => {
  // Vérifier qu'il n'existe pas déjà
  const exists = messages.value.some((m) => m.id === messageData.id)
  if (!exists) {
    messages.value.push(messageData)
  }
}
```

---

### 9. Emails de notifications (optionnel)

**Implémenter les notifications:**

```php
// app/Events/MessageSent.php
use Illuminate\Queue\SerializesModels;

public function __construct(
    public Messages $message
) {
    // Optionnel: Envoyer une notification au receiver
    // Notification::send($message->receiver, new MessageReceived($message));
}
```

---

### 10. Performance - Messages prennent du temps à apparaître

**Optimisations:**

```javascript
// Frontend - Limiter le nombre de messages chargés
const loadMessages = async () => {
  const response = await api.get('/messages?limit=50&sort=-created_at')
  messages.value = response.data.data
}

// Backend - Charger seulement les messages récents
public function store(Request $request): JsonResponse
{
  $message = Messages::with(['sender', 'receiver'])->create($data);
  
  // L'événement est automatiquement dispatché via el modèle
  // Le frontend reçoit le message instantanément via WebSocket
  
  return response()->json([...], 201);
}
```

---

### 11. Statut en ligne/hors ligne

**À implémenter:**

```php
// Backend - Event pour le statut utilisateur
class UserPresenceChanged implements ShouldBroadcast
{
    public function __construct(
        public User $user,
        public bool $isOnline
    ) {}
    
    public function broadcastOn(): array
    {
        return [
            new Channel('presence.online'),
        ];
    }
}

// Frontend
subscribeToPresence = () => {
  const channel = echo.channel('presence.online')
  
  channel.here((users) => {
    console.log('Utilisateurs en ligne:', users)
  })
  .joining((user) => {
    console.log(user.name, 'vient d\'arriver')
  })
  .leaving((user) => {
    console.log(user.name, 'a quitté')
  })
}
```

---

### 12. Redis/Scaling (pour production)

**Configuration:**

```env
# .env backend
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# config/reverb.php
'scaling' => [
    'enabled' => env('REVERB_SCALING_ENABLED', false),
    'channel' => env('REVERB_SCALING_CHANNEL', 'reverb'),
    'server' => [
        'host' => env('REDIS_HOST'),
        'port' => env('REDIS_PORT'),
        'password' => env('REDIS_PASSWORD'),
    ],
],
```

---

### 13. Logs et Debugging

**Vérifier les logs Reverb:**
```bash
# Dans le terminal Reverb, vous verrez:
[12:30:15] User 1 connected
[12:30:20] Broadcast to chat.1_2
[12:30:25] User 2 received message
```

**Logs Laravel:**
```bash
tail -f storage/logs/laravel.log
```

**Logs Frontend (Console):**
```javascript
// Tous les événements WebSocket sont loggés
console.log('Message reçu:', messageData)
```

---

## Checklist complète

- [ ] `BROADCAST_CONNECTION=reverb` dans `.env` backend
- [ ] `php artisan reverb:start` en cours d'exécution
- [ ] Variables d'environnement frontend configurées
- [ ] Token d'authentification valide et présent
- [ ] Migrations exécutées : `php artisan migrate`
- [ ] Service websocket importé et initialisé
- [ ] Composable useChat utilisé dans la vue
- [ ] Console affiche "WebSocket connecté"
- [ ] Messages transitent par l'API au chargement
- [ ] Nouveaux messages reçus en temps réel via WebSocket

---

## Ressources

- [Laravel Broadcasting](https://laravel.com/docs/broadcasting)
- [Reverb Documentation](https://laravel.com/docs/reverb)
- [Laravel Echo](https://github.com/laravel/echo)
- [Pusher JS](https://pusher.com/docs/channels/pusher_js/)

