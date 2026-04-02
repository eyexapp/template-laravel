<?php

namespace App\Repositories\Contracts;

use App\Models\Item;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ItemRepositoryInterface
{
    /** @return LengthAwarePaginator<int, Item> */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Item;

    /** @param  array<string, mixed>  $data */
    public function create(array $data): Item;

    /** @param  array<string, mixed>  $data */
    public function update(Item $item, array $data): Item;

    public function delete(Item $item): void;
}
