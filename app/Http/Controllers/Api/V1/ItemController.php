<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Item\CreateItemAction;
use App\Actions\Item\DeleteItemAction;
use App\Actions\Item\UpdateItemAction;
use App\DTOs\Item\CreateItemData;
use App\DTOs\Item\UpdateItemData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Item\StoreItemRequest;
use App\Http\Requests\Item\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Repositories\Contracts\ItemRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    public function __construct(
        private readonly ItemRepositoryInterface $repository,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $items = $this->repository->paginate();

        return ItemResource::collection($items);
    }

    public function store(StoreItemRequest $request, CreateItemAction $action): JsonResponse
    {
        $data = CreateItemData::from($request->validated());
        $item = $action->execute($data, $request->user()->id);

        return (new ItemResource($item))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(int $id): ItemResource
    {
        $item = $this->repository->findOrFail($id);

        return new ItemResource($item);
    }

    public function update(UpdateItemRequest $request, int $id, UpdateItemAction $action): ItemResource
    {
        $item = $this->repository->findOrFail($id);
        $data = UpdateItemData::from($request->validated());
        $item = $action->execute($item, $data);

        return new ItemResource($item);
    }

    public function destroy(int $id, DeleteItemAction $action): JsonResponse
    {
        $item = $this->repository->findOrFail($id);
        $action->execute($item);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
