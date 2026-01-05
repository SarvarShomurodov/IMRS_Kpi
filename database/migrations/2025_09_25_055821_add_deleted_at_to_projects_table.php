<?php
// Migration fayli (database/migrations/xxxx_add_deleted_at_to_projects_table.php)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->softDeletes(); // Bu deleted_at ustunini qo'shadi
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};