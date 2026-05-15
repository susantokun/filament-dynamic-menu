<?php

namespace Susantokun\FilamentDynamicMenu\Commands;

use Illuminate\Console\Command;
use Susantokun\FilamentDynamicMenu\Models\MenuGroup;
use Susantokun\FilamentDynamicMenu\Models\MenuItem;
use Susantokun\FilamentDynamicMenu\Models\MenuSetting;

class ExportMenuCommand extends Command
{
    protected $signature = 'filament-dynamic-menu:export
                           {tenantId? : The tenant ID to export menus for}
                           {--path= : Output file path (defaults to storage/app/)}';

    protected $description = 'Export menus for a tenant to a JSON file';

    public function handle(): int
    {
        $tenantId = $this->argument('tenantId') ?? 'default';
        $singleMode = config('filament-dynamic-menu.tenant_mode') === 'single';

        if ($singleMode) {
            $tenantId = 'default';
        }

        $data = $this->gatherData($tenantId, $singleMode);

        $filename = "menu-export-{$tenantId}-".now()->format('Y-m-d-His').'.json';
        $path = $this->option('path') ?: storage_path('app');
        $filepath = rtrim($path, '/').'/'.$filename;

        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("Menu data exported to: {$filepath}");
        $this->line("  Groups: {$data['export_info']['groups_count']}");
        $this->line("  Items: {$data['export_info']['items_count']}");

        return self::SUCCESS;
    }

    protected function gatherData(string $tenantId, bool $singleMode): array
    {
        $groupsQuery = MenuGroup::query();
        $itemsQuery = MenuItem::query();
        $settingsQuery = MenuSetting::query();

        if (! $singleMode) {
            $groupsQuery->where('tenant_id', $tenantId);
            $itemsQuery->where('tenant_id', $tenantId);
            $settingsQuery->where('tenant_id', $tenantId);
        }

        $groups = $groupsQuery->get()->toArray();
        $items = $itemsQuery->get()->toArray();
        $settings = $settingsQuery->first()?->toArray();

        return [
            'export_info' => [
                'version' => '1.0',
                'exported_at' => now()->toIso8601String(),
                'tenant_id' => $tenantId,
                'tenant_mode' => config('filament-dynamic-menu.tenant_mode'),
                'groups_count' => count($groups),
                'items_count' => count($items),
            ],
            'settings' => $settings,
            'groups' => $groups,
            'items' => $items,
        ];
    }
}
