<?php

namespace App\Listeners;

use App\Events\ItemCreated;
use App\Jobs\ProcessItemCreated;

class SendItemCreatedNotification
{
    public function handle(ItemCreated $event): void
    {
        ProcessItemCreated::dispatch($event->item);
    }
}
