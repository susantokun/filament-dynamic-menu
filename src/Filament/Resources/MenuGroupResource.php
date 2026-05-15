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
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Susantokun\FilamentDynamicMenu\Filament\Clusters\MenuSettings\MenuSettingsCluster;
use Susantokun\FilamentDynamicMenu\Models\MenuGroup;

class MenuGroupResource extends Resource
{
    protected static ?string $model = MenuGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $cluster = MenuSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'groups';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(100),
            TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->required(),
            Group::make([
                Toggle::make('is_collapsible')
                    ->default(true),
                Toggle::make('is_collapsed')
                    ->default(false),
                Toggle::make('is_visible')
                    ->default(true),
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
                ->columnSpanFull()
                ->multiple()
                ->native(false)
                ->helperText(__('filament-dynamic-menu::filament-dynamic-menu.help.roles_group')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                IconColumn::make('is_visible')
                    ->boolean()
                    ->alignCenter(),
                IconColumn::make('is_collapsible')
                    ->boolean()
                    ->alignCenter(),
                IconColumn::make('is_collapsed')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('items_count')
                    ->label(__('filament-dynamic-menu::filament-dynamic-menu.table.items'))
                    ->counts('items')
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
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
            'index' => Pages\ManageMenuGroups::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
