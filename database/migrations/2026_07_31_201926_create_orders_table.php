<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->foreignUuid('table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->string('order_number', 30)->unique();
            $table->string('customer_name', 100)->nullable();
            $table->enum('order_type', ['dine_in', 'qr_dine_in', 'online_pickup', 'online_delivery'])->default('dine_in');
            $table->enum('status', ['open', 'paid', 'partially_refunded', 'refunded', 'cancelled'])->default('open');
            $table->foreignUuid('opened_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
