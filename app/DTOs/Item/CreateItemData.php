<?php

namespace App\DTOs\Item;

use Spatie\LaravelData\Data;

class CreateItemData extends Data
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description = null,
    ) {}
}
