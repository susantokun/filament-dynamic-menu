<?php

namespace Susantokun\FilamentDynamicMenu\Services;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Susantokun\FilamentDynamicMenu\Models\MenuGroup;
use Susantokun\FilamentDynamicMenu\Models\MenuItem;
use Susantokun\FilamentDynamicMenu\Models\MenuSidebar;

class MenuBuilderService
{
    public function isEnabled(): bool
    {
        return config('filament-dynamic-menu.enabled', false);
    }

    public function tenantMode(): string
    {
        return config('filament-dynamic-menu.tenant_mode', 'single');
    }

    public function resolveTenantId(): ?string
    {
        if ($this->tenantMode() === 'single') {
            return 'default';
        }

        $tenant = Filament::getTenant();

        if ($tenant) {
            return (string) $tenant->id;
        }

        return null;
    }

    public function getTenantId(): ?string
    {
        return $this->resolveTenantId();
    }

    public function ensureSeedData(string $tenantId): void
    {
        try {
            $menuGroups = $this->getMenuGroups($tenantId);
            $ungroupedItems = $this->getUngroupedItems($tenantId);

            if ($menuGroups->isEmpty() && $ungroupedItems->isEmpty()) {
                $this->seedDefaultMenu($tenantId);
                $this->clearCache($tenantId);
            }
        } catch (\Throwable $e) {
            Log::error('FilamentDynamicMenu: ensureSeedData failed: ' . $e->getMessage());
        }
    }

    public function build(NavigationBuilder $builder): NavigationBuilder
    {
        $tenantId = $this->resolveTenantId();

        if (! $tenantId && $this->tenantMode() !== 'single') {
            return $builder;
        }

        $tenantId = $tenantId ?? 'default';

        try {
            if (config('filament-dynamic-menu.auto_seed_on_empty', true)) {
                $this->ensureSeedData($tenantId);
            }

            $menuGroups = $this->getMenuGroups($tenantId);
            $ungroupedItems = $this->getUngroupedItems($tenantId);

            $topItems = $this->buildItems($ungroupedItems);

            $groups = [];
            foreach ($menuGroups as $menuGroup) {
                if ($menuGroup->roles && ! auth()->user()?->hasAnyRole($menuGroup->roles)) {
                    continue;
                }

                $items = $this->buildItems($menuGroup->items);

                if ($items->isEmpty()) {
                    continue;
                }

                $groups[] = NavigationGroup::make($menuGroup->name)
                    ->collapsed($menuGroup->is_collapsed)
                    ->collapsible($menuGroup->is_collapsible)
                    ->items($items->toArray());
            }

            if ($topItems->isNotEmpty()) {
                $builder->items($topItems->toArray());
            }

            if (! empty($groups)) {
                $builder->groups($groups);
            }

            return $builder;
        } catch (\Throwable $e) {
            Log::error('FilamentDynamicMenu: build failed: ' . $e->getMessage(), [
                'exception' => $e,
                'tenant_id' => $tenantId,
            ]);

            return $builder;
        }
    }

    public function getSidebar(int|string $tenantId): MenuSidebar
    {
        $attributes = $this->tenantMode() === 'single'
            ? []
            : ['tenant_id' => $tenantId];

        $defaults = array_merge($attributes, [
            'collapsible_navigation_groups' => true,
            'sidebar_collapsible_on_desktop' => true,
            'sidebar_fully_collapsible_on_desktop' => true,
        ]);

        return MenuSidebar::firstOrCreate($attributes, $defaults);
    }

    public function clearCache(?string $tenantId = null): void
    {
        $tenantId ??= $this->resolveTenantId() ?? 'default';
        $prefix = config('filament-dynamic-menu.cache.prefix', 'filament_dynamic_menu');

        $menuKey = "{$prefix}_tenant_{$tenantId}_menu";
        $ungroupedKey = "{$prefix}_tenant_{$tenantId}_ungrouped_items";

        try {
            if (Cache::getStore() instanceof TaggableStore) {
                Cache::tags(["{$prefix}_tenant_{$tenantId}", 'menu'])->flush();
            } else {
                Cache::forget($menuKey);
                Cache::forget($ungroupedKey);
            }
        } catch (\BadMethodCallException) {
            Cache::forget($menuKey);
            Cache::forget($ungroupedKey);
        }
    }

    protected function seedDefaultMenu(string $tenantId): void
    {
        try {
            $seederClass = config('filament-dynamic-menu.default_seeder');
            if (! $seederClass || ! class_exists($seederClass)) {
                return;
            }

            $seeder = new $seederClass;

            if ($this->tenantMode() !== 'single' && property_exists($seeder, 'tenantId')) {
                $reflection = new \ReflectionClass($seeder);
                $property = $reflection->getProperty('tenantId');
                $property->setValue($seeder, $tenantId);
            }

            $seeder->run();
        } catch (\Throwable $e) {
            Log::error('FilamentDynamicMenu: seed failed: ' . $e->getMessage());
        }
    }

    protected function getUngroupedItems(string $tenantId): Collection
    {
        $prefix = config('filament-dynamic-menu.cache.prefix', 'filament_dynamic_menu');
        $ttl = config('filament-dynamic-menu.cache.ttl', 86400);
        $key = "{$prefix}_tenant_{$tenantId}_ungrouped_items";

        $callback = function () use ($tenantId) {
            $query = MenuItem::whereNull('menu_group_id')
                ->where('is_visible', true)
                ->where('is_active', true)
                ->whereNull('parent_id');

            if ($this->tenantMode() !== 'single') {
                $query->where('tenant_id', $tenantId);
            }

            return $query->with(['children' => function ($q) {
                $q->where('is_visible', true)
                    ->where('is_active', true)
                    ->orderBy('sort_order');
            }])
                ->orderBy('sort_order')
                ->get();
        };

        try {
            if (Cache::getStore() instanceof TaggableStore) {
                return Cache::tags(["{$prefix}_tenant_{$tenantId}", 'menu'])
                    ->remember($key, now()->addSeconds($ttl), $callback);
            }

            return Cache::remember($key, now()->addSeconds($ttl), $callback);
        } catch (\Throwable) {
            return $callback();
        }
    }

