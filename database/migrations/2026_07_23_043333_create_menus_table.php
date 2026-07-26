<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->foreignUuid('main_ingredient_id')->nullable()->constrained('ingredients')->nullOnDelete();
            $table->string('name', 100);
            $table->decimal('price', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->jsonb('custom_fields')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
