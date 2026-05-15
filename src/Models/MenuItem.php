<?php

namespace Susantokun\FilamentDynamicMenu\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Susantokun\FilamentDynamicMenu\Observers\MenuItemObserver;
use Susantokun\FilamentDynamicMenu\Traits\BelongsToOptionalTenant;

#[ObservedBy([MenuItemObserver::class])]
class MenuItem extends Model
{
    use BelongsToOptionalTenant;
    use SoftDeletes;

    protected $table = 'menu_items';

    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'is_active' => 'boolean',
            'open_in_new_tab' => 'boolean',
            'roles' => 'array',
            'permissions' => 'array',
        ];
    }

    public function menuGroup(): BelongsTo
    {
        return $this->belongsTo(MenuGroup::class, 'menu_group_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id');
    }

    public function tenant(): ?BelongsTo
    {
        return $this->belongsToOptionalTenant();
    }
}
