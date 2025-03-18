<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Make sure we have users first
        $userCount = User::count();

        if ($userCount === 0) {
            User::factory(5)->create();
        }

        // Create a mix of tasks
        Task::factory(20)->create();
        Task::factory(5)->highPriority()->create();
        Task::factory(10)->completed()->create();
    }
}
