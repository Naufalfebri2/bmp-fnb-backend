<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->foreignUuid('shift_schedule_id')
                ->nullable()
                ->after('employee_id')
                ->constrained('shift_schedules')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropForeign(['shift_schedule_id']);
            $table->dropColumn('shift_schedule_id');
        });
    }
};
