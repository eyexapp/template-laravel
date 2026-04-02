---
name: code-quality
type: knowledge
version: 1.0.0
agent: CodeActAgent
triggers:
  - code quality
  - naming
  - eloquent
  - pint
  - types
---

# Code Quality — Laravel 11

## Naming Conventions

| Element | Convention | Example |
|---------|-----------|---------|
| Controller | PascalCase + Controller | `UserController` |
| Model | Singular PascalCase | `User`, `BlogPost` |
| Migration | snake_case with timestamp | `2024_01_01_create_users_table` |
| Table | plural snake_case | `users`, `blog_posts` |
| Column | snake_case | `created_at`, `user_id` |
| Factory | PascalCase + Factory | `UserFactory` |
| Seeder | PascalCase + Seeder | `UserSeeder` |
| Request | PascalCase + Request | `StoreUserRequest` |
| Resource | PascalCase + Resource | `UserResource` |
| Policy | PascalCase + Policy | `UserPolicy` |
| Route | plural kebab-case | `/api/blog-posts` |

## Eloquent Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
```

## Laravel Pint (Formatting)

```json
// pint.json
{
    "preset": "laravel",
    "rules": {
        "declare_strict_types": true,
        "final_class": true
    }
}
```

```bash
./vendor/bin/pint
./vendor/bin/pint --test  # CI dry-run
```

## Collections

```php
// Use collection methods over loops
$activeUsers = User::where('active', true)
    ->get()
    ->map(fn (User $user) => [
        'id' => $user->id,
        'name' => $user->name,
    ])
    ->filter(fn ($u) => $u['name'] !== null);
```

## Scopes

```php
// Model scopes for reusable queries
class User extends Model
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }
}

// Usage
User::active()->role('admin')->paginate();
```

## Migration Best Practices

```php
return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['user', 'admin'])->default('user');
            $table->timestamps();
            $table->softDeletes();

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```
