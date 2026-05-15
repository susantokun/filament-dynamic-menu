<?php

namespace Susantokun\FilamentDynamicMenu\Tests\Unit\Enums;

use Susantokun\FilamentDynamicMenu\Enums\MenuIconEnum;
use Susantokun\FilamentDynamicMenu\Tests\TestCase;

class MenuIconEnumTest extends TestCase
{
    public function test_enum_has_cases(): void
    {
        $cases = MenuIconEnum::cases();

        $this->assertNotEmpty($cases);
        $this->assertGreaterThan(200, count($cases));
    }

    public function test_each_case_implements_has_label(): void
    {
        foreach (MenuIconEnum::cases() as $case) {
            $this->assertIsString($case->getLabel());
            $this->assertNotEmpty($case->getLabel());
        }
    }

    public function test_options_returns_array(): void
    {
        $options = MenuIconEnum::options();

        $this->assertIsArray($options);
    }

    public function test_html_options_returns_array(): void
    {
        $options = MenuIconEnum::htmlOptions();

        $this->assertIsArray($options);
    }

    public function test_home_icon_has_correct_value(): void
    {
        $this->assertEquals('heroicon-o-home', MenuIconEnum::HOME->value);
        $this->assertEquals('Home', MenuIconEnum::HOME->getLabel());
    }

    public function test_cog_icon_has_correct_value(): void
    {
        $this->assertEquals('heroicon-o-cog-6-tooth', MenuIconEnum::COG_6_TOOTH->value);
    }

    public function test_user_icons_exist(): void
    {
        $this->assertNotNull(MenuIconEnum::tryFrom('heroicon-o-user'));
        $this->assertNotNull(MenuIconEnum::tryFrom('heroicon-o-users'));
        $this->assertNotNull(MenuIconEnum::tryFrom('heroicon-o-user-group'));
    }
}