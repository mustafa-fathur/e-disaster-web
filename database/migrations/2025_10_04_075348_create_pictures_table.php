<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pictures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('foreign_id');
            $table->enum('type', ['profile', 'disaster', 'disaster_report', 'disaster_victim', 'disaster_aid']);
            $table->string('caption')->nullable();
            $table->string('file_path', 255);
            $table->string('mine_type', 50)->nullable();
            $table->string('alt_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pictures');
    }
};
