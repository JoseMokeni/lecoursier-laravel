<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'userId' => $this->user_id,
            'level' => $this->level,
            'experiencePoints' => $this->experience_points,
            'experienceToNextLevel' => $this->getExperienceToNextLevel(),
            'progressToNextLevel' => $this->getProgressToNextLevel(),

            // Task statistics
            'totalTasksCompleted' => $this->total_tasks_completed,
            'tasksThisMonth' => $this->tasks_this_month,
            'tasksThisWeek' => $this->tasks_this_week,

            // Points statistics
            'totalPoints' => $this->total_points,
            'pointsThisMonth' => $this->points_this_month,
            'pointsThisWeek' => $this->points_this_week,

            // Distance statistics
            'totalDistanceKm' => round($this->total_distance_km, 2),

            // Time statistics
            'fastestCompletionTime' => $this->fastest_completion_time,
            'slowestCompletionTime' => $this->slowest_completion_time,
            'avgCompletionTime' => round($this->avg_completion_time, 2),

            // Streak statistics
            'currentStreak' => $this->current_streak,
            'longestStreak' => $this->longest_streak,
            'lastTaskDate' => $this->last_task_date?->toDateString(),

            // Additional computed statistics
            'badgesCount' => $this->user->badges()->count(),
            'completionRate' => $this->getCompletionRate(),
            'performanceScore' => $this->getPerformanceScore(),
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Get experience points needed to reach next level.
     */
    private function getExperienceToNextLevel(): int
    {
        $nextLevelXP = $this->level * 1000;
        return $nextLevelXP - $this->experience_points;
    }

    /**
     * Get progress percentage to next level.
     */
    private function getProgressToNextLevel(): float
    {
        $currentLevelXP = ($this->level - 1) * 1000;
        $nextLevelXP = $this->level * 1000;
        $xpInCurrentLevel = $this->experience_points - $currentLevelXP;
        $xpNeededForLevel = $nextLevelXP - $currentLevelXP;

        return round(($xpInCurrentLevel / $xpNeededForLevel) * 100, 2);
    }

    /**
     * Calculate completion rate (assuming all tasks are completed successfully).
     */
    private function getCompletionRate(): float
    {
        // This is a placeholder - you might want to track failed/cancelled tasks
        // and calculate actual completion rate
        return $this->total_tasks_completed > 0 ? 100.0 : 0.0;
    }

    /**
     * Calculate overall performance score.
     */
    private function getPerformanceScore(): float
    {
        $scores = [];

        // Speed score (lower avg time = higher score)
        if ($this->avg_completion_time > 0) {
            $baselineTime = 60; // 60 minutes baseline
            $speedScore = min(100, ($baselineTime / $this->avg_completion_time) * 100);
            $scores[] = $speedScore;
        }

        // Consistency score (based on streak)
        $consistencyScore = min(100, $this->current_streak * 10);
        $scores[] = $consistencyScore;

        // Activity score (based on tasks completed)
        $activityScore = min(100, $this->total_tasks_completed * 2);
        $scores[] = $activityScore;

        return count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : 0;
    }
}
