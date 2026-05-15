<?php

namespace Susantokun\FilamentDynamicMenu\Observers;

use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Susantokun\FilamentDynamicMenu\Models\MenuGroup;

class MenuGroupObserver
{
    public function saved(MenuGroup $menuGroup): void
    {
        $this->flushMenuCache($menuGroup);
    }

    public function deleted(MenuGroup $menuGroup): void
    {
        $this->flushMenuCache($menuGroup);
    }

    protected function flushMenuCache(MenuGroup $menuGroup): void
    {
        $tenantId = $menuGroup->tenant_id ?? 'default';
        $prefix = config('filament-dynamic-menu.cache.prefix', 'filament_dynamic_menu');
        $key = "{$prefix}_tenant_{$tenantId}_menu";

        try {
            if (Cache::getStore() instanceof TaggableStore) {
                Cache::tags(["{$prefix}_tenant_{$tenantId}", 'menu'])->flush();
            } else {
                Cache::forget($key);
            }
        } catch (\BadMethodCallException) {
            Cache::forget($key);
        }
    }
}
