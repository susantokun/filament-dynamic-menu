<?php

namespace Susantokun\FilamentDynamicMenu\Filament\Resources;

use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Facades\Filament;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Susantokun\FilamentDynamicMenu\Enums\MenuIconEnum;
use Susantokun\FilamentDynamicMenu\Filament\Clusters\MenuSettings\MenuSettingsCluster;
use Susantokun\FilamentDynamicMenu\Models\MenuItem;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static string|BackedEnum|null $navigationIcon = HeroIcon::OutlinedBars3;

    protected static ?string $cluster = MenuSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $slug = 'items';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')
                ->options([
                    'resource' => 'Resource',
                    'page' => 'Page',
                    'cluster' => 'Cluster',
                    'url' => 'Custom URL',
                    'separator' => 'Separator',
                ])
                ->default('resource')
                ->required()
                ->live()
                ->native(false),

            Select::make('target')
                ->label(__('filament-dynamic-menu::filament-dynamic-menu.fields.target'))
                ->options(fn() => self::getDiscoverableTargets())
                ->searchable()
                ->native(false)
                ->visible(fn(Get $get): bool => in_array($get('type'), ['resource', 'page', 'cluster'])),

            TextInput::make('url')
                ->label(__('filament-dynamic-menu::filament-dynamic-menu.fields.url'))
                ->url()
                ->visible(fn(Get $get): bool => $get('type') === 'url'),

            TextInput::make('label')
                ->required()
                ->maxLength(100),

            Select::make('icon')
                ->options(MenuIconEnum::htmlOptions())
                ->searchable()
                ->native(false)
                ->allowHtml(),

            Select::make('menu_group_id')
                ->label(__('filament-dynamic-menu::filament-dynamic-menu.fields.menu_group'))
                ->relationship('menuGroup', 'name')
                ->createOptionForm([
                    TextInput::make('name')->required()->maxLength(100),
                    TextInput::make('sort_order')->numeric()->default(0),
                    Toggle::make('is_visible')->default(true),
                ])
                ->native(false),

            Select::make('parent_id')
                ->label(__('filament-dynamic-menu::filament-dynamic-menu.fields.parent_item'))
                ->relationship(
                    name: 'parent',
                    titleAttribute: 'label',
                    modifyQueryUsing: fn(Builder $query, $record) => $query
                        ->when(
                            $record,
                            fn(Builder $q) => $q->whereNot('menu_items.id', $record->id),
                        ),
                )
                ->searchable()
                ->preload()
                ->native(false),

            TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->required(),

            Group::make([
                Toggle::make('is_visible')
                    ->default(true),

                Toggle::make('is_active')
                    ->default(true),

                Toggle::make('open_in_new_tab')
                    ->default(false),
            ])->columns(3)->columnSpanFull(),

            Select::make('roles')
                ->label(__('filament-dynamic-menu::filament-dynamic-menu.fields.visible_for_roles'))
                ->options(function () {
                    $roleModelClass = config('filament-dynamic-menu.role_model', null);

                    if (! $roleModelClass || ! class_exists($roleModelClass)) {
                        return [];
                    }

                    $tenant = Filament::getTenant();

                    if ($tenant) {
                        $query = $roleModelClass::where('tenant_id', $tenant->id);
                    } else {
                        $query = $roleModelClass::query();
                    }

                    if (! auth()->user()?->hasRole(config('filament-dynamic-menu.super_admin_role', 'super_admin'))) {
                        $query->whereNot('name', config('filament-dynamic-menu.super_admin_role', 'super_admin'));
                    }

                    return $query->pluck('name', 'name');
                })
                ->multiple()
                ->native(false)
                ->helperText(__('filament-dynamic-menu::filament-dynamic-menu.help.roles_item')),

            Select::make('permissions')
                ->label(__('filament-dynamic-menu::filament-dynamic-menu.fields.required_permissions'))
                ->options(function () {
                    $permissionModelClass = config('filament-dynamic-menu.permission_model', null);

                    if (! $permissionModelClass || ! class_exists($permissionModelClass)) {
                        return [];
                    }

                    return $permissionModelClass::query()->pluck('name', 'name');
                })
                ->multiple()
                ->searchable()
                ->native(false)
                ->helperText(__('filament-dynamic-menu::filament-dynamic-menu.help.permissions')),

            TextInput::make('badge')
                ->maxLength(100),

            Select::make('badge_color')
                ->options([
                    'danger' => 'Danger',
                    'gray' => 'Gray',
                    'info' => 'Info',
                    'primary' => 'Primary',
                    'success' => 'Success',
                    'warning' => 'Warning',
                ])
                ->native(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                TextColumn::make('label')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('menuGroup.name')
                    ->label(__('filament-dynamic-menu::filament-dynamic-menu.table.group'))
                    ->sortable(),
                IconColumn::make('icon')
                    ->icon(fn($state) => $state ?: null)
                    ->alignCenter(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                IconColumn::make('is_visible')
                    ->boolean()
                    ->alignCenter(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->slideOver(),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMenuItems::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    protected static function getDiscoverableTargets(): array
    {
        $targets = [];
        $panelId = config('filament-dynamic-menu.panel_id', 'admin');

        try {
            $panel = Filament::getPanel($panelId);

            foreach ($panel->getResources() as $resource) {
                $targets[$resource] = class_basename($resource);
            }

            foreach ($panel->getPages() as $page) {
                $targets[$page] = class_basename($page);
            }

            foreach ($panel->getClusters() as $cluster) {
                $targets[$cluster] = class_basename($cluster);
            }
        } catch (\Throwable) {
            //
        }

        return $targets;
    }
}
