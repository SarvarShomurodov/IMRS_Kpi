<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'create-role',
            'edit-role',
            'delete-role',
            'create-user',
            'edit-user',
            'delete-user',
            'view-project',
            'create-project',
            'edit-project',
            'delete-project',
            'view-task',
            'create-task',
            'edit-task',
            'delete-task',
            'view-subtask',
            'create-subtask',
            'edit-subtask',
            'delete-subtask',
            'view-taskassign',
            'create-taskassign',
            'edit-taskassign',
            'delete-taskassign',
            // 👇 Qo‘shimcha qilib quyidagilarni qo‘shing:
            'view assignments',
            'edit assignments',
            'delete assignments',
            'view own assignment',
            'edit own assignment',
            'delete own assignment',
         ];
 
          // Looping and Inserting Array's Permissions into Permission Table
          foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}