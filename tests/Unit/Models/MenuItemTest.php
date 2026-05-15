<?php

namespace Susantokun\FilamentDynamicMenu\Tests\Unit\Models;

use Susantokun\FilamentDynamicMenu\Models\MenuItem;
use Susantokun\FilamentDynamicMenu\Tests\TestCase;

class MenuItemTest extends TestCase
{
    public function test_menu_item_model_has_correct_table(): void
    {
        $item = new MenuItem;

        $this->assertEquals('menu_items', $item->getTable());
    }

    public function test_menu_item_casts_are_correct(): void
    {
        $item = new MenuItem;

        $casts = $item->getCasts();

        $this->assertEquals('boolean', $casts['is_visible']);
        $this->assertEquals('boolean', $casts['is_active']);
        $this->assertEquals('boolean', $casts['open_in_new_tab']);
        $this->assertEquals('array', $casts['roles']);
        $this->assertEquals('array', $casts['permissions']);
    }

    public function test_menu_item_has_relationships(): void
    {
        $item = new MenuItem;

        $this->assertTrue(method_exists($item, 'menuGroup'));
        $this->assertTrue(method_exists($item, 'parent'));
        $this->assertTrue(method_exists($item, 'children'));
    }

    public function test_menu_item_type_values(): void
    {
        $item = new MenuItem;

        $item->fill([
            'type' => 'resource',
            'label' => 'Users',
            'target' => 'App\Filament\Resources\UserResource',
            'sort_order' => 1,
            'is_visible' => true,
            'is_active' => true,
        ]);

        $this->assertEquals('resource', $item->type);
        $this->assertEquals('Users', $item->label);
        $this->assertEquals('App\Filament\Resources\UserResource', $item->target);
        $this->assertTrue($item->is_visible);
        $this->assertTrue($item->is_active);
    }

    public function test_menu_item_badge_fields(): void
    {
        $item = new MenuItem;

        $item->fill([
            'badge' => 'New',
            'badge_color' => 'success',
        ]);

        $this->assertEquals('New', $item->badge);
        $this->assertEquals('success', $item->badge_color);
    }
}