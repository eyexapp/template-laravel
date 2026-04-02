<?php

namespace App\Actions\Item;

use App\DTOs\Item\UpdateItemData;
use App\Models\Item;
use App\Repositories\Contracts\ItemRepositoryInterface;

class UpdateItemAction
{
    public function __construct(
        private readonly ItemRepositoryInterface $repository,
    ) {}

    public function execute(Item $item, UpdateItemData $data): Item
    {
        return $this->repository->update($item, $data->toArray());
    }
}
