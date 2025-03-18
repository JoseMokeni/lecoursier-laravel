<?php

namespace Database\Seeders;

use App\Models\Milestone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MilestoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create regular milestones
        Milestone::factory(15)->create();

        // Create favorite milestones
        Milestone::factory(5)->favorite()->create();
    }
}
