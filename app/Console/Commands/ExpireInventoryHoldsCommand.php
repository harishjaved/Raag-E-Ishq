<?php

namespace App\Console\Commands;

use App\Services\InventoryService;
use Illuminate\Console\Command;

class ExpireInventoryHoldsCommand extends Command
{
    protected $signature = 'app:expire-inventory-holds';
    protected $description = 'Release expired inventory holds and mark pending bookings as expired';

    public function handle(InventoryService $inventoryService): int
    {
        $releasedCount = $inventoryService->releaseExpiredHolds();
        if ($releasedCount > 0) {
            $this->info("Successfully released {$releasedCount} expired inventory hold(s).");
        } else {
            $this->line("No expired holds to release.");
        }

        return Command::SUCCESS;
    }
}
