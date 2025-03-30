<?php

namespace Database\Seeders;

use App\Models\Milestone;
use App\Models\Task;
use App\Models\TaskMilestone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskMilestoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Make sure we have tasks and milestones
        if (Task::count() === 0) {
            $this->call(TaskSeeder::class);
        }

        if (Milestone::count() === 0) {
            $this->call(MilestoneSeeder::class);
        }

        // For each task, assign 1-3 random milestones
        Task::all()->each(function ($task) {
            $milestoneIds = Milestone::inRandomOrder()
                ->limit(rand(1, 3))
                ->pluck('id');

            $task->milestones()->attach($milestoneIds);
        });
    }
}
