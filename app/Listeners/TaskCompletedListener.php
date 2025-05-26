<?php

namespace App\Listeners;

use App\Events\TaskCompleted;
use App\Services\BadgeService;
use App\Services\UserStatsService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class TaskCompletedListener
{
    use InteractsWithQueue;

    protected BadgeService $badgeService;
    protected UserStatsService $userStatsService;

    /**
     * Create the event listener.
     */
    public function __construct(BadgeService $badgeService, UserStatsService $userStatsService)
    {
        $this->badgeService = $badgeService;
        $this->userStatsService = $userStatsService;
    }

    /**
     * Handle the event.
     */
    public function handle(TaskCompleted $event): void
    {
        Log::info('TaskCompletedListener::handle called', [
            'task_id' => $event->task->id,
            'user_id' => $event->task->user_id ?? 'null',
        ]);

        try {
            $task = $event->task;
            $user = $task->user;

            if (!$user) {
                Log::warning('Task completed but no assigned user found', [
                    'task_id' => $task->id
                ]);
                return;
            }

            Log::info('Starting user stats update', [
                'user_id' => $user->id,
                'task_id' => $task->id,
            ]);

            // Update user statistics
            $userStats = $this->userStatsService->updateStatsOnTaskCompletion($user, $task);

            Log::info('User stats updated after task completion', [
                'user_id' => $user->id,
                'task_id' => $task->id,
                'total_tasks' => $userStats->total_tasks_completed,
                'total_points' => $userStats->total_points,
                'current_level' => $userStats->level,
            ]);

            // Check and award badges
            $earnedBadges = $this->badgeService->checkAndAwardBadges($user);

            if ($earnedBadges->isNotEmpty()) {
                Log::info('Badges earned after task completion', [
                    'user_id' => $user->id,
                    'task_id' => $task->id,
                    'badges_earned' => $earnedBadges->count(),
                    'badge_names' => $earnedBadges->map(fn($userBadge) => $userBadge->badge->name)->toArray(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error handling task completion for badge system', [
                'task_id' => $event->task->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Optionally re-throw to trigger job retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(TaskCompleted $event, \Throwable $exception): void
    {
        Log::error('TaskCompletedListener job failed', [
            'task_id' => $event->task->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
