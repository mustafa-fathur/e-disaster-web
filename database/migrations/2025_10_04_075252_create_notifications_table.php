<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('title', 45);
            $table->string('message', 50);
            $table->enum('category', [
                'volunteer_verification', 'new_disaster', 'new_disaster_report',
                'new_disaster_victim_report', 'new_disaster_aid_report', 'disaster_status_changed'
            ]);
            $table->boolean('is_read')->default(false);
            $table->timestamp('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
