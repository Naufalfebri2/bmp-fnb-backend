<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_outflows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('daily_stock_id')->constrained('daily_stocks')->cascadeOnDelete();
            $table->enum('category', ['production', 'waste', 'supplier_return']);
            $table->decimal('quantity', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_outflows');
    }
};
