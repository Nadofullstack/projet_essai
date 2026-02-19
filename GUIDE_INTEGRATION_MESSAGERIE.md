# 🚀 Guide d'Intégration - Messagerie Complète

## 📥 Fichiers créés/modifiés

### Frontend
```
✓ frontend/src/stores/messages.js (AMÉLIORÉ)
  - Ajout de fetchAvailableUsers()
  - Amélioration de searchUsers()
  - Meilleur filtrage et tri

✓ frontend/src/views/Messages.vue (EXISTANT - compatible)
  - Utilise déjà messagesStore.searchUsers()
  - Utilise déjà startConversationWithUser()
  - Support complet de la recherche d'utilisateurs

✓ frontend/src/components/Messages/MessagesList.vue (NOUVEAU)
  - Composant de liste des conversations
  - Onglets conversations/contacts
  - Recherche intégrée

✓ frontend/src/components/Messages/ChatWindow.vue (NOUVEAU)
  - Interface de chat
  - Affichage des messages
  - Zone d'input

✓ frontend/src/components/Messages/MessagesContainer.vue (NOUVEAU)
  - Conteneur principal
  - Layout deux colonnes
```

### Backend (Exemple)
```
✓ backend/app/Http/Controllers/MessagesControllerExample.php (NOUVEAU)
  - Implémentation exemple de MessagesController
  - À adapter à votre structure existante
```

### Documentation
```
✓ FONCTIONNALITE_MESSAGERIE_COMPLETE.md
  - Documentation complète de la fonctionnalité
  - Architecture détaillée
  - Endpoints API requis

✓ GUIDE_INTEGRATION_MESSAGERIE.md (CE FICHIER)
  - Instructions d'intégration rapides
  - Checklist de vérification
```

## ✅ Checklist d'intégration

### 1️⃣ Frontend - Vérifier que tout est en place

```bash
# Aller dans le dossier frontend
cd frontend

# Vérifier que Axios est installé
npm list axios
# Si absent: npm install axios

# Vérifier que Pinia est installé
npm list pinia
# Si absent: npm install pinia
```

### 2️⃣ Backend - Vérifier les routes API

Dans votre fichier `backend/routes/api.php`, assurez-vous d'avoir:

```php
Route::middleware('auth:sanctum')->group(function () {
    // Routes de messages
    Route::get('/messages/users/list', [MessagesController::class, 'listUsers']);
    Route::get('/messages/conversations', [MessagesController::class, 'conversations']);
    Route::get('/messages/{userId}', [MessagesController::class, 'show']);
    Route::post('/messages', [MessagesController::class, 'store']);
    Route::put('/messages/{userId}/read', [MessagesController::class, 'markAsRead']);
    Route::get('/messages/unread', [MessagesController::class, 'unread']);
});
```

### 3️⃣ Backend - Vérifier le modèle Message

Vous devez avoir un modèle `Message` avec ces attributs:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'content', 'is_read'];
    protected $casts = ['is_read' => 'boolean'];
    
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
```

### 4️⃣ Backend - Vérifier la migration

Assurez-vous que votre migration `messages` existe:

```php
Schema::create('messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
    $table->text('content');
    $table->boolean('is_read')->default(false);
    $table->timestamp('read_at')->nullable();
    $table->timestamps();
    
    $table->index(['sender_id', 'receiver_id']);
    $table->index('created_at');
});
```

Si la migration n'existe pas, créez-la:

```bash
cd backend
php artisan make:migration create_messages_table
```

Puis exécutez les migrations:

```bash
php artisan migrate
```

### 5️⃣ Backend - Adapter le MessagesController

Si vous avez déjà un `MessagesController`, adaptez-le en utilisant l'exemple fourni dans `MessagesControllerExample.php`.

**Points clés à implémenter:**
- ✅ `listUsers()` - Retourner tous les utilisateurs sauf celui connecté
- ✅ `conversations()` - Retourner les conversations de l'utilisateur
- ✅ `show($userId)` - Retourner les messages avec un utilisateur
- ✅ `store()` - Créer un nouveau message
- ✅ `markAsRead()` - Marquer un message comme lu

### 6️⃣ Frontend - Tester la recherche d'utilisateurs

```bash
# Démarrer le serveur frontend
cd frontend
npm run dev

# Ouvrir dans le navigateur
http://localhost:5173/messages
```

**Test:**
1. Ouvrir la console (F12)
2. Chercher les logs: `✓ Utilisateurs disponibles chargés`
3. Essayer de rechercher un utilisateur dans le champ
4. Vérifier que la liste s'affiche

### 7️⃣ Backend - Démarrer le serveur

```bash
cd backend
php artisan serve
# Serveur sur http://localhost:8000
```

### 8️⃣ Test complet end-to-end

**Scénario:**
1. Authentifiez-vous sur http://localhost:5173/messages
2. Recherchez un autre utilisateur par nom
3. Cliquez pour ouvrir la conversation
4. Envoyez un message
5. Vérifiez qu'il apparaît dans la liste

## 🔧 Configuration Avancée

### Variables d'environnement

Assurez-vous que dans `.env.local`:

```
VITE_API_URL=http://localhost:8000
```

### Token d'authentification

Le store récupère automatiquement le token depuis localStorage avec la clé `auth_token`.

Si vous utilisez une clé différente, modifiez dans `frontend/src/stores/messages.js`:

```javascript
// Ligne ~45
const token = localStorage.getItem('votre_cle_token')
```

## 🐛 Débogage

### Logs utiles

**Console navigateur (F12):**
```
✓ Utilisateurs disponibles chargés: 5
✓ Conversations chargées: 3
✓ Message envoyé avec succès
```

**Logs backend:**
```bash
tail -f backend/storage/logs/laravel.log
```

### Vérifier les appels API

```javascript
// Dans la console du navigateur
localStorage.getItem('auth_token')
fetch('http://localhost:8000/api/messages/users/list', {
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN'
  }
}).then(r => r.json()).then(console.log)
```

## 📱 Points d'entrée

### Route Vue Router

Vérifiez que vous avez la route dans `frontend/src/router/index.js`:

```javascript
{
  path: '/messages',
  name: 'Messages',
  component: () => import('@/views/Messages.vue'),
  meta: { requiresAuth: true }
}
```

### Lien dans la navigation

Assurez-vous qu'il y a un lien vers `/messages` dans votre navigation (Sidebar, Header, etc).

## 🎉 Vous êtes prêt!

Si tous les points de la checklist sont cochés ✅, la fonctionnalité de messagerie complète est prête à l'emploi!

### Prochaines étapes optionnelles:

- [ ] Implémenter les notifications en temps réel (WebSocket)
- [ ] Ajouter un typing indicator ("en train d'écrire...")
- [ ] Permettre le partage de fichiers
- [ ] Implémenter les appels audio/vidéo réels
- [ ] Ajouter les groupes de messages

---

**Besoin d'aide?**

Consultez:
- `FONCTIONNALITE_MESSAGERIE_COMPLETE.md` - Documentation détaillée
- `MessagesControllerExample.php` - Exemple d'implémentation backend
- `frontend/src/stores/messages.js` - Logique du store
- `frontend/src/views/Messages.vue` - Composant principal

**Date**: 11 février 2026
**Status**: ✅ Prêt à intégrer
