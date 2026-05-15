<?php

namespace Susantokun\FilamentDynamicMenu\Traits;

use Filament\Navigation\NavigationBuilder;
use Filament\Panel;
use Illuminate\Support\Facades\App;
use Susantokun\FilamentDynamicMenu\Services\MenuBuilderService;

trait HasDynamicMenu
{
    public function dynamicMenu(Panel $panel): Panel
    {
        if (! config('filament-dynamic-menu.enabled')) {
            return $panel;
        }

        return $panel->navigation(function (NavigationBuilder $builder): NavigationBuilder {
            try {
                return App::make(MenuBuilderService::class)->build($builder);
            } catch (\Throwable) {
                return $builder;
            }
        });
    }

    public function dynamicMenuSidebar(Panel $panel): Panel
    {
        if (! config('filament-dynamic-menu.enabled')) {
            return $panel;
        }

        return $panel
            ->collapsibleNavigationGroups(
                fn(): bool => $this->getDynamicMenuSidebar('collapsible_navigation_groups')
            )
            ->sidebarCollapsibleOnDesktop(
                fn(): bool => $this->getDynamicMenuSidebar('sidebar_collapsible_on_desktop')
            )
            ->sidebarFullyCollapsibleOnDesktop(
                fn(): bool => $this->getDynamicMenuSidebar('sidebar_fully_collapsible_on_desktop')
            );
    }

    protected function getDynamicMenuSidebar(string $key): bool
    {
        try {
            $service = App::make(MenuBuilderService::class);
            $tenantId = $service->resolveTenantId();

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
