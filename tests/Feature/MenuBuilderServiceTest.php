<?php

namespace Susantokun\FilamentDynamicMenu\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Susantokun\FilamentDynamicMenu\Models\MenuGroup;
use Susantokun\FilamentDynamicMenu\Models\MenuItem;
use Susantokun\FilamentDynamicMenu\Models\MenuSidebar;
use Susantokun\FilamentDynamicMenu\Services\MenuBuilderService;
use Susantokun\FilamentDynamicMenu\Tests\TestCase;

class MenuBuilderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'packages-susantokun/filament-dynamic-menu/database/migrations',
            '--realpath' => true,
        ]);
    }

    public function test_service_is_enabled_checks_config(): void
    {
        Config::set('filament-dynamic-menu.enabled', true);
        $service = app(MenuBuilderService::class);

        $this->assertTrue($service->isEnabled());

        Config::set('filament-dynamic-menu.enabled', false);

        $this->assertFalse($service->isEnabled());
    }

    public function test_tenant_mode_returns_configured_value(): void
    {
        Config::set('filament-dynamic-menu.tenant_mode', 'single');
        $service = app(MenuBuilderService::class);

        $this->assertEquals('single', $service->tenantMode());

        Config::set('filament-dynamic-menu.tenant_mode', 'stancl');
        $service = app(MenuBuilderService::class);

        $this->assertEquals('stancl', $service->tenantMode());
    }

    public function test_resolve_tenant_id_returns_default_for_single_mode(): void
    {
        Config::set('filament-dynamic-menu.tenant_mode', 'single');
        $service = app(MenuBuilderService::class);

        $this->assertEquals('default', $service->resolveTenantId());
    }

    public function test_clear_cache_does_not_throw_exception(): void
    {
        $service = app(MenuBuilderService::class);

        Cache::put('filament_dynamic_menu_tenant_default_menu', 'test-data', 3600);
        $this->assertNotNull(Cache::get('filament_dynamic_menu_tenant_default_menu'));

        $service->clearCache('default');

        $this->assertTrue(true);
    }

    public function test_get_sidebar_creates_default_on_first_call(): void
    {
        $service = app(MenuBuilderService::class);

        $this->assertEquals(0, MenuSidebar::count());

        $sidebar = $service->getSidebar('default');

        $this->assertEquals(1, MenuSidebar::count());
        $this->assertTrue($sidebar->collapsible_navigation_groups);
        $this->assertTrue($sidebar->sidebar_collapsible_on_desktop);
        $this->assertTrue($sidebar->sidebar_fully_collapsible_on_desktop);
    }

    public function test_menu_group_and_item_relationship(): void
    {
        $group = MenuGroup::create([
            'name' => 'Test Group',
            'icon' => 'heroicon-o-home',
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        $item = MenuItem::create([
            'menu_group_id' => $group->id,
            'type' => 'resource',
            'label' => 'Test Item',
            'icon' => 'heroicon-o-user',
            'sort_order' => 1,
            'is_visible' => true,
            'is_active' => true,
        ]);

        $this->assertEquals(1, $group->items()->count());
        $this->assertEquals('Test Item', $group->items->first()->label);
        $this->assertEquals('Test Group', $item->menuGroup->name);
    }

    public function test_menu_item_parent_child_relationship(): void
    {
        $parent = MenuItem::create([
            'type' => 'page',
            'label' => 'Parent Item',
            'sort_order' => 1,
            'is_visible' => true,
            'is_active' => true,
        ]);

        $child = MenuItem::create([
            'parent_id' => $parent->id,
            'type' => 'page',
            'label' => 'Child Item',
            'sort_order' => 1,
            'is_visible' => true,
            'is_active' => true,
        ]);

        $this->assertEquals(1, $parent->children()->count());
        $this->assertEquals('Child Item', $parent->children->first()->label);
        $this->assertEquals($parent->id, $child->parent_id);
    }

    public function test_menu_group_role_casting(): void
    {
        $group = MenuGroup::create([
            'name' => 'Admin Group',
            'roles' => ['admin', 'super_admin'],
            'is_visible' => true,
        ]);

        $this->assertIsArray($group->roles);
        $this->assertEquals(['admin', 'super_admin'], $group->roles);
    }

    public function test_menu_item_permissions_casting(): void
    {
        $item = MenuItem::create([
            'type' => 'resource',
            'label' => 'Protected Item',
            'permissions' => ['ViewAny:User', 'Create:User'],
            'is_visible' => true,
            'is_active' => true,
        ]);

        $this->assertIsArray($item->permissions);
        $this->assertEquals(['ViewAny:User', 'Create:User'], $item->permissions);
    }

    public function test_menu_item_separator_type(): void
    {
        $item = MenuItem::create([
            'type' => 'separator',
            'label' => 'Separator',
            'is_visible' => true,
            'is_active' => true,
        ]);

        $this->assertEquals('separator', $item->type);
    }

    public function test_menu_item_url_type(): void
    {
        $item = MenuItem::create([
            'type' => 'url',
            'label' => 'Google',
            'url' => 'https://google.com',
            'open_in_new_tab' => true,
            'is_visible' => true,
            'is_active' => true,
        ]);

        $this->assertEquals('url', $item->type);
        $this->assertEquals('https://google.com', $item->url);
        $this->assertTrue($item->open_in_new_tab);
    }

    public function test_menu_item_badge_support(): void
    {
        $item = MenuItem::create([
            'type' => 'resource',
            'label' => 'Orders',
            'badge' => '5',
            'badge_color' => 'danger',
            'is_visible' => true,
            'is_active' => true,
        ]);

        $this->assertEquals('5', $item->badge);
        $this->assertEquals('danger', $item->badge_color);
    }
}
