<?php

use App\Actions\Item\CreateItemAction;
use App\Models\Item;
use App\Models\User;
use App\DTOs\Item\CreateItemData;

it('creates an item and returns it', function () {
    $user = User::factory()->create();
    $data = CreateItemData::from(['title' => 'Test Item', 'description' => 'A description']);

    $action = app(CreateItemAction::class);
    $item = $action->execute($data, $user->id);

    expect($item)
        ->toBeInstanceOf(Item::class)
        ->title->toBe('Test Item')
        ->description->toBe('A description')
        ->user_id->toBe($user->id);
});

it('creates an item without description', function () {
    $user = User::factory()->create();
    $data = CreateItemData::from(['title' => 'No Desc']);

    $action = app(CreateItemAction::class);
    $item = $action->execute($data, $user->id);

    expect($item)
        ->title->toBe('No Desc')
        ->description->toBeNull();
});
