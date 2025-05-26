<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            // Task Completion Badges
            [
                'name' => 'First Delivery',
                'description' => 'Complete your first task',
                'icon' => '🎯',
                'category' => 'task_completion',
                'criteria' => ['tasks_completed' => 1],
                'points' => 10,
                'rarity' => 'bronze',
                'is_active' => true,
            ],
            [
                'name' => 'Getting Started',
                'description' => 'Complete 5 tasks',
                'icon' => '🚀',
                'category' => 'task_completion',
                'criteria' => ['tasks_completed' => 5],
                'points' => 25,
                'rarity' => 'bronze',
                'is_active' => true,
            ],
            [
                'name' => 'Task Master',
                'description' => 'Complete 25 tasks',
                'icon' => '💪',
                'category' => 'task_completion',
                'criteria' => ['tasks_completed' => 25],
                'points' => 50,
                'rarity' => 'silver',
                'is_active' => true,
            ],
            [
                'name' => 'Marathon Runner',
                'description' => 'Complete 50 tasks',
                'icon' => '🏃‍♂️',
                'category' => 'task_completion',
                'criteria' => ['tasks_completed' => 50],
                'points' => 100,
                'rarity' => 'gold',
                'is_active' => true,
            ],
            [
                'name' => 'Legendary Courier',
                'description' => 'Complete 100 tasks',
                'icon' => '👑',
                'category' => 'task_completion',
                'criteria' => ['tasks_completed' => 100],
                'points' => 200,
                'rarity' => 'platinum',
                'is_active' => true,
            ],

            // Speed Badges
            [
                'name' => 'Speed Demon',
                'description' => 'Complete 5 tasks in under 30 minutes',
                'icon' => '⚡',
                'category' => 'speed',
                'criteria' => ['fast_completions' => 5],
                'points' => 30,
                'rarity' => 'silver',
                'is_active' => true,
            ],
            [
                'name' => 'Lightning Fast',
                'description' => 'Complete 10 tasks in under 20 minutes',
                'icon' => '⚡⚡',
                'category' => 'speed',
                'criteria' => ['very_fast_completions' => 10],
                'points' => 50,
                'rarity' => 'gold',
                'is_active' => true,
            ],
            [
                'name' => 'Efficiency Expert',
                'description' => 'Maintain average completion time under 30 minutes',
                'icon' => '🎯⏱️',
                'category' => 'speed',
                'criteria' => ['avg_completion_time' => 30, 'min_tasks' => 10],
                'points' => 40,
                'rarity' => 'gold',
                'is_active' => true,
            ],

            // Consistency Badges
            [
                'name' => 'Streak Starter',
                'description' => 'Complete tasks for 3 consecutive days',
                'icon' => '🔥',
                'category' => 'consistency',
                'criteria' => ['current_streak' => 3],
                'points' => 20,
                'rarity' => 'bronze',
                'is_active' => true,
            ],
            [
                'name' => 'Consistent Performer',
                'description' => 'Complete tasks for 7 consecutive days',
                'icon' => '🔥🔥',
                'category' => 'consistency',
                'criteria' => ['current_streak' => 7],
                'points' => 35,
                'rarity' => 'silver',
                'is_active' => true,
            ],
            [
                'name' => 'Unstoppable',
                'description' => 'Complete tasks for 30 consecutive days',
                'icon' => '🔥🔥🔥',
                'category' => 'consistency',
                'criteria' => ['longest_streak' => 30],
                'points' => 100,
                'rarity' => 'platinum',
                'is_active' => true,
            ],

            // Milestone Badges
            [
                'name' => 'Distance Tracker',
                'description' => 'Travel 100 kilometers total',
                'icon' => '🗺️',
                'category' => 'milestone',
                'criteria' => ['total_distance_km' => 100],
                'points' => 30,
                'rarity' => 'silver',
                'is_active' => true,
            ],
            [
                'name' => 'Long Hauler',
                'description' => 'Travel 500 kilometers total',
                'icon' => '🚛',
                'category' => 'milestone',
                'criteria' => ['total_distance_km' => 500],
                'points' => 75,
                'rarity' => 'gold',
                'is_active' => true,
            ],
            [
                'name' => 'Point Collector',
                'description' => 'Earn 100 total points',
                'icon' => '💎',
                'category' => 'milestone',
                'criteria' => ['total_points' => 100],
                'points' => 25,
                'rarity' => 'bronze',
                'is_active' => true,
            ],
            [
                'name' => 'High Achiever',
                'description' => 'Earn 500 total points',
                'icon' => '💎💎',
                'category' => 'milestone',
                'criteria' => ['total_points' => 500],
                'points' => 50,
                'rarity' => 'gold',
                'is_active' => true,
            ],

            // Special Badges
            [
                'name' => 'Early Bird',
                'description' => 'Complete 10 tasks before 9 AM',
                'icon' => '🌅',
                'category' => 'special',
                'criteria' => ['early_completions' => 10],
                'points' => 40,
                'rarity' => 'silver',
                'is_active' => true,
            ],
            [
                'name' => 'Night Owl',
                'description' => 'Complete 10 tasks after 8 PM',
                'icon' => '🦉',
                'category' => 'special',
                'criteria' => ['late_completions' => 10],
                'points' => 40,
                'rarity' => 'silver',
                'is_active' => true,
            ],
            [
                'name' => 'Weekend Warrior',
                'description' => 'Complete 20 tasks on weekends',
                'icon' => '⚔️',
                'category' => 'special',
                'criteria' => ['weekend_completions' => 20],
                'points' => 35,
                'rarity' => 'silver',
                'is_active' => true,
            ],
            [
                'name' => 'Perfect Month',
                'description' => 'Complete at least one task every day for a month',
                'icon' => '📅✨',
                'category' => 'special',
                'criteria' => ['perfect_month' => 1],
                'points' => 150,
                'rarity' => 'platinum',
                'is_active' => true,
            ],
        ];

        foreach ($badges as $badgeData) {
            Badge::create($badgeData);
        }
    }
}
