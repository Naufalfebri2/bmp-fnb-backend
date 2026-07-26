<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('phone', 20);
            $table->enum('role', ['staff', 'admin', 'owner']);
            $table->date('start_date');
            $table->decimal('base_salary', 12, 2);
            $table->decimal('remaining_leave_quota', 4, 1)->default(12);
            $table->boolean('is_active')->default(true);
            $table->jsonb('custom_fields')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
