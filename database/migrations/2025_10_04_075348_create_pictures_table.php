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
            $table->string('foreign_id', 45)->nullable();
            $table->enum('type', ['profile', 'disaster', 'report', 'victim', 'aid']);
            $table->string('caption', 45)->nullable();
            $table->string('file_path', 45)->nullable();
            $table->string('mine_type', 45)->nullable();
            $table->string('alt_text', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pictures');
    }
};
