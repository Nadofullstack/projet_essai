# 🚀 GUIDE: PRÉVENIR LES TIMEOUTS À L'AVENIR

## 🛡️ Bonnes Pratiques de Développement

### 1️⃣ RÈGLE: Toujours Limiter les Résultats

```php
// ❌ DANGER: Peut timeout
Route::get('/messages', function () {
    return Messages::all();  // Récupère TOUT
});

// ✅ BON: Limité & sûr
Route::get('/messages', function () {
    return Messages::latest()->limit(50)->get();
});
```

### 2️⃣ RÈGLE: Utiliser Select() pour les Colonnes

```php
// ❌ DANGER: Charge tout
$users = User::where('active', true)->get();

// ✅ BON: Charger le strict nécessaire
$users = User::where('active', true)
    ->select('id', 'name', 'email')
    ->get();
```

### 3️⃣ RÈGLE: Pagination par Défaut

```php
// ❌ DANGER: N-ième page de 10,000 résultats
$posts = Post::paginate(10);

// ✅ BON: Limiter le curseur
$posts = Post::where('category_id', $categoryId)
    ->select('id', 'title', 'excerpt')
    ->latest()
    ->limit(100)
    ->get();
    
// ET EN FRONTEND: Implémenter "Load More"
```

### 4️⃣ RÈGLE: Timeout = Buffer de Sécurité

```javascript
// ❌ DANGER: 5 secondes c'est trop court
const api = axios.create({ timeout: 5000 })

// ✅ BON: 30-45 secondes pour les opérations lentes
const api = axios.create({ timeout: 30000 })

// ✅ MEILLEUR: Timeout différent par endpoint
api.get('/messages/search', { timeout: 60000 })  // Opération lente
api.get('/users/profile', { timeout: 5000 })     // Opération rapide
```

---

## 📋 Checklist Avant de Livrer une Feature

### Backend (PHP/Laravel)

- [ ] **LIMIT**: Chaque query a `.limit()`?
  ```php
  ✅ ::latest()->limit(100)
  ❌ ::all()
  ```

- [ ] **SELECT**: Charger que les colonnes nécessaires?
  ```php
  ✅ ->select('id', 'name', 'email')
  ❌ ->select('*')
  ```

- [ ] **INDEX**: Colonnes WHERE/JOIN sont indexées?
  ```sql
  ✅ CREATE INDEX idx_user_messages ON messages(user_id, created_at)
  ❌ Pas d'index
  ```

- [ ] **N+1**: Pas de requête par boucle?
  ```php
  ✅ Users::with('posts')->get()  // 2 queries
  ❌ Users::get(); foreach($u->posts)  // N+1 queries
  ```

- [ ] **Logs**: Logger les temps lents?
  ```php
  ✅ if(microtime() > 2) Log::warning("Slow query");
  ❌ Pas de monitoring
  ```

### Frontend (JavaScript/Vue)

- [ ] **TIMEOUT**: Timeout configuré (>30s)?
  ```javascript
  ✅ timeout: 45000
  ❌ timeout: 5000
  ```

- [ ] **PAGINATION**: Afficher "Charger plus" si data > limit?
  ```javascript
  ✅ if(response.total_count > 100) showLoadMoreButton()
  ❌ Supposer que tout est chargé
  ```

- [ ] **LOADING**: UI feedback pendant chargement?
  ```vue
  ✅ <div v-if="loading">Chargement...</div>
  ❌ Rien, interface gelée
  ```

- [ ] **ERROR**: Afficher erreur timeout spécifiquement?
  ```javascript
  ✅ if(err.code === 'ECONNABORTED') showTimeoutError()
  ❌ Generic error message
  ```

- [ ] **MONITORING**: Logger les temps de requête?
  ```javascript
  ✅ console.time('load-messages'); ... console.timeEnd()
  ❌ Pas de monitoring
  ```

---

## 🔨 Outils de Diagnostic

### Test 1: Mesurer le Temps de Requête

```bash
# Bash/cURL
time curl http://localhost:8000/api/messages/conversation/1 \
  -H "Authorization: Bearer TOKEN"

# Devrait prendre < 2 secondes
```

### Test 2: Analyser Query SQL Performance

```php
// Dans tinker ou contrôleur
use Illuminate\Support\Facades\DB;

DB::enableQueryLog();
$messages = Messages::where(...)->get();
dd(DB::getQueryLog());
// Affiche: combien de queries? Combien de temps chaque?
```

### Test 3: Tester avec Jmeter

```xml
<!-- Pour charger 100 utilisateurs simultanés -->
<TestPlan>
  <ThreadGroup numThreads="100" rampTime="10">
    <HTTPSampler method="GET" url="/api/messages/conversation/1"/>
  </ThreadGroup>
</TestPlan>
```

