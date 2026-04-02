<?php

namespace App\Repositories\Eloquent;

use App\Models\Item;
use App\Repositories\Contracts\ItemRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;

class ItemRepository implements ItemRepositoryInterface
{
    /** @return LengthAwarePaginator<int, Item> */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return QueryBuilder::for(Item::class)
            ->allowedFilters(['title'])
            ->allowedSorts(['title', 'created_at'])
            ->defaultSort('-created_at')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Item
    {
        return Item::findOrFail($id);
    }

    /** @param  array<string, mixed>  $data */
    public function create(array $data): Item
    {
        return Item::create($data);
    }

    /** @param  array<string, mixed>  $data */
    public function update(Item $item, array $data): Item
    {
        $item->update($data);

        return $item->fresh();
    }

    public function delete(Item $item): void
    {
        $item->delete();
    }
}
