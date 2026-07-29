<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_swap_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('requester_schedule_id')->constrained('shift_schedules')->cascadeOnDelete();
            $table->foreignUuid('target_schedule_id')->constrained('shift_schedules')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_swap_requests');
    }
};
