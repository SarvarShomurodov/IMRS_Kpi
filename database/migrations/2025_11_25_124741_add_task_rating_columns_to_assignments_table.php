<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1-qadam: Ustunlarni qo'shamiz (tez)
        Schema::table('assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('task_id')->nullable()->after('user_id');
            $table->unsignedBigInteger('subtask_id')->nullable()->after('task_id');
            $table->decimal('rating', 8, 2)->nullable()->after('subtask_id');
        });

        // 2-qadam: Indexlar qo'shamiz (sekin bo'lishi mumkin, lekin zarur)
        // Index query tezligini 100x oshiradi
        Schema::table('assignments', function (Blueprint $table) {
            $table->index('task_id', 'idx_assignments_task');
            $table->index('subtask_id', 'idx_assignments_subtask');
            $table->index(['user_id', 'task_id'], 'idx_assignments_user_task'); // Composite index - tezlashtirish uchun
        });

        // 3-qadam: Foreign key qo'shamiz
        // set null - task/subtask o'chirilsa, assignmentlar saqlanadi
        Schema::table('assignments', function (Blueprint $table) {
            $table->foreign('task_id', 'fk_assignments_task')
                  ->references('id')
                  ->on('tasks')
                  ->onDelete('set null');
                  
            $table->foreign('subtask_id', 'fk_assignments_subtask')
                  ->references('id')
                  ->on('sub_tasks')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('assignments', function (Blueprint $table) {
            // 1. Avval foreign keylarni o'chirish
            $table->dropForeign('fk_assignments_task');
            $table->dropForeign('fk_assignments_subtask');
            
            // 2. Indexlarni o'chirish
            $table->dropIndex('idx_assignments_task');
            $table->dropIndex('idx_assignments_subtask');
            $table->dropIndex('idx_assignments_user_task');
            
            // 3. Ustunlarni o'chirish
            $table->dropColumn(['task_id', 'subtask_id', 'rating']);
        });
    }
};