<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_groups', function (Blueprint $table) {
            $table->comment('Dynamic Navigation Groups');
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->string('name', 100);
            $table->string('icon', 100)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_collapsible')->default(true);
            $table->boolean('is_collapsed')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->json('roles')->nullable()->comment('Role names that can see this group, null = all roles');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menu_groups');
    }
};
