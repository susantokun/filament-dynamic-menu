<?php

namespace Susantokun\FilamentDynamicMenu\Database\Seeders;

use Illuminate\Database\Seeder;
use Susantokun\FilamentDynamicMenu\Filament\Clusters\MenuSettings\MenuSettingsCluster;
use Susantokun\FilamentDynamicMenu\Models\MenuGroup;
use Susantokun\FilamentDynamicMenu\Models\MenuItem;
use Susantokun\FilamentDynamicMenu\Models\MenuSidebar;

class DefaultMenuSeeder extends Seeder
{
    public int $tenantId = 1;

    public function run(): void
    {
        $tenantId = $this->tenantId;
        $singleMode = config('filament-dynamic-menu.tenant_mode') === 'single';

        MenuSidebar::updateOrCreate(
            $singleMode ? [] : ['tenant_id' => $tenantId],
            array_merge(
                $singleMode ? [] : ['tenant_id' => $tenantId],
                [
                    'collapsible_navigation_groups' => false,
                    'sidebar_collapsible_on_desktop' => true,
                    'sidebar_fully_collapsible_on_desktop' => true,
                ]
            )
        );

        MenuItem::create(array_merge(
            $singleMode ? [] : ['tenant_id' => $tenantId],
            [
                'menu_group_id' => null,
                'type' => 'page',
                'label' => 'Dashboard',
                'icon' => 'heroicon-o-home',
                'target' => 'App\Filament\Pages\Dashboard',
                'sort_order' => 1,
                'is_visible' => true,
                'is_active' => true,
            ]
        ));

        $settingsGroup = MenuGroup::create(array_merge(
            $singleMode ? [] : ['tenant_id' => $tenantId],
            [
                'name' => 'Settings',
                'icon' => 'heroicon-o-cog-6-tooth',
                'sort_order' => 99,
                'is_collapsible' => true,
                'is_visible' => true,
            ]
        ));

        MenuItem::create(array_merge(
            $singleMode ? [] : ['tenant_id' => $tenantId],
            [
                'menu_group_id' => $settingsGroup->id,
                'type' => 'cluster',
                'label' => 'Menu Settings',
                'icon' => 'heroicon-o-list-bullet',
                'target' => MenuSettingsCluster::class,
                'sort_order' => 1,
                'is_visible' => true,
                'is_active' => true,
            ]
        ));
    }
}
