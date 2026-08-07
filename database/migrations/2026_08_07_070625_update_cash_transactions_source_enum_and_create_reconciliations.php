<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE cash_transactions DROP CONSTRAINT cash_transactions_source_check");
        DB::statement("ALTER TABLE cash_transactions ADD CONSTRAINT cash_transactions_source_check CHECK (source IN ('pos', 'purchase_order', 'payroll', 'manual', 'refund', 'adjustment'))");

        Schema::create('cash_reconciliations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cash_account_id')->constrained('cash_accounts')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('system_balance', 12, 2);
            $table->decimal('physical_balance', 12, 2);
            $table->decimal('difference', 12, 2);
            $table->enum('status', ['completed', 'pending_approval', 'rejected'])->default('completed');
            $table->foreignUuid('reconciled_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('adjustment_transaction_id')->nullable()->constrained('cash_transactions')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['cash_account_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_reconciliations');

        DB::statement("ALTER TABLE cash_transactions DROP CONSTRAINT cash_transactions_source_check");
        DB::statement("ALTER TABLE cash_transactions ADD CONSTRAINT cash_transactions_source_check CHECK (source IN ('pos', 'purchase_order', 'payroll', 'manual'))");
    }
};