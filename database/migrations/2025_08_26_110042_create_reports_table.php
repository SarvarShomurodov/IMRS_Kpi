<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['weekly', 'monthly'])->default('weekly');
            $table->date('start_date'); // Haftalik uchun dushanba, oylik uchun oyning 1-sanasi
            $table->date('end_date');   // Haftalik uchun juma, oylik uchun oyning oxiri
            $table->longText('content');
            $table->boolean('is_editable')->default(true);
            $table->json('admin_reviews')->nullable(); // Admin ko'rib chiqishlari
            $table->integer('approved_count')->default(0); // Nechta admin tasdiqlagan
            $table->integer('rejected_count')->default(0); // Nechta admin rad etgan
            $table->timestamps();
            
            // Bir xodim bir hafta/oy uchun faqat bitta xisobot yoza oladi
            $table->unique(['user_id', 'type', 'start_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
};