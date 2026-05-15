<?php

namespace Susantokun\FilamentDynamicMenu\Observers;

use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Susantokun\FilamentDynamicMenu\Models\MenuItem;

class MenuItemObserver
{
    public function saved(MenuItem $menuItem): void
    {
        $this->flushMenuCache($menuItem);
    }

    public function deleted(MenuItem $menuItem): void
    {
        $this->flushMenuCache($menuItem);
    }

    protected function flushMenuCache(MenuItem $menuItem): void
    {
        $tenantId = $menuItem->tenant_id ?? 'default';
        $prefix = config('filament-dynamic-menu.cache.prefix', 'filament_dynamic_menu');
        $key = "{$prefix}_tenant_{$tenantId}_menu";
        $ungroupedKey = "{$prefix}_tenant_{$tenantId}_ungrouped_items";

        try {
            if (Cache::getStore() instanceof TaggableStore) {
                Cache::tags(["{$prefix}_tenant_{$tenantId}", 'menu'])->flush();
            } else {
                Cache::forget($key);
                Cache::forget($ungroupedKey);
            }
        } catch (\BadMethodCallException) {
            Cache::forget($key);
            Cache::forget($ungroupedKey);
        }
    }
}
