<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('table_bookings', function (Blueprint $table) {
            $table->foreignUuid('table_id')->nullable()->after('outlet_id')->constrained('tables')->nullOnDelete();
            $table->integer('duration_minutes')->default(120)->after('booking_datetime');
        });

        DB::statement("ALTER TABLE table_bookings DROP CONSTRAINT table_bookings_status_check");
        DB::statement("ALTER TABLE table_bookings ADD CONSTRAINT table_bookings_status_check CHECK (status IN ('pending', 'awaiting_deposit', 'confirmed', 'seated', 'no_show', 'cancelled'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE table_bookings DROP CONSTRAINT table_bookings_status_check");
        DB::statement("ALTER TABLE table_bookings ADD CONSTRAINT table_bookings_status_check CHECK (status IN ('pending', 'confirmed', 'seated', 'no_show', 'cancelled'))");

        Schema::table('table_bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('table_id');
            $table->dropColumn('duration_minutes');
        });
    }
};
