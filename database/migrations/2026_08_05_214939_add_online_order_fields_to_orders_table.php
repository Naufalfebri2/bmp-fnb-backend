<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // online_pickup fields
            $table->string('customer_phone', 20)->nullable()->after('customer_name');
            $table->timestamp('requested_pickup_time')->nullable()->after('acknowledged_by');

            // online_delivery fields
            $table->enum('source_platform', ['grab', 'gojek', 'shopeefood', 'direct_call', 'other'])
                ->nullable()
                ->after('requested_pickup_time');
            $table->string('platform_order_id', 50)->nullable()->after('source_platform');
            $table->enum('input_method', ['manual', 'api'])->default('manual')->after('platform_order_id');

            // courier tracking (online_delivery only)
            $table->enum('courier_status', ['pending', 'prepared', 'picked_up_by_courier'])
                ->nullable()
                ->after('input_method');
            $table->timestamp('courier_picked_up_at')->nullable()->after('courier_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_phone',
                'requested_pickup_time',
                'source_platform',
                'platform_order_id',
                'input_method',
                'courier_status',
                'courier_picked_up_at',
            ]);
        });
    }
};
