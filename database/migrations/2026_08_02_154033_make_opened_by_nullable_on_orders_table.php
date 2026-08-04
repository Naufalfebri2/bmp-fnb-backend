<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['opened_by']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignUuid('opened_by')->nullable()->change();
            $table->foreign('opened_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['opened_by']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignUuid('opened_by')->nullable(false)->change();
            $table->foreign('opened_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
