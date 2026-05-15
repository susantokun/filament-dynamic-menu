<?php

namespace Susantokun\FilamentDynamicMenu\Tests\Unit\Models;

use Susantokun\FilamentDynamicMenu\Models\MenuGroup;
use Susantokun\FilamentDynamicMenu\Tests\TestCase;

class MenuGroupTest extends TestCase
{
    public function test_menu_group_model_has_correct_table(): void
    {
        $group = new MenuGroup;

        $this->assertEquals('menu_groups', $group->getTable());
    }

    public function test_menu_group_casts_are_correct(): void
    {
        $group = new MenuGroup;

        $casts = $group->getCasts();

        $this->assertEquals('boolean', $casts['is_collapsible']);
        $this->assertEquals('boolean', $casts['is_collapsed']);
        $this->assertEquals('boolean', $casts['is_visible']);
        $this->assertEquals('array', $casts['roles']);
    }

    public function test_menu_group_has_items_relationship(): void
    {
        $group = new MenuGroup;

        $this->assertTrue(method_exists($group, 'items'));
    }

    public function test_menu_group_fillable_attributes(): void
    {
        $group = new MenuGroup;

        $group->fill([
            'name' => 'Test Group',
            'icon' => 'heroicon-o-home',
            'sort_order' => 1,
            'is_collapsible' => true,
            'is_collapsed' => false,
            'is_visible' => true,
            'roles' => ['admin'],
        ]);

        $this->assertEquals('Test Group', $group->name);
        $this->assertEquals('heroicon-o-home', $group->icon);
        $this->assertEquals(1, $group->sort_order);
        $this->assertTrue($group->is_collapsible);
        $this->assertFalse($group->is_collapsed);
        $this->assertTrue($group->is_visible);
        $this->assertEquals(['admin'], $group->roles);
    }
}