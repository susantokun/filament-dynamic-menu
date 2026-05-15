<?php

use Susantokun\FilamentDynamicMenu\Database\Seeders\DefaultMenuSeeder;

return [

    /*
    |--------------------------------------------------------------------------
    | Dynamic Menu Enabled
    |--------------------------------------------------------------------------
    |
    | Master switch untuk dynamic menu. Set false untuk menggunakan
    | auto-discovery navigasi bawaan Filament.
    |
    */
    'enabled' => env('FILAMENT_DYNAMIC_MENU_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Tenant Mode
    |--------------------------------------------------------------------------
    |
    | Supported modes:
    |   'single'  - No tenant, menus are global
    |   'stancl'  - Uses stancl/tenancy for multi-tenancy.
    |   'custom'  - Uses your own tenant implementation.
    |
    */
    'tenant_mode' => 'single',

    /*
    |--------------------------------------------------------------------------
    | Tenant Model (for 'custom' mode)
    |--------------------------------------------------------------------------
    |
    | When tenant_mode is 'custom', provide the FQCN of your Tenant model.
    | The model must have an 'id' property.
    |
    */
    'tenant_model' => null,

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'ttl' => 86400, // 24 hours
        'prefix' => 'filament_dynamic_menu',
    ],

    /*
    |--------------------------------------------------------------------------
    | Shield Integration
    |--------------------------------------------------------------------------
    |
    | When enabled, MenuBuilderService will auto-resolve Shield permission
    | names for Resource/Page/Cluster types. Requires bezhansalleh/filament-shield.
    |
    */
    'shield_integration' => true,

    /*
    |--------------------------------------------------------------------------
    | Auto Seed On Empty
    |--------------------------------------------------------------------------
    |
    | When enabled, if no menu data exists for a tenant, the DefaultMenuSeeder
    | is automatically run to populate initial menu items.
    |
    */
    'auto_seed_on_empty' => true,

    /*
    |--------------------------------------------------------------------------
    | Super Admin Role Name
    |--------------------------------------------------------------------------
    |
    | Used to exclude super admin from role filters in the Filament forms.
    |
    */
    'super_admin_role' => 'super_admin',

    /*
    |--------------------------------------------------------------------------
    | Default Seeder Class
    |--------------------------------------------------------------------------
    |
    | The FQCN of the seeder class to auto-run when no menu data exists.
    | Set to null to disable auto-seeding.
    |
    */
    'default_seeder' => DefaultMenuSeeder::class,

    /*
    |--------------------------------------------------------------------------
    | Role & Permission Models
    |--------------------------------------------------------------------------
    |
    | FQCN of your Role and Permission models. These are used in the Filament
    | forms for role/permission selection dropdowns. Set to null to disable.
    |
    */
    'role_model' => null,
    'permission_model' => null,

    /*
    |--------------------------------------------------------------------------
    | Registration Mode
    |--------------------------------------------------------------------------
    |
    | Controls how the package registers its Filament resources/pages/clusters.
    |
    |   'plugin'  (Recommended) Use FilamentDynamicMenuPlugin::make() in your
    |             PanelProvider's ->plugins() array. The simplest setup with
    |             just one line — all resources, pages, and navigation are
    |             handled automatically.
    |
    |   'auto'    (Legacy) The service provider auto-registers resources to
    |             the panel specified by panel_id. Use HasDynamicMenu trait
    |             in your PanelProvider for navigation control.
    |
    */
    'registration_mode' => 'plugin',

    /*
    |--------------------------------------------------------------------------
    | Route / Panel
    |--------------------------------------------------------------------------
    |
    | The Filament panel ID where menu management pages are registered.
    | Only used when registration_mode is 'auto'.
    |
    */
    'panel_id' => 'admin'
];
