<?php

namespace Susantokun\FilamentDynamicMenu\Facades;

use Illuminate\Support\Facades\Facade;
use Susantokun\FilamentDynamicMenu\Services\MenuBuilderService;

/**
 * @method static bool isEnabled()
 * @method static string tenantMode()
 * @method static string|null resolveTenantId()
 * @method static string|null getTenantId()
 * @method static void clearCache(string|null $tenantId = null)
 * @method static \Susantokun\FilamentDynamicMenu\Models\MenuSetting getSettings(int|string $tenantId)
 *
 * @see MenuBuilderService
 */
class DynamicMenu extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'filament-dynamic-menu';
    }
}
