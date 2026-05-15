<?php

namespace Susantokun\FilamentDynamicMenu\Commands;

use Illuminate\Console\Command;
use Susantokun\FilamentDynamicMenu\Facades\DynamicMenu;

class ClearCacheCommand extends Command
{
    protected $signature = 'filament-dynamic-menu:clear-cache
                           {tenantId? : The tenant ID to clear cache for}';

    protected $description = 'Clear the dynamic menu cache for a specific tenant';

    public function handle(): int
    {
        $tenantId = $this->argument('tenantId');

        DynamicMenu::clearCache($tenantId);

        $this->info('Dynamic menu cache cleared'.($tenantId ? " for tenant '{$tenantId}'." : '.'));

        return self::SUCCESS;
    }
}
