<?php

namespace App\Actions\Item;

use App\Models\Item;
use App\Repositories\Contracts\ItemRepositoryInterface;

class DeleteItemAction
{
    public function __construct(
        private readonly ItemRepositoryInterface $repository,
    ) {}

    public function execute(Item $item): void
    {
        $this->repository->delete($item);
    }
}
