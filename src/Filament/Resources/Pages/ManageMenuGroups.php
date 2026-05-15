<?php

namespace Susantokun\FilamentDynamicMenu\Filament\Resources\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;
use Susantokun\FilamentDynamicMenu\Filament\Resources\MenuGroupResource;

class ManageMenuGroups extends ManageRecords
{
    protected static string $resource = MenuGroupResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver(),
        ];
    }
}
