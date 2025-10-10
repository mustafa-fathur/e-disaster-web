<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disaster_victims', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('disaster_id')->nullable()->constrained('disasters')->onDelete('cascade');
            $table->foreignUuid('reported_by')->nullable()->constrained('disaster_volunteers')->onDelete('cascade');
            $table->string('nik', 45)->nullable();
            $table->string('name', 45)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->boolean('gender')->nullable();
            $table->string('contact_info', 100)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_evacuated')->default(false);
            $table->enum('status', ['minor_injury', 'serious_injuries', 'lost', 'deceased']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disaster_victims');
    }
};
