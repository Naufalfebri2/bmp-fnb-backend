<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_stocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('opening_stock', 10, 2);
            $table->decimal('expected_closing_stock', 10, 2)->nullable();
            $table->decimal('actual_closing_stock', 10, 2)->nullable();
            $table->decimal('variance', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['ingredient_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_stocks');
    }
};
