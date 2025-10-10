<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disaster_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('disaster_id')->nullable()->constrained('disasters')->onDelete('cascade');
            $table->foreignUuid('reported_by')->nullable()->constrained('disaster_volunteers')->onDelete('cascade');
            $table->string('title', 45);
            $table->text('description')->nullable();
            $table->text('lat')->nullable();
            $table->text('long')->nullable();
            $table->boolean('is_final_stage')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disaster_reports');
    }
};
