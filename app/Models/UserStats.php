<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStats extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_tasks_completed',
        'total_points',
        'total_distance_km',
        'avg_completion_time',
        'fastest_completion_time',
        'current_streak',
        'longest_streak',
        'last_task_date',
        'level',
        'experience_points',
        'monthly_tasks_completed',
        'weekly_tasks_completed',
        'monthly_points',
        'weekly_points',
    ];

    protected $casts = [
        'last_task_date' => 'datetime',
        'total_distance_km' => 'decimal:2',
        'avg_completion_time' => 'decimal:2',
        'fastest_completion_time' => 'decimal:2',
        'total_points' => 'integer',
        'experience_points' => 'integer',
        'level' => 'integer',
        'total_tasks_completed' => 'integer',
        'current_streak' => 'integer',
        'longest_streak' => 'integer',
        'monthly_tasks_completed' => 'integer',
        'weekly_tasks_completed' => 'integer',
        'monthly_points' => 'integer',
        'weekly_points' => 'integer',
    ];

    /**
     * Get the user that owns the stats.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate level based on experience points.
     */
    public function calculateLevel(): int
    {
        // Simple level calculation: every 1000 XP = 1 level
        return floor($this->experience_points / 1000) + 1;
    }

    /**
     * Get XP needed for next level.
     */
    public function getXpForNextLevel(): int
    {
        $nextLevel = $this->level + 1;
        return ($nextLevel - 1) * 1000;
    }

    /**
     * Get XP progress in current level.
     */
    public function getXpProgressInLevel(): int
    {
        $currentLevelMinXp = ($this->level - 1) * 1000;
        return $this->experience_points - $currentLevelMinXp;
    }

    /**
     * Update streak based on task completion.
     */
    public function updateStreak(): void
    {
        $today = now()->startOfDay();
        $lastTaskDate = $this->last_task_date ? $this->last_task_date->startOfDay() : null;

        if (!$lastTaskDate) {
            $this->current_streak = 1;
        } elseif ($lastTaskDate->equalTo($today)) {
            // Already completed task today, no change to streak
            return;
        } elseif ($lastTaskDate->equalTo($today->subDay())) {
            // Completed task yesterday, increment streak
            $this->current_streak++;
        } else {
            // Break in streak, reset to 1
            $this->current_streak = 1;
        }

        // Update longest streak if current is higher
        if ($this->current_streak > $this->longest_streak) {
            $this->longest_streak = $this->current_streak;
        }

        $this->last_task_date = now();
    }

    /**
     * Add experience points and update level.
     */
    public function addExperience(int $xp): void
    {
        $this->experience_points += $xp;
        $newLevel = $this->calculateLevel();

        if ($newLevel > $this->level) {
            $this->level = $newLevel;
        }
    }
}
