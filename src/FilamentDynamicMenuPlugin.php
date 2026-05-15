<?php

namespace Susantokun\FilamentDynamicMenu;

use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationBuilder;
use Filament\Panel;
use Susantokun\FilamentDynamicMenu\Filament\Pages\MenuSidebar;
use Susantokun\FilamentDynamicMenu\Filament\Resources\MenuGroupResource;
use Susantokun\FilamentDynamicMenu\Filament\Resources\MenuItemResource;
use Susantokun\FilamentDynamicMenu\Services\MenuBuilderService;

class FilamentDynamicMenuPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-dynamic-menu';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                MenuGroupResource::class,
                MenuItemResource::class,
            ])
            ->pages([
                MenuSidebar::class,
            ]);

        $panel->discoverClusters(
            in: __DIR__ . '/Filament/Clusters',
            for: 'Susantokun\\FilamentDynamicMenu\\Filament\\Clusters'
        );
    }

    public function boot(Panel $panel): void
    {
        if (! config('filament-dynamic-menu.enabled')) {
            return;
        }

        $service = app(MenuBuilderService::class);

        $panel->navigation(function (NavigationBuilder $builder) use ($service): NavigationBuilder {
            try {
                return $service->build($builder);
            } catch (\Throwable) {
                return $builder;
            }
        });

        $panel
            ->collapsibleNavigationGroups(
                fn(): bool => $this->resolveSidebar('collapsible_navigation_groups')
            )
            ->sidebarCollapsibleOnDesktop(
                fn(): bool => $this->resolveSidebar('sidebar_collapsible_on_desktop')
            );

        if (method_exists($panel, 'sidebarFullyCollapsibleOnDesktop')) {
            $panel->sidebarFullyCollapsibleOnDesktop(
                fn(): bool => $this->resolveSidebar('sidebar_fully_collapsible_on_desktop')
            );
        }
    }

    protected function resolveSidebar(string $key): bool
    {
        try {
            $service = app(MenuBuilderService::class);
            $tenantId = $service->getTenantId();

            if (! $tenantId) {
                return true;
            }

            $sidebar = $service->getSidebar($tenantId);

            return (bool) ($sidebar->{$key} ?? true);
        } catch (\Throwable) {
            return true;
        }
    }
}
