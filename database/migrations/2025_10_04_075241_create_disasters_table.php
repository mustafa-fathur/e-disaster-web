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
            $table->string('title', 45);
            $table->text('description')->nullable();
            $table->enum('source', ['BMKG', 'manual'])->default('BMKG');
            $table->enum('types', [
                'gempa bumi', 'tsunami', 'gunung meletus', 'banjir', 'kekeringan',
                'angin topan', 'tahan longsor', 'bencanan non alam', 'bencana sosial'
            ]);
            $table->enum('status', ['ongoing', 'completed'])->default('ongoing');
            $table->date('date')->nullable();
            $table->time('time')->nullable();
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
