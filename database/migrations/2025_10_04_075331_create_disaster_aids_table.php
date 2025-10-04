<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disaster_aids', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('disaster_id')->nullable()->constrained('disasters')->onDelete('cascade');
            $table->foreignUuid('reported_by')->nullable()->constrained('disaster_volunteers')->onDelete('cascade');
            $table->string('title', 45)->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('unit', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disaster_aids');
    }
};
