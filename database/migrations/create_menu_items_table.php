<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->comment('Dynamic Navigation Items');
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->foreignId('menu_group_id')->nullable()->constrained('menu_groups')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->string('type', 20)->default('resource')->comment('resource, page, cluster, url, separator');
            $table->string('label', 100);
            $table->string('icon', 100)->nullable();
            $table->string('target', 200)->nullable()->comment('FQCN of resource/page/cluster');
            $table->string('url', 500)->nullable()->comment('Custom URL for type=url');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('roles')->nullable()->comment('Role names that can see this item, null = all roles');
            $table->json('permissions')->nullable()->comment('Permission names required');
            $table->boolean('open_in_new_tab')->default(false);
            $table->string('badge', 100)->nullable();
            $table->string('badge_color', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();

            if (config('filament-dynamic-menu.tenant_mode') !== 'single') {
                $table->index(['tenant_id', 'menu_group_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
