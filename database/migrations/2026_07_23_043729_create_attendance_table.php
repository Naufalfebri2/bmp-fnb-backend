<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->dateTime('check_in_time')->nullable();
            $table->dateTime('check_out_time')->nullable();
            $table->string('check_in_photo', 255)->nullable();
            $table->string('check_out_photo', 255)->nullable();
            $table->decimal('location_lat', 10, 8)->nullable();
            $table->decimal('location_long', 11, 8)->nullable();
            $table->integer('late_minutes')->default(0);
            $table->enum('status', [
                'on_time',
                'late',
                'sick_with_letter',
                'sick_without_letter',
                'leave',
                'time_off',
                'absent',
            ]);
            $table->string('supporting_document', 255)->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
