<?php

namespace App\Actions\Item;

use App\DTOs\Item\CreateItemData;
use App\Events\ItemCreated;
use App\Models\Item;
use App\Repositories\Contracts\ItemRepositoryInterface;

class CreateItemAction
{
    public function __construct(
        private readonly ItemRepositoryInterface $repository,
    ) {}

    public function execute(CreateItemData $data, int $userId): Item
    {
        $item = $this->repository->create([
            ...$data->toArray(),
            'user_id' => $userId,
        ]);

        event(new ItemCreated($item));

        return $item;
    }
}
