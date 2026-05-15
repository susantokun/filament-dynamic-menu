<?php

namespace Susantokun\FilamentDynamicMenu\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'filament-dynamic-menu:install
                           {--force : Overwrite existing files}
                           {--no-migration : Skip running migrations}';

    protected $description = 'Install Filament Dynamic Menu package';

    public function handle(): int
    {
        $this->info('Installing Filament Dynamic Menu...');

        $this->publishTag('filament-dynamic-menu-config');
        $this->publishTag('filament-dynamic-menu-migrations');
        $this->publishTag('filament-dynamic-menu-translations');
        $this->publishTag('filament-dynamic-menu-views');

        if (! $this->option('no-migration')) {
            if ($this->confirm('Run migrations now?', true)) {
                $this->call('migrate');
            }
        }

        $this->info('Filament Dynamic Menu installed successfully!');
        $this->line('');
        $this->line('Next steps:');
        $this->line('1. Set FILAMENT_DYNAMIC_MENU_ENABLED=true in your .env file');
        $this->line('2. Add FilamentDynamicMenuPlugin::make() to your PanelProvider plugins');

        return self::SUCCESS;
    }

    protected function publishTag(string $tag): void
    {
        $params = ['--tag' => $tag];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }
}
