<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cash_account_id')->constrained('cash_accounts')->cascadeOnDelete();
            $table->date('date');
            $table->enum('type', ['in', 'out']);
            $table->enum('source', ['pos', 'purchase_order', 'payroll', 'manual']);
            $table->decimal('amount', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
