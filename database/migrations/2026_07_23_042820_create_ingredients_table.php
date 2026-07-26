<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('unit', 20);
            $table->enum('risk_category', ['perishable', 'dry_goods']);
            $table->decimal('alert_threshold', 10, 2);
            $table->jsonb('custom_fields')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
