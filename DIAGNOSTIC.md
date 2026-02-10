# 🔍 Diagnostic des Erreurs d'Envoi de Messages

## Problème #1: ERR_CONNECTION_REFUSED (ligne 195 messages.js)

### Cause
L'appel `api.post('/messages', ...)` échoue car le serveur **n'est pas accessible**.

### Solutions
1. **Vérifier que le backend est démarré:**
   ```bash
   cd backend
   php artisan serve
   # Doit afficher: Server running on [http://127.0.0.1:8000]
   ```

2. **Tester l'endpoint directement:**
   ```bash
   curl -i http://localhost:8000/api/health
   # Doit retourner 200 OK avec: {"status":"ok","timestamp":"..."}
   ```

3. **Vérifier que le frontend utilise la bonne URL:**
   - Fichier: `frontend/src/services/api.js`
   - Doit avoir: `const API_BASE_URL = 'http://localhost:8000'`

---

## Problème #2: 401 Unauthorized (ligne 35 messages.js)

### Cause
Après l'erreur d'envoi, le code essaie d'appeler `/messages/conversations` sans token valide.

### Pourquoi
1. Pas de token en localStorage
2. Le token est expiré
3. Le endpoint demande `auth:sanctum` middleware

### Solutions
1. **Vérifier le token en localStorage:**
   - Ouvrir DevTools → Application → Local Storage
   - Chercher la clé `auth_token`
   - Le token doit exister et commencer par une chaîne valide

2. **Obtenir un token de test:**
   ```bash
   curl -i http://localhost:8000/api/auth/test-token
   # Doit retourner 200 avec: {"success":true,"token":"...","user":{...}}
   ```

3. **Mettre à jour le token manuellement:**
   ```javascript
   // Dans la console du navigateur
   localStorage.setItem('auth_token', 'VOTRE_TOKEN_ICI')
   location.reload()
   ```

---

## Problème #3: Lenteur d'Envoi

### Cause Originale
Timeout configuré à **30 secondes** dans `api.js`

### Solution Appliquée ✅
Réduit à **5 secondes** pour une meilleure UX

---

## ✅ Changements Effectués

### 1. **api.js** - Configuration API
- ✅ Timeout réduit: 30s → 5s
- ✅ Meilleure gestion des erreurs 401
- ✅ Support du mode démo avec tokens de démo

### 2. **messages.js** - Store des Messages
- ✅ Optimistic Update: ajouter le message avant confirmation serveur
- ✅ Statuts de message améliorés: `pending` → `sent` → `failed`
- ✅ Meilleur diagnostic d'erreur avec codes spécifiques
- ✅ N'affiche plus le fallback automatiquement

### 3. **Messages.vue** - Vue des Messages
- ✅ Affichage d'erreurs claires à l'utilisateur
- ✅ Input vidé immédiatement (UX amélioré)
- ✅ Scroll automatique au nouveau message

---

## 🧪 Checklist de Vérification

- [ ] Backend Laravel est démarré (`php artisan serve`)
- [ ] Frontend est en développement (`npm run dev`)
- [ ] `GET http://localhost:8000/api/health` retourne 200
- [ ] `GET http://localhost:8000/api/auth/test-token` retourne 200
- [ ] Token est présent dans `localStorage` (DevTools → Application)
- [ ] CORS est bien configuré (voir `backend/config/cors.php`)
- [ ] Base de données SQLite existe (`backend/database/database.sqlite`)

---

## 📊 Flow d'Envoi Corrigé

```
1. Utilisateur tape un message et appuie sur "Envoyer"
   ↓
2. Message ajouté LOCALEMENT avec statut "pending" (optimistic update)
   ↓
3. Input vidé immédiatement (meilleure UX)
   ↓
4. Tentative d'envoi POST /messages avec timeout 5s
   ↓
5. Si succès: statut → "sent" + ID du serveur
   Si échec: afficher erreur + statut → "failed"
   ↓
6. Message visible avec indicateur de statut
```

---

## 🆘 Dépannage Rapide

**Si vous avez toujours des erreurs:**

1. Ouvrir DevTools (F12)
2. Aller à l'onglet "Network"
3. Envoyer un message
4. Chercher la requête POST `/api/messages`
5. Regarder le statut et la réponse
6. Partager les détails pour diagnostic

---

## 📝 Notes

- Les messages avec statut "failed" peuvent être renvoyés manuellement
- Le fallback en mode démo a été retiré pour plus de clarté
- Les erreurs de réseau sont maintenant clairement identifiées
