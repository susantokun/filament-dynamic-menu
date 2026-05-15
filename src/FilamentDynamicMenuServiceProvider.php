<?php

namespace Susantokun\FilamentDynamicMenu;

use Filament\Facades\Filament;
use Filament\Panel;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Susantokun\FilamentDynamicMenu\Commands\ClearCacheCommand;
use Susantokun\FilamentDynamicMenu\Commands\ExportMenuCommand;
use Susantokun\FilamentDynamicMenu\Commands\ImportMenuCommand;
use Susantokun\FilamentDynamicMenu\Commands\InstallCommand;
use Susantokun\FilamentDynamicMenu\Filament\Clusters\MenuSettings\MenuSettingsCluster;
use Susantokun\FilamentDynamicMenu\Filament\Pages\MenuSidebar;
use Susantokun\FilamentDynamicMenu\Filament\Resources\MenuGroupResource;
use Susantokun\FilamentDynamicMenu\Filament\Resources\MenuItemResource;
use Susantokun\FilamentDynamicMenu\Services\MenuBuilderService;

class FilamentDynamicMenuServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-dynamic-menu')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews()
            ->hasMigrations([
                'create_menu_groups_table',
                'create_menu_items_table',
                'create_menu_sidebars_table',
            ])
            ->hasCommands([
                InstallCommand::class,
                ExportMenuCommand::class,
                ImportMenuCommand::class,
                ClearCacheCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('filament-dynamic-menu', function ($app) {
            return $app->make(MenuBuilderService::class);
        });
    }

    public function packageBooted(): void
    {
        $this->registerFilamentResources();
    }

    protected function registerFilamentResources(): void
    {
        if (! $this->app->runningInConsole() || $this->app->runningUnitTests()) {
            $this->registerWithFilament();
        }

        $this->callAfterResolving('filament', function () {
            $this->registerWithFilament();
        });
    }

    protected function registerWithFilament(): void
    {
        if (config('filament-dynamic-menu.registration_mode') === 'plugin') {
            return;
        }

        try {
            $panelId = config('filament-dynamic-menu.panel_id', 'admin');

            if (! Filament::hasPanel($panelId)) {
                return;
            }

            $panel = Filament::getPanel($panelId);

            $this->registerResources($panel);
            $this->registerCluster($panel);
            $this->registerPages($panel);
        } catch (\Throwable) {
            // Silently skip if not in Filament context
        }
    }

    protected function registerResources(Panel $panel): void
    {
        try {
            $panel
                ->resources([
                    MenuGroupResource::class,
                    MenuItemResource::class,
                ]);
        } catch (\Throwable) {
            //
        }
    }

    protected function registerCluster(Panel $panel): void
    {
        try {
            if (! collect($panel->getClusters())->contains(MenuSettingsCluster::class)) {
                $panel->discoverClusters(
                    in: dirname(__DIR__) . '/src/Filament/Clusters',
                    for: 'Susantokun\\FilamentDynamicMenu\\Filament\\Clusters'
                );
            }
        } catch (\Throwable) {
            //
        }
    }

    protected function registerPages(Panel $panel): void
    {
        try {
            if (! collect($panel->getPages())->contains(MenuSidebar::class)) {
                $panel->discoverPages(
                    in: dirname(__DIR__) . '/src/Filament/Pages',
                    for: 'Susantokun\\FilamentDynamicMenu\\Filament\\Pages'
                );
            }
        } catch (\Throwable) {
            //
        }
    }
}
