<?php

namespace Susantokun\FilamentDynamicMenu\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Susantokun\FilamentDynamicMenu\Filament\Clusters\MenuSettings\MenuSettingsCluster;
use Susantokun\FilamentDynamicMenu\Models\MenuSidebar as MenuSidebarModel;
use Susantokun\FilamentDynamicMenu\Services\MenuBuilderService;

class MenuSidebar extends Page
{
    protected static ?string $cluster = MenuSettingsCluster::class;

    protected static ?string $slug = 'sidebar';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament-dynamic-menu::pages.menu-sidebar';

    protected Width|string|null $maxContentWidth = Width::Full;

    public ?array $data = [];

    public function mount(): void
    {
        $service = app(MenuBuilderService::class);
        $tenantId = $service->resolveTenantId() ?? 'default';

        $attributes = config('filament-dynamic-menu.tenant_mode') === 'single'
            ? []
            : ['tenant_id' => $tenantId];

        $sidebar = MenuSidebarModel::firstOrCreate(
            $attributes,
            array_merge($attributes, [
                'collapsible_navigation_groups' => false,
                'sidebar_collapsible_on_desktop' => true,
                'sidebar_fully_collapsible_on_desktop' => true,
            ])
        );

        $this->data = $sidebar->toArray();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Toggle::make('collapsible_navigation_groups')
                    ->label(__('filament-dynamic-menu::filament-dynamic-menu.menu_sidebar.collapsible_navigation_groups'))
                    ->helperText(__('filament-dynamic-menu::filament-dynamic-menu.menu_sidebar.collapsible_navigation_groups_help')),
                Toggle::make('sidebar_collapsible_on_desktop')
                    ->label(__('filament-dynamic-menu::filament-dynamic-menu.menu_sidebar.sidebar_collapsible_on_desktop'))
                    ->helperText(__('filament-dynamic-menu::filament-dynamic-menu.menu_sidebar.sidebar_collapsible_on_desktop_help')),
                Toggle::make('sidebar_fully_collapsible_on_desktop')
                    ->label(__('filament-dynamic-menu::filament-dynamic-menu.menu_sidebar.sidebar_fully_collapsible_on_desktop'))
                    ->helperText(__('filament-dynamic-menu::filament-dynamic-menu.menu_sidebar.sidebar_fully_collapsible_on_desktop_help')),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-dynamic-menu::filament-dynamic-menu.actions.save'))
                ->action(fn() => $this->save()),
        ];
    }

    public function save(): void
    {
        $service = app(MenuBuilderService::class);
        $tenantId = $service->resolveTenantId() ?? 'default';

        $attributes = config('filament-dynamic-menu.tenant_mode') === 'single'
            ? []
            : ['tenant_id' => $tenantId];

        MenuSidebarModel::updateOrCreate(
            $attributes,
            array_merge($attributes, $this->data)
        );

        Notification::make()
            ->title(__('filament-dynamic-menu::filament-dynamic-menu.messages.saved'))
            ->success()
            ->send();
    }

    public function getTitle(): string|Htmlable
    {
        return __('filament-dynamic-menu::filament-dynamic-menu.menu_sidebar.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('filament-dynamic-menu::filament-dynamic-menu.menu_sidebar.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-dynamic-menu::filament-dynamic-menu.menu_sidebar.title');
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-bars-3-bottom-left';
    }
}
