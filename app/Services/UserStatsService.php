<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserStats;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserStatsService
{
    /**
     * Update user statistics when a task is completed.
     */
    public function updateStatsOnTaskCompletion(User $user, Task $task): UserStats
    {
        $userStats = $this->getOrCreateUserStats($user);

        // Calculate completion time in minutes
        $completionTime = $this->calculateCompletionTime($task);

        // Calculate distance if available
        $distance = $this->calculateDistance($task);

        // Update basic stats
        $userStats->total_tasks_completed++;
        $userStats->total_distance_km += $distance;

        // Update completion times
        $this->updateCompletionTimes($userStats, $completionTime);

        // Update points (base points + bonus)
        $points = $this->calculateTaskPoints($task, $completionTime);
        $userStats->total_points += $points;
        $userStats->addExperience($points);

        // Update streaks
        $this->updateStreaks($userStats);

        // Update monthly/weekly counters
        $this->updatePeriodCounters($userStats);

        // Update performance metrics
        $this->updatePerformanceMetrics($userStats);

        $userStats->save();

        Log::info("User stats updated on task completion", [
            'user_id' => $user->id,
            'task_id' => $task->id,
            'points_earned' => $points,
            'completion_time' => $completionTime,
            'distance' => $distance,
        ]);

        return $userStats;
    }

    /**
     * Get or create user stats for a user.
     */
    public function getOrCreateUserStats(User $user): UserStats
    {
        return UserStats::firstOrCreate(
            ['user_id' => $user->id],
            [
                'total_tasks_completed' => 0,
                'total_points' => 0,
                'total_distance_km' => 0,
                'fastest_completion_time' => null,
                'avg_completion_time' => 0,
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_task_date' => null,
                'monthly_tasks_completed' => 0,
                'weekly_tasks_completed' => 0,
                'monthly_points' => 0,
                'weekly_points' => 0,
                'level' => 1,
                'experience_points' => 0,
            ]
        );
    }

    /**
     * Reset monthly statistics.
     */
    public function resetMonthlyStats(User $user): void
    {
        $userStats = $user->stats;
        if ($userStats) {
            $userStats->monthly_tasks_completed = 0;
            $userStats->monthly_points = 0;
            $userStats->save();
        }
    }

    /**
     * Reset weekly statistics.
     */
    public function resetWeeklyStats(User $user): void
    {
        $userStats = $user->stats;
        if ($userStats) {
            $userStats->weekly_tasks_completed = 0;
            $userStats->weekly_points = 0;
            $userStats->save();
        }
    }

    /**
     * Calculate task completion time in minutes.
     */
    private function calculateCompletionTime(Task $task): float
    {
        if (!$task->created_at || !$task->completed_at) {
            return 0;
        }

        $createdAt = Carbon::parse($task->created_at);
        $completedAt = Carbon::parse($task->completed_at);

        return $createdAt->diffInMinutes($completedAt);
    }

    /**
     * Calculate distance for a task (if available).
     */
    private function calculateDistance(Task $task): float
    {
        // If your task model has distance field or you can calculate it
        // from pickup and delivery locations, implement here
        // For now, return 0 or a default value
        return $task->distance ?? 0;
    }

    /**
     * Calculate points earned for completing a task.
     */
    private function calculateTaskPoints(Task $task, float $completionTime): int
    {
        $basePoints = 10; // Base points for completing any task

        // Priority bonus
        $priorityBonus = match($task->priority ?? 'medium') {
            'low' => 0,
            'medium' => 5,
            'high' => 10,
            'urgent' => 15,
            default => 5,
        };

        // Speed bonus (for tasks completed under 30 minutes)
        $speedBonus = $completionTime > 0 && $completionTime <= 30 ? 5 : 0;

        return $basePoints + $priorityBonus + $speedBonus;
    }

    /**
     * Update completion time statistics.
     */
    private function updateCompletionTimes(UserStats $userStats, float $completionTime): void
    {
        if ($completionTime <= 0) {
            return;
        }

        // Update fastest time
        if (is_null($userStats->fastest_completion_time) || $completionTime < $userStats->fastest_completion_time) {
            $userStats->fastest_completion_time = $completionTime;
        }

        // Update slowest time
        if (is_null($userStats->slowest_completion_time) || $completionTime > $userStats->slowest_completion_time) {
            $userStats->slowest_completion_time = $completionTime;
        }

        // Update average completion time
        $totalTasks = $userStats->total_tasks_completed + 1; // +1 because we haven't incremented yet
        if ($userStats->avg_completion_time > 0) {
            $userStats->avg_completion_time = (($userStats->avg_completion_time * ($totalTasks - 1)) + $completionTime) / $totalTasks;
        } else {
            $userStats->avg_completion_time = $completionTime;
        }
    }

    /**
     * Update streak counters.
     */
    private function updateStreaks(UserStats $userStats): void
    {
        $today = Carbon::today();
        $lastTaskDate = $userStats->last_task_date ? Carbon::parse($userStats->last_task_date) : null;

        if (!$lastTaskDate) {
            // First task ever
            $userStats->current_streak = 1;
            $userStats->longest_streak = 1;
        } elseif ($lastTaskDate->isYesterday()) {
            // Continuing streak
            $userStats->current_streak++;
            if ($userStats->current_streak > $userStats->longest_streak) {
                $userStats->longest_streak = $userStats->current_streak;
            }
        } elseif ($lastTaskDate->isToday()) {
            // Same day, don't change streak
            // Keep current streak as is
        } else {
            // Streak broken
            $userStats->current_streak = 1;
        }

        $userStats->last_task_date = $today;
    }

    /**
     * Update monthly and weekly counters.
     */
    private function updatePeriodCounters(UserStats $userStats): void
    {
        $now = Carbon::now();

        // Check if we need to reset monthly stats
        if ($userStats->updated_at && !$userStats->updated_at->isSameMonth($now)) {
            $userStats->monthly_tasks_completed = 0;
            $userStats->monthly_points = 0;
        }

        // Check if we need to reset weekly stats
        if ($userStats->updated_at && !$userStats->updated_at->isSameWeek($now)) {
            $userStats->weekly_tasks_completed = 0;
            $userStats->weekly_points = 0;
        }

        // Increment counters
        $userStats->monthly_tasks_completed++;
        $userStats->weekly_tasks_completed++;

        // Points will be added separately
    }

    /**
     * Update performance metrics.
     */
    private function updatePerformanceMetrics(UserStats $userStats): void
    {
        // Calculate performance rating based on various factors
        $completionRate = 100; // Assuming task is completed successfully
        $speedScore = $this->calculateSpeedScore($userStats);
        $consistencyScore = $this->calculateConsistencyScore($userStats);

        // You can expand this with more sophisticated performance calculations
    }

    /**
     * Calculate speed score based on completion times.
     */
    private function calculateSpeedScore(UserStats $userStats): float
    {
        if (!$userStats->avg_completion_time || $userStats->avg_completion_time <= 0) {
            return 0;
        }

        // Lower completion time = higher score (max 100)
        // Assuming 60 minutes is the baseline for score calculation
        $baselineTime = 60;
        return min(100, ($baselineTime / $userStats->avg_completion_time) * 100);
    }

    /**
     * Calculate consistency score based on streak.
     */
    private function calculateConsistencyScore(UserStats $userStats): float
    {
        // Score based on current streak (max 100)
        return min(100, $userStats->current_streak * 10);
    }
}
