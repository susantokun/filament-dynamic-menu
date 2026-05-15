<?php

namespace Susantokun\FilamentDynamicMenu\Filament\Clusters\MenuSettings;

use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Illuminate\Contracts\Support\Htmlable;

class MenuSettingsCluster extends Cluster
{
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('filament-dynamic-menu::filament-dynamic-menu.cluster.menu_settings.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-dynamic-menu::filament-dynamic-menu.cluster.menu_settings.navigation_label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('filament-dynamic-menu::filament-dynamic-menu.cluster.menu_settings.title');
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-list-bullet';
    }

    public static function canAccess(): bool
    {
        return auth()->user()->can('MenuSettings');
    }
}
