<?php

namespace Susantokun\FilamentDynamicMenu\Observers;

use Illuminate\Support\Facades\Cache;
use Susantokun\FilamentDynamicMenu\Models\MenuSidebar;

class MenuSidebarObserver
{
    public function saved(MenuSidebar $menuSidebar): void
    {
        $tenantId = $menuSidebar->tenant_id ?? 'default';
        $prefix = config('filament-dynamic-menu.cache.prefix', 'filament_dynamic_menu');
        Cache::forget("{$prefix}_tenant_{$tenantId}_menu");
        Cache::forget("{$prefix}_tenant_{$tenantId}_ungrouped_items");
    }
}
