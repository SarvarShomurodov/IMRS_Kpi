<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Attendances table (users table allaqachon mavjud)
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('morning_in')->nullable();
            $table->time('lunch_out')->nullable();
            $table->time('lunch_in')->nullable();
            $table->time('evening_out')->nullable();
            $table->integer('morning_late')->default(0); // daqiqalar
            $table->integer('lunch_duration')->default(0); // daqiqalar
            $table->integer('early_leave')->default(0); // daqiqalar
            $table->text('morning_comment')->nullable();
            $table->text('lunch_comment')->nullable();
            $table->text('evening_comment')->nullable();
            $table->text('day_comment')->nullable(); // kelmagan kunlar uchun
            $table->timestamps();
            
            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};