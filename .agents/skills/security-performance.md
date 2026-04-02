---
name: security-performance
type: knowledge
version: 1.0.0
agent: CodeActAgent
triggers:
  - security
  - performance
  - sanctum
  - cache
  - n+1
  - rate limit
---

# Security & Performance — Laravel 11

## Performance

### Eager Loading (N+1 Prevention)

```php
// ❌ N+1 problem
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // Separate query per post
}

// ✅ Eager load
$posts = Post::with('author')->get();

// ✅ Nested eager loading
$posts = Post::with(['author', 'comments.user'])->get();

// ✅ Prevent lazy loading in development
Model::preventLazyLoading(!app()->isProduction());
```

### Caching

```php
// Cache expensive queries
$users = Cache::remember('active-users', 3600, function () {
    return User::active()->with('posts')->get();
});

// Cache tags for grouped invalidation
Cache::tags(['users'])->put('user:'.$id, $user, 3600);
Cache::tags(['users'])->flush();
```

### Query Optimization

```php
// Select only needed columns
User::select('id', 'name', 'email')->paginate(15);

// Chunk large datasets
User::chunk(200, function ($users) {
    foreach ($users as $user) {
        // Process
    }
});

// Cursor for memory efficiency
foreach (User::cursor() as $user) {
    // Process one at a time
}
```

### Route Caching

```bash
php artisan route:cache    # Production
php artisan config:cache   # Production
php artisan view:cache     # Production
php artisan optimize       # All of the above
```

## Security

### Sanctum Authentication

```php
// Install
composer require laravel/sanctum
php artisan install:api

// Issue token
$token = $user->createToken('api-token', ['read', 'write']);

// Protect routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('posts', PostController::class);
});
```

### Authorization (Policies)

```php
class PostPolicy
{
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->isAdmin();
    }
}

// In controller
$this->authorize('update', $post);
```

### Rate Limiting

```php
// bootstrap/app.php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

### Mass Assignment Protection

```php
// Always use $fillable (whitelist)
protected $fillable = ['name', 'email'];

// Never use $guarded = [] in production
```

### CSRF & CORS

```php
// config/cors.php
'allowed_origins' => ['https://myapp.com'],
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
'supports_credentials' => true,
```

### Encryption

```php
// Encrypt sensitive data
$encrypted = Crypt::encryptString($apiKey);
$decrypted = Crypt::decryptString($encrypted);
```
