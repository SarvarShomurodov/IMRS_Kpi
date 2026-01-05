<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::table('task_assignments', function (Blueprint $table) {
            // project_id ustunini qo'shish (agar yo'q bo'lsa)
            if (!Schema::hasColumn('task_assignments', 'project_id')) {
                $table->unsignedBigInteger('project_id')->nullable()->after('user_id');
                
                // Foreign key qo'shish
                $table->foreign('project_id')
                    ->references('id')
                    ->on('projects')
                    ->onDelete('set null'); // Project o'chirilsa NULL bo'lsin
            }
        });

        // ✅ MUHIM: Mavjud ma'lumotlarni to'ldirish
        // User ning project_id si bilan task_assignment ni to'ldirish
        DB::statement("
            UPDATE task_assignments ta
            INNER JOIN users u ON ta.user_id = u.id
            SET ta.project_id = u.project_id
            WHERE ta.project_id IS NULL
            AND u.project_id IS NOT NULL
        ");
    }

    public function down()
    {
        Schema::table('task_assignments', function (Blueprint $table) {
            // Foreign key ni o'chirish
            $table->dropForeign(['project_id']);
            // Ustunni o'chirish
            $table->dropColumn('project_id');
        });
    }
};