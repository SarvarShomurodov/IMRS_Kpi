<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('task_assignments', function (Blueprint $table) {
            // ✅ Composite indexes
            $table->index(['user_id', 'addDate'], 'idx_ta_user_date');
            $table->index(['addDate', 'rating'], 'idx_ta_date_rating');
            $table->index('project_id', 'idx_ta_project');
            $table->index('subtask_id', 'idx_ta_subtask');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'idx_a_user_date');
            $table->index(['date', 'rating'], 'idx_a_date_rating');
            $table->index(['task_id', 'subtask_id'], 'idx_a_task_subtask');
            $table->index('rating', 'idx_a_rating');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('position', 'idx_u_position');
            $table->index('project_id', 'idx_u_project');
            $table->index('created_at', 'idx_u_created');
        });

        Schema::table('sub_tasks', function (Blueprint $table) {
            $table->index('task_id', 'idx_st_task');
        });
    }

    public function down()
    {
        Schema::table('task_assignments', function (Blueprint $table) {
            $table->dropIndex('idx_ta_user_date');
            $table->dropIndex('idx_ta_date_rating');
            $table->dropIndex('idx_ta_project');
            $table->dropIndex('idx_ta_subtask');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex('idx_a_user_date');
            $table->dropIndex('idx_a_date_rating');
            $table->dropIndex('idx_a_task_subtask');
            $table->dropIndex('idx_a_rating');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_u_position');
            $table->dropIndex('idx_u_project');
            $table->dropIndex('idx_u_created');
        });

        Schema::table('sub_tasks', function (Blueprint $table) {
            $table->dropIndex('idx_st_task');
        });
    }
};