<?php

namespace Susantokun\FilamentDynamicMenu\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Stancl\Tenancy\Database\Models\Tenant;

trait BelongsToOptionalTenant
{
    public static function bootBelongsToOptionalTenant(): void
    {
        $mode = config('filament-dynamic-menu.tenant_mode');

        if ($mode === 'stancl' && trait_exists(BelongsToTenant::class)) {
            static::addGlobalScope('tenant', function ($query) {
                if ($tenant = tenant()) {
                    $query->where($query->getModel()->getTable().'.tenant_id', $tenant->id);
                }
            });
        }

        if ($mode === 'stancl') {
            static::creating(function ($model) {
                if (! $model->tenant_id && $tenant = tenant()) {
                    $model->tenant_id = $tenant->id;
                }
            });
        }

        if ($mode === 'custom') {
            static::creating(function ($model) {
                if (! $model->tenant_id) {
                    $model->tenant_id = $model->resolveCustomTenantId();
                }
            });
        }
    }

    public function belongsToOptionalTenant(): ?BelongsTo
    {
        $mode = config('filament-dynamic-menu.tenant_mode');

        if ($mode === 'single') {
            return null;
        }

        if ($mode === 'stancl' && class_exists(Tenant::class)) {
            return $this->belongsTo(Tenant::class, 'tenant_id');
        }

        $model = config('filament-dynamic-menu.tenant_model');

        if ($mode === 'custom' && $model && class_exists($model)) {
            return $this->belongsTo($model, 'tenant_id');
        }

        return null;
    }

    protected function resolveCustomTenantId(): ?string
    {
        $model = config('filament-dynamic-menu.tenant_model');

        if (! $model || ! class_exists($model)) {
            return null;
        }

        if (method_exists($model, 'current')) {
            return (string) $model::current()?->id;
        }

        if (function_exists('tenant')) {
            $tenant = tenant();

            return $tenant ? (string) $tenant->id : null;
        }

        return null;
    }
}
