---
name: testing
type: knowledge
version: 1.0.0
agent: CodeActAgent
triggers:
  - test
  - phpunit
  - pest
  - factory
  - feature test
---

# Testing — Laravel 11 (PHPUnit / Pest)

## Feature Test

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_users(): void
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_user(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => 'Alice',
            'email' => 'alice@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Alice');

        $this->assertDatabaseHas('users', ['email' => 'alice@test.com']);
    }

    public function test_create_user_validates_email(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => 'Alice',
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
```

## Unit Test

```php
<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\UserService;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    public function test_password_is_hashed(): void
    {
        // Unit tests for pure logic
        $this->assertTrue(strlen(bcrypt('test')) > 50);
    }
}
```

## Factories

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'role' => 'user',
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }
}

// Usage in tests
User::factory()->admin()->create();
User::factory()->count(5)->create();
```

## Auth Testing

```php
public function test_authenticated_user_can_access(): void
{
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/profile');

    $response->assertOk();
}

public function test_unauthenticated_returns_401(): void
{
    $this->getJson('/api/profile')
        ->assertUnauthorized();
}
```

## Running Tests

```bash
php artisan test
php artisan test --filter=UserControllerTest
php artisan test --coverage
php artisan test --parallel
```
