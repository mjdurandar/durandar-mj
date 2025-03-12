<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_hours_config', function (Blueprint $table) {
            $table->id();
            $table->string('day_of_week');
            $table->time('opening_time');
            $table->time('closing_time');
            $table->time('lunch_break_start')->nullable();
            $table->time('lunch_break_end')->nullable();
            $table->boolean('is_open')->default(true);
            $table->boolean('alternate_weeks_only')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_hours_config');
    }
}; 