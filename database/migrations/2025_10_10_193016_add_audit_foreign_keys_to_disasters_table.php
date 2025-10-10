<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disasters', function (Blueprint $table) {
            $table->foreign('cancelled_by')->references('id')->on('disaster_volunteers')->onDelete('set null');
            $table->foreign('completed_by')->references('id')->on('disaster_volunteers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('disasters', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropForeign(['completed_by']);
        });
    }
};