<?php

namespace Susantokun\FilamentDynamicMenu\Tests\Unit\Models;

use Susantokun\FilamentDynamicMenu\Models\MenuSidebar;
use Susantokun\FilamentDynamicMenu\Tests\TestCase;

class MenuSidebarTest extends TestCase
{
    public function test_menu_sidebar_model_has_correct_table(): void
    {
        $sidebar = new MenuSidebar;

        $this->assertEquals('menu_sidebars', $sidebar->getTable());
    }

    public function test_menu_sidebar_casts_are_correct(): void
    {
        $sidebar = new MenuSidebar;

        $casts = $sidebar->getCasts();

        $this->assertEquals('boolean', $casts['collapsible_navigation_groups']);
        $this->assertEquals('boolean', $casts['sidebar_collapsible_on_desktop']);
        $this->assertEquals('boolean', $casts['sidebar_fully_collapsible_on_desktop']);
    }

    public function test_menu_sidebar_default_values(): void
    {
        $sidebar = new MenuSidebar;

        $sidebar->fill([
            'collapsible_navigation_groups' => true,
            'sidebar_collapsible_on_desktop' => true,
            'sidebar_fully_collapsible_on_desktop' => false,
        ]);

        $this->assertTrue($sidebar->collapsible_navigation_groups);
        $this->assertTrue($sidebar->sidebar_collapsible_on_desktop);
        $this->assertFalse($sidebar->sidebar_fully_collapsible_on_desktop);
    }
}
