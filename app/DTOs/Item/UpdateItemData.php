<?php

namespace App\DTOs\Item;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UpdateItemData extends Data
{
    public function __construct(
        public readonly string|Optional $title = new Optional,
        public readonly string|null|Optional $description = new Optional,
    ) {}
}
