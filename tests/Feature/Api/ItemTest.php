<?php

use App\Models\Item;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('lists paginated items', function () {
    Item::factory()->count(3)->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/items');

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'title', 'description', 'user_id', 'created_at', 'updated_at']],
            'links',
            'meta',
        ]);
});

it('creates a new item', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/items', [
            'title' => 'New Item',
            'description' => 'Item description',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'New Item')
        ->assertJsonPath('data.user_id', $this->user->id);

    $this->assertDatabaseHas('items', ['title' => 'New Item']);
});

it('validates required fields on create', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/items', []);

    $response->assertUnprocessable()
        ->assertJsonPath('error.code', 'VALIDATION_ERROR')
        ->assertJsonPath('error.details.title.0', fn ($v) => str_contains($v, 'title'));
});

it('shows a single item', function () {
    $item = Item::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/items/{$item->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $item->id)
        ->assertJsonPath('data.title', $item->title);
});

it('returns 404 for non-existent item', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/items/99999');

    $response->assertNotFound();
});

it('updates an existing item', function () {
    $item = Item::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson("/api/v1/items/{$item->id}", [
            'title' => 'Updated Title',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.title', 'Updated Title');

    $this->assertDatabaseHas('items', ['id' => $item->id, 'title' => 'Updated Title']);
});

it('deletes an item', function () {
    $item = Item::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/v1/items/{$item->id}");

    $response->assertNoContent();

    $this->assertSoftDeleted('items', ['id' => $item->id]);
});

it('filters items by title', function () {
    Item::factory()->create(['title' => 'Alpha', 'user_id' => $this->user->id]);
    Item::factory()->create(['title' => 'Beta', 'user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/items?filter[title]=Alpha');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Alpha');
});

it('sorts items by title', function () {
    Item::factory()->create(['title' => 'Banana', 'user_id' => $this->user->id]);
    Item::factory()->create(['title' => 'Apple', 'user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/items?sort=title');

    $response->assertOk();

    $titles = collect($response->json('data'))->pluck('title')->all();
    expect($titles)->toBe(['Apple', 'Banana']);
});

it('rejects unauthenticated item access', function () {
    $this->getJson('/api/v1/items')->assertUnauthorized();
    $this->postJson('/api/v1/items', ['title' => 'Test'])->assertUnauthorized();
});
