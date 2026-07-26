<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('month');
            $table->decimal('base_salary', 12, 2);
            $table->decimal('total_late_deduction', 12, 2)->default(0);
            $table->decimal('total_absence_deduction', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2);
            $table->enum('status', ['draft', 'final', 'paid'])->default('draft');
            $table->timestamps();

            $table->unique(['employee_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};
