<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserStats;
use App\Events\BadgeEarned;
use App\Jobs\SendFcmNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BadgeService
{
    /**
     * Check if a user qualifies for any badges and award them.
     */
    public function checkAndAwardBadges(User $user): Collection
    {
        $earnedBadges = collect();
        $activebadges = Badge::active()->get();

        foreach ($activebadges as $badge) {
            if (!$badge->hasBeenEarnedBy($user) && $this->userQualifiesForBadge($user, $badge)) {
                $earnedBadge = $this->awardBadge($user, $badge);
                $earnedBadges->push($earnedBadge);
            }
        }

        return $earnedBadges;
    }

    /**
     * Award a specific badge to a user.
     */
    public function awardBadge(User $user, Badge $badge): UserBadge
    {
        $userBadge = UserBadge::create([
            'user_id' => $user->id,
            'badge_id' => $badge->id,
            'earned_at' => now(),
            'progress' => $this->calculateProgress($user, $badge),
        ]);

        // Add points to user stats
        $this->addPointsToUser($user, $badge->points);

        $tenantId = tenancy()->tenant->id;

        // Fire badge earned event
        event(new BadgeEarned($user, $badge, $tenantId));

        // Send FCM notification
        SendFcmNotification::dispatch($user->id, 'Nouveau badge gagné !', 'Vous avez gagné le badge ' . $badge->name . ' !');

        Log::info("Badge awarded", [
            'user_id' => $user->id,
            'badge_id' => $badge->id,
            'badge_name' => $badge->name,
            'points' => $badge->points,
        ]);

        return $userBadge;
    }

    /**
     * Check if a user qualifies for a specific badge.
     */
    public function userQualifiesForBadge(User $user, Badge $badge): bool
    {
        $criteria = $badge->criteria;
        $userStats = $user->stats;

        if (!$userStats) {
            return false;
        }

        switch ($badge->category) {
            case 'task_completion':
                return $this->checkTaskCompletionCriteria($userStats, $criteria);

            case 'points':
                return $this->checkPointsCriteria($userStats, $criteria);

            case 'streak':
                return $this->checkStreakCriteria($userStats, $criteria);

            case 'speed':
                return $this->checkSpeedCriteria($userStats, $criteria);

            case 'distance':
                return $this->checkDistanceCriteria($userStats, $criteria);

            case 'level':
                return $this->checkLevelCriteria($userStats, $criteria);

            case 'special':
                return $this->checkSpecialCriteria($user, $criteria);

            default:
                return false;
        }
    }

    /**
     * Get progress towards a badge for a user.
     */
    public function getBadgeProgress(User $user, Badge $badge): array
    {
        $criteria = $badge->criteria;
        $userStats = $user->stats;

        if (!$userStats) {
            return ['current' => 0, 'required' => 1, 'percentage' => 0];
        }

        return $this->calculateProgress($user, $badge);
    }

    /**
     * Get all badges with progress for a user.
     */
    public function getBadgesWithProgress(User $user): Collection
    {
        $badges = Badge::active()->get();

        return $badges->map(function ($badge) use ($user) {
            $hasEarned = $badge->hasBeenEarnedBy($user);
            $progress = $hasEarned ? null : $this->getBadgeProgress($user, $badge);

            return [
                'badge' => $badge,
                'earned' => $hasEarned,
                'earned_at' => $hasEarned ? $badge->getProgressFor($user) : null,
                'progress' => $progress,
            ];
        });
    }

    /**
     * Check task completion criteria.
     */
    private function checkTaskCompletionCriteria(UserStats $userStats, array $criteria): bool
    {
        $required = $criteria['tasks_completed'] ?? 0;
        return $userStats->total_tasks_completed >= $required;
    }

    /**
     * Check points criteria.
     */
    private function checkPointsCriteria(UserStats $userStats, array $criteria): bool
    {
        $required = $criteria['points'] ?? 0;
        return $userStats->total_points >= $required;
    }

    /**
     * Check streak criteria.
     */
    private function checkStreakCriteria(UserStats $userStats, array $criteria): bool
    {
        $required = $criteria['streak'] ?? 0;
        return $userStats->current_streak >= $required || $userStats->longest_streak >= $required;
    }

    /**
     * Check speed criteria.
     */
    private function checkSpeedCriteria(UserStats $userStats, array $criteria): bool
    {
        $requiredAvgTime = $criteria['avg_completion_time'] ?? 0;
        $minTasks = $criteria['min_tasks'] ?? 1;

        // Check if user has completed minimum required tasks
        if ($userStats->total_tasks_completed < $minTasks) {
            return false;
        }

        // Check if average completion time meets the requirement
        return $userStats->avg_completion_time > 0 && $userStats->avg_completion_time <= $requiredAvgTime;
    }

    /**
     * Check distance criteria.
     */
    private function checkDistanceCriteria(UserStats $userStats, array $criteria): bool
    {
        $required = $criteria['distance'] ?? 0;
        return $userStats->total_distance_km >= $required;
    }

    /**
     * Check level criteria.
     */
    private function checkLevelCriteria(UserStats $userStats, array $criteria): bool
    {
        $required = $criteria['level'] ?? 0;
        return $userStats->level >= $required;
    }

    /**
     * Check special criteria (custom logic).
     */
    private function checkSpecialCriteria(User $user, array $criteria): bool
    {
        // Implement custom criteria logic here
        // This could include combinations of different stats, time-based achievements, etc.
        return false;
    }

    /**
     * Calculate progress towards a badge.
     */
    private function calculateProgress(User $user, Badge $badge): array
    {
        $criteria = $badge->criteria;
        $userStats = $user->stats;

        if (!$userStats) {
            return ['current' => 0, 'required' => 1, 'percentage' => 0];
        }

        switch ($badge->category) {
            case 'task_completion':
                $current = (int) $userStats->total_tasks_completed;
                $required = (int) ($criteria['tasks_completed'] ?? 1);
                break;

            case 'points':
                $current = (int) $userStats->total_points;
                $required = (int) ($criteria['points'] ?? 1);
                break;

            case 'streak':
                $current = (int) max($userStats->current_streak, $userStats->longest_streak);
                $required = (int) ($criteria['streak'] ?? 1);
                break;

            case 'speed':
                $requiredAvgTime = (float) ($criteria['avg_completion_time'] ?? 1);
                $minTasks = (int) ($criteria['min_tasks'] ?? 1);
                $current = (float) $userStats->avg_completion_time;

                // For speed badges with min_tasks requirement, we need to check task count first
                if ($userStats->total_tasks_completed < $minTasks) {
                    // Show progress towards minimum tasks requirement
                    return [
                        'current' => (int) $userStats->total_tasks_completed,
                        'required' => $minTasks,
                        'percentage' => round(min(100, ($userStats->total_tasks_completed / $minTasks) * 100), 2),
                        'requirement_type' => 'min_tasks'
                    ];
                }

                // Show progress towards time requirement
                if ($requiredAvgTime > 0 && $current > 0) {
                    $percentage = min(100, ($requiredAvgTime / $current) * 100);
                } else {
                    $percentage = 0;
                }
                return [
                    'current' => round($current, 2),
                    'required' => $requiredAvgTime,
                    'percentage' => round($percentage, 2),
                    'requirement_type' => 'avg_time'
                ];

            case 'distance':
                $current = (float) $userStats->total_distance_km;
                $required = (float) ($criteria['distance'] ?? 1);
                break;

            case 'level':
                $current = (int) $userStats->level;
                $required = (int) ($criteria['level'] ?? 1);
                break;

            case 'milestone':
                // Handle milestone badges based on their specific criteria
                if (isset($criteria['total_distance_km'])) {
                    $current = (float) $userStats->total_distance_km;
                    $required = (float) $criteria['total_distance_km'];
                } elseif (isset($criteria['total_points'])) {
                    $current = (int) $userStats->total_points;
                    $required = (int) $criteria['total_points'];
                } else {
                    $current = 0;
                    $required = 1;
                }
                break;

            case 'consistency':
                // Handle consistency badges (streaks)
                if (isset($criteria['current_streak'])) {
                    $current = (int) $userStats->current_streak;
                    $required = (int) $criteria['current_streak'];
                } elseif (isset($criteria['longest_streak'])) {
                    $current = (int) $userStats->longest_streak;
                    $required = (int) $criteria['longest_streak'];
                } else {
                    $current = 0;
                    $required = 1;
                }
                break;

            default:
                $current = 0;
                $required = 1;
        }

        $percentage = $required > 0 ? min(100, ($current / $required) * 100) : 0;

        return [
            'current' => $current,
            'required' => $required,
            'percentage' => round($percentage, 2),
        ];
    }

    /**
     * Add points to user stats.
     */
    private function addPointsToUser(User $user, int $points): void
    {
        $userStats = $user->stats;

        if ($userStats) {
            $userStats->total_points += $points;
            $userStats->addExperience($points);
            $userStats->save();
        }
    }
}