### Test 4: DevTools Firefox/Chrome

```
1. F12 → Network tab
2. Cliquer sur une conversation
3. Voir le temps complet en colonne "Time"
4. Devrait être < 3 secondes (pas 15+)
```

---

## 🎯 Patterns à Éviter

### ❌ Pattern 1: Charger TOUT pour un Filtre

```php
// MAUVAIS: Charge 10,000 messages en mémoire pour filtrer 10
public function getUnreadMessages() {
    $messages = Messages::all();  // ← 10,000 résultats
    return $messages->where('is_read', false);  // ← Filtre en PHP
}

// BON: Filtrer à la DB
public function getUnreadMessages() {
    return Messages::where('is_read', false)
        ->select('id', 'content', 'created_at')
        ->limit(50)
        ->get();
}
```

### ❌ Pattern 2: Requête dans Boucle

```php
// MAUVAIS: N+1 queries (1 + N)
$users = User::all();  // Query 1
foreach ($users as $user) {
    echo $user->posts->count();  // Queries 2-N
}

// BON: Eager loading
$users = User::with('posts')->get();  // Query 1 + 1
foreach ($users as $user) {
    echo $user->posts->count();  // Pas de query supplémentaire
}
```

### ❌ Pattern 3: Timeout Trop Court

```javascript
// MAUVAIS: 5 secondes pour file upload
const api = axios.create({ timeout: 5000 });
api.post('/upload', formData);  // File 20MB = timeout

// BON: Timeout long pour operations lentes
api.post('/upload', formData, { timeout: 300000 });  // 5 minutes
```

---

## 📊 Métriques à Monitorer

### Backend Metrics
```php
// Ajouter dans votre middleware
use Illuminate\Support\Facades\Log;

if ($requestTime > 2) {
    Log::channel('slow-queries')->warning('Slow request', [
        'url' => $request->path(),
        'time' => $requestTime,
        'query_count' => count(DB::getQueryLog())
    ]);
}
```

### Frontend Metrics
```javascript
// Dashboard de monitoring
api.interceptors.response.use(
    (response) => {
        const time = response.headers['x-response-time'];
        analytics.trackRequestTime(response.config.url, time);
        return response;
    }
);
```

---

## 🔍 Debugging Checklist

Si vous avez un timeout:

### 1. Vérifier le Timeout Configuré
```javascript
console.log("Timeout:", api.defaults.timeout);  // Doit être 30000+
```

### 2. Analyser les Données
```php
// Combien de messages?
dd(Messages::count());
// Si > 5000, le problème c'est probablement ça
```

### 3. Profiler la Query
```php
DB::listen(function ($query) {
    if ($query->time > 1000) {
        Log::error("SLOW QUERY: " . $query->sql . " took " . $query->time . "ms");
    }
});
```

### 4. Tester la Requête Manuellement
```bash
curl "http://localhost:8000/api/messages/conversation/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -w "\nResponse size: %{size_download} bytes\nTime: %{time_total}s\n"
```

### 5. Vérifier Network Tab
- DevTools → Network
- Cliquer sur la requête lente
- Vérifier: Taille + Timing

---

## ✅ Fix Verification Checklist

Après avoir appliqué le fix:

- [ ] Console montre "✅ X messages chargés (total: Y)"
- [ ] Pas de message d'erreur timeout
- [ ] Temps de chargement < 3 secondes
- [ ] UI responsive (pas gelée)
- [ ] Peut cliquer sur plusieurs conversations rapidement

---

## 🎓 Ressources Utiles

### Documentation
- [Laravel: Database Queries Best Practices](https://laravel.com/docs/queries)
- [Laravel: Eager Loading (N+1 prevention)](https://laravel.com/docs/eloquent-relationships#eager-loading)
- [Axios: Timeout Configuration](https://axios-http.com/docs/reqconfig)

### Tools
- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) - Voir les queries en dev
- [Chrome DevTools](https://developer.chrome.com/docs/devtools/) - Analyser network
- [Postman](https://www.postman.com/) - Tester API directement

### Commands Utiles
```bash
# Laravel Tinker pour tester queries
php artisan tinker
>>> Messages::count()
>>> DB::enableQueryLog(); Messages::where(...)->get(); dd(DB::getQueryLog());
```

---

## 🎯 Résumé Rapide

| Problème | Solution | Impact |
|----------|----------|--------|
| Charger tout | `.limit()` | -80% temps |
| Colonnes inutiles | `.select()` | -20% temps |
| N+1 queries | `.with()` | -50% requêtes |
| Timeout court | Augmenter à 45s | Évite timeout |
| Pas de feedback | Loading spinner | UX meilleure |

---

**Après ces changements, vous ne devriez JAMAIS revoir ce timeout!** ✅
