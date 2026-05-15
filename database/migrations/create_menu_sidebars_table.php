<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_sidebars', function (Blueprint $table) {
            $table->comment('Menu Sidebar Settings per Tenant');
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->boolean('collapsible_navigation_groups')->default(false);
            $table->boolean('sidebar_collapsible_on_desktop')->default(true);
            $table->boolean('sidebar_fully_collapsible_on_desktop')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_sidebars');
    }
};
