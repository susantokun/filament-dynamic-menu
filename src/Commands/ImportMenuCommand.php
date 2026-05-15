<?php

namespace Susantokun\FilamentDynamicMenu\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Susantokun\FilamentDynamicMenu\Models\MenuGroup;
use Susantokun\FilamentDynamicMenu\Models\MenuItem;
use Susantokun\FilamentDynamicMenu\Models\MenuSetting;

class ImportMenuCommand extends Command
{
    protected $signature = 'filament-dynamic-menu:import
                           {file : Path to the JSON export file}
                           {tenantId? : The target tenant ID}
                           {--force : Force import without confirmation}';

    protected $description = 'Import menus from a JSON export file into a tenant';

    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return self::FAILURE;
        }

        $json = file_get_contents($filePath);
        $data = json_decode($json, true);

        if (! $data || ! isset($data['export_info'])) {
            $this->error('Invalid export file format.');

            return self::FAILURE;
        }

        $tenantId = $this->argument('tenantId') ?? 'default';
        $singleMode = config('filament-dynamic-menu.tenant_mode') === 'single';

        if ($singleMode) {
            $tenantId = 'default';
        }

        $groupsCount = count($data['groups'] ?? []);
        $itemsCount = count($data['items'] ?? []);

        if (! $this->option('force')) {
            $this->warn("This will import {$groupsCount} groups and {$itemsCount} items for tenant '{$tenantId}'.");
            if (! $this->confirm('Do you want to continue?')) {
                $this->info('Import cancelled.');

                return self::SUCCESS;
            }
        }

        $this->performImport($data, $tenantId, $singleMode);

        return self::SUCCESS;
    }

    protected function performImport(array $data, string $tenantId, bool $singleMode): void
    {
        DB::transaction(function () use ($data, $tenantId, $singleMode) {
            $idMap = [];

            if (! empty($data['settings'])) {
                $settingsData = $this->stripTimestamps($data['settings']);
                if (! $singleMode) {
                    $settingsData['tenant_id'] = $tenantId;
                }
                MenuSetting::updateOrCreate(
                    $singleMode ? [] : ['tenant_id' => $tenantId],
                    $settingsData
                );
            }

            foreach ($data['groups'] ?? [] as $groupData) {
                $stripped = $this->stripTimestamps($groupData);
                if (! $singleMode) {
                    $stripped['tenant_id'] = $tenantId;
                }

                $group = MenuGroup::create($stripped);
                $idMap[$groupData['id']] = $group->id;
            }

            foreach ($data['items'] ?? [] as $itemData) {
                $stripped = $this->stripTimestamps($itemData);
                if (! $singleMode) {
                    $stripped['tenant_id'] = $tenantId;
                }

                if (isset($stripped['menu_group_id']) && isset($idMap[$stripped['menu_group_id']])) {
                    $stripped['menu_group_id'] = $idMap[$stripped['menu_group_id']];
                }

                MenuItem::create($stripped);
            }

            $this->call('filament-dynamic-menu:clear-cache', ['tenantId' => $tenantId]);
        });

        $this->info("Menu imported successfully for tenant '{$tenantId}'.");
    }

    protected function stripTimestamps(array $data): array
    {
        return array_diff_key($data, array_flip([
            'id', 'created_at', 'updated_at', 'deleted_at',
        ]));
    }
}
