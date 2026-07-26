<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->string('customer_name', 100);
            $table->string('phone', 20);
            $table->integer('guest_count');
            $table->dateTime('booking_datetime');
            $table->enum('status', ['pending', 'confirmed', 'seated', 'no_show', 'cancelled'])->default('pending');
            $table->boolean('is_event')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_bookings');
    }
};
