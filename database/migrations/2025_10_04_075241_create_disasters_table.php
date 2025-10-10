<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disasters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('reported_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('source', ['bmkg', 'manual'])->default('bmkg');
            $table->enum('types', [
                'earthquake', 'tsunami', 'volcanic_eruption', 'flood', 'drought',
                'tornado', 'landslide', 'non_natural_disaster', 'social_disaster'
            ]);
            $table->enum('status', ['cancelled', 'ongoing', 'completed'])->default('ongoing');
            $table->string('title', 45);
            $table->text('description')->nullable();
            $table->date('date');
            $table->time('time');
            $table->string('location', 45)->nullable();
            $table->text('coordinate')->nullable();
            $table->float('lat')->nullable();
            $table->float('long')->nullable();
            $table->float('magnitude')->nullable();
            $table->float('depth')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disasters');
    }
};
