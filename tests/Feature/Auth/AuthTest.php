<?php

use App\Models\User;

it('registers a new user', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => ['user' => ['id', 'name', 'email'], 'token'],
            'message',
        ]);

    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

it('fails to register with duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('error.code', 'VALIDATION_ERROR')
        ->assertJsonPath('error.details.email.0', fn ($v) => str_contains($v, 'email'));
});

it('logs in with valid credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['user' => ['id', 'name', 'email'], 'token'],
            'message',
        ]);
});

it('fails to login with wrong password', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrongpassword',
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('error.code', 'VALIDATION_ERROR')
        ->assertJsonPath('error.details.email.0', fn ($v) => str_contains($v, 'credentials'));
});

it('returns authenticated user profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/me');

    $response->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});

it('logs out and revokes token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('/api/v1/auth/logout');

    $response->assertOk()
        ->assertJsonStructure(['message']);

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('rejects unauthenticated access to protected routes', function () {
    $this->getJson('/api/v1/auth/me')
        ->assertUnauthorized();

    $this->postJson('/api/v1/auth/logout')
        ->assertUnauthorized();
});
