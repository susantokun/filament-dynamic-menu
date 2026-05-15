<?php

namespace Susantokun\FilamentDynamicMenu\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Susantokun\FilamentDynamicMenu\Observers\MenuGroupObserver;
use Susantokun\FilamentDynamicMenu\Traits\BelongsToOptionalTenant;

#[ObservedBy([MenuGroupObserver::class])]
class MenuGroup extends Model
{
    use BelongsToOptionalTenant;
    use SoftDeletes;

    protected $table = 'menu_groups';

    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_collapsible' => 'boolean',
            'is_collapsed' => 'boolean',
            'is_visible' => 'boolean',
            'roles' => 'array',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_group_id');
    }

    public function tenant(): ?BelongsTo
    {
        return $this->belongsToOptionalTenant();
    }
}
