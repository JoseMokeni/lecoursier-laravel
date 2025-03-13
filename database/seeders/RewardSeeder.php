<?php

namespace Database\Seeders;

use App\Models\Reward;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RewardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 20 rewards with random users
        Reward::factory(20)->create();

        // Alternatively, you can create rewards for specific users
        // Create 5 rewards for each of 3 users
        User::factory(3)->create()->each(function ($user) {
            Reward::factory(5)->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
