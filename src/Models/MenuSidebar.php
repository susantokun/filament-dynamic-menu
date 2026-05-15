<?php

namespace Susantokun\FilamentDynamicMenu\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Susantokun\FilamentDynamicMenu\Observers\MenuSidebarObserver;
use Susantokun\FilamentDynamicMenu\Traits\BelongsToOptionalTenant;

#[ObservedBy([MenuSidebarObserver::class])]
class MenuSidebar extends Model
{
    use BelongsToOptionalTenant;
    use SoftDeletes;

    protected $table = 'menu_sidebars';

    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'collapsible_navigation_groups' => 'boolean',
            'sidebar_collapsible_on_desktop' => 'boolean',
            'sidebar_fully_collapsible_on_desktop' => 'boolean',
        ];
    }

    public function tenant(): ?BelongsTo
    {
        return $this->belongsToOptionalTenant();
    }
}
