<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE cash_transactions DROP CONSTRAINT cash_transactions_source_check");
        DB::statement("ALTER TABLE cash_transactions ADD CONSTRAINT cash_transactions_source_check CHECK (source IN ('pos', 'purchase_order', 'payroll', 'manual', 'refund'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE cash_transactions DROP CONSTRAINT cash_transactions_source_check");
        DB::statement("ALTER TABLE cash_transactions ADD CONSTRAINT cash_transactions_source_check CHECK (source IN ('pos', 'purchase_order', 'payroll', 'manual'))");
    }
};
