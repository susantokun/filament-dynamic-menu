<?php

namespace Susantokun\FilamentDynamicMenu\Tests;

use Illuminate\Support\Facades\Config;
use Tests\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('filament-dynamic-menu.tenant_mode', 'single');
        Config::set('filament-dynamic-menu.shield_integration', false);
        Config::set('filament-dynamic-menu.auto_seed_on_empty', false);
    }
}