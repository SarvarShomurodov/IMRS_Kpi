<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Faker\Factory as Faker;
class TaskAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // DB::table('task_assignments')->truncate();
        $faker = Faker::create();

        // Ruxsat etilgan user_id ro'yxati (1-57, 3 va 47 tashlab ketiladi)
        $userIds = collect(range(1, 57))->reject(function ($id) {
            return in_array($id, [3, 47]);
        })->values()->all(); // indexed array qilish

        for ($i = 0; $i < 100400; $i++) {
            // Tasodifiy sanani yaratish (faqat 2025 yil)
            $addDate = $faker->dateTimeBetween('2023-01-01', '2025-12-31')->format('Y-m-d');
        
            // Tasodifiy subtask_id
            $subtaskId = rand(1, 87);
        
            // Ratingni shartga qarab belgilash
            if ($subtaskId >= 79 && $subtaskId <= 87) {
                // -0.5 dan -10.0 gacha float manfiy son
                $rating = $faker->randomFloat(1, -10, -0.5);
            } else {
                $rating = rand(1, 5); // 1 dan 5 gacha butun ijobiy sonlar
            }
        
            DB::table('task_assignments')->insert([
                'subtask_id' => $subtaskId,
                'user_id' => $faker->randomElement($userIds),
                'rating' => $rating,
                'comment' => $faker->optional()->sentence,
                'addDate' => $addDate,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }        
    }
}