    protected function getMenuGroups(string $tenantId): Collection
    {
        $prefix = config('filament-dynamic-menu.cache.prefix', 'filament_dynamic_menu');
        $ttl = config('filament-dynamic-menu.cache.ttl', 86400);
        $key = "{$prefix}_tenant_{$tenantId}_menu";

        $callback = function () use ($tenantId) {
            $query = MenuGroup::where('is_visible', true);

            if ($this->tenantMode() !== 'single') {
                $query->where('tenant_id', $tenantId);
            }

            return $query->with(['items' => function ($q) {
                $q->where('is_visible', true)
                    ->where('is_active', true)
                    ->whereNull('parent_id')
                    ->orderBy('sort_order');
            }, 'items.children' => function ($q) {
                $q->where('is_visible', true)
                    ->where('is_active', true)
                    ->orderBy('sort_order');
            }])
                ->orderBy('sort_order')
                ->get();
        };

        try {
            if (Cache::getStore() instanceof TaggableStore) {
                return Cache::tags(["{$prefix}_tenant_{$tenantId}", 'menu'])
                    ->remember($key, now()->addSeconds($ttl), $callback);
            }

            return Cache::remember($key, now()->addSeconds($ttl), $callback);
        } catch (\Throwable) {
            return $callback();
        }
    }

    protected function buildItems($items): Collection
    {
        $result = collect();

        foreach ($items as $item) {
            try {
                $hasChildren = $item->relationLoaded('children') && $item->children->isNotEmpty();

                $navItem = $this->buildSingleItem($item, $hasChildren);
                if (! $navItem) {
                    continue;
                }

                if ($hasChildren) {
                    $childItems = $this->buildItems($item->children);
                    if ($childItems->isNotEmpty()) {
                        $navItem->childItems($childItems->toArray());
                    }
                }

                $result->push($navItem);
            } catch (\Throwable $e) {
                Log::warning('FilamentDynamicMenu: failed to build item: ' . $e->getMessage(), [
                    'item_id' => $item->id ?? null,
                    'label' => $item->label ?? null,
                ]);
            }
        }

        return $result;
    }

    protected function buildSingleItem(MenuItem $item, bool $hasChildren = false): ?NavigationItem
    {
        if ($item->type === 'separator') {
            return null;
        }

        $user = auth()->user();

        if ($item->roles && ! $user?->hasAnyRole($item->roles)) {
            return null;
        }

        if (filled($item->permissions)) {
            if (! $user?->hasAnyPermission($item->permissions)) {
                return null;
            }
        } elseif (config('filament-dynamic-menu.shield_integration', true)) {
            $shieldPermission = $this->resolveShieldPermission($item);

            if ($shieldPermission === 'denied') {
                return null;
            }

            if ($shieldPermission !== null && ! $user?->can($shieldPermission)) {
                return null;
            }
        }

        $url = $hasChildren ? null : $this->resolveUrl($item);

        $navItem = NavigationItem::make($item->label)
            ->icon($item->icon)
            ->sort($item->sort_order);

        if ($item->badge) {
            $navItem->badge($item->badge, $item->badge_color ?: null);
        }

        if ($url) {
            $navItem->url($url, shouldOpenInNewTab: $item->open_in_new_tab);
        }

        if ($item->type !== 'url' && $item->target && class_exists($item->target)) {
            if (method_exists($item->target, 'getNavigationItemActiveRoutePattern')) {
                $routePattern = $item->target::getNavigationItemActiveRoutePattern();
                $navItem->isActiveWhen(function () use ($routePattern): bool {
                    $request = request();

                    return $request->routeIs($routePattern);
                });
            }
        }

        return $navItem;
    }

    protected function resolveShieldPermission(MenuItem $item): ?string
    {
        if (! $item->target || ! class_exists($item->target)) {
            return null;
        }

        if ($item->type === 'resource' && is_subclass_of($item->target, Resource::class)) {
            try {
                $model = $item->target::getModel();
                $modelName = class_basename($model);

                return "ViewAny:{$modelName}";
            } catch (\Throwable) {
                return null;
            }
        }

        if ($item->type === 'page') {
            $basename = class_basename($item->target);

            return "View:{$basename}";
        }

        if ($item->type === 'cluster') {
            try {
                if (method_exists($item->target, 'canAccess') && ! $item->target::canAccess()) {
                    return 'denied';
                }
            } catch (\Throwable $e) {
                Log::warning('FilamentDynamicMenu: cluster canAccess check failed: ' . $e->getMessage(), [
                    'target' => $item->target,
                ]);
            }
        }

        return null;
    }

    protected function resolveUrl(MenuItem $item): ?string
    {
        if ($item->type === 'url' && $item->url) {
            return $item->url;
        }

        if (! $item->target || ! class_exists($item->target)) {
            return null;
        }

        try {
            if ($item->type === 'resource' && is_subclass_of($item->target, Resource::class)) {
                return $item->target::getUrl('index');
            }

            if (method_exists($item->target, 'getUrl')) {
                return $item->target::getUrl();
            }
        } catch (\Throwable $e) {
            Log::warning('FilamentDynamicMenu: URL resolution failed: ' . $e->getMessage(), [
                'item_id' => $item->id,
                'target' => $item->target,
            ]);
        }

        return null;
    }
}
