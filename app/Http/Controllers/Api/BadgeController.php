<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BadgeResource;
use App\Http\Resources\UserStatsResource;
use App\Models\Badge;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BadgeController extends Controller
{
    protected BadgeService $badgeService;

    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    /**
     * Get all badges with user's progress.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        $category = $request->get('category');
        $earned = $request->get('earned'); // 'true', 'false', or null for all

        $query = Badge::active();

        if ($category) {
            $query->where('category', $category);
        }

        $badges = $query->get();

        $badgesWithProgress = $badges->map(function ($badge) use ($user, $earned) {
            $hasEarned = $badge->hasBeenEarnedBy($user);

            // Filter by earned status if specified
            if ($earned === 'true' && !$hasEarned) {
                return null;
            }
            if ($earned === 'false' && $hasEarned) {
                return null;
            }

            $userBadge = $hasEarned ? $user->userBadges()->where('badge_id', $badge->id)->first() : null;
            $progress = !$hasEarned ? $this->badgeService->getBadgeProgress($user, $badge) : null;

            return [
                'badge' => new BadgeResource($badge),
                'earned' => $hasEarned,
                'earned_at' => $userBadge?->earned_at?->toISOString(),
                'progress' => $progress,
            ];
        })->filter(); // Remove null values

        return response()->json([
            'data' => $badgesWithProgress->values(),
            'meta' => [
                'total_badges' => $badges->count(),
                'earned_badges' => $badges->filter(fn($badge) => $badge->hasBeenEarnedBy($user))->count(),
            ]
        ]);
    }

    /**
     * Get user's earned badges.
     */
    public function earned(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        $category = $request->get('category');
        $limit = $request->get('limit', 50);

        $query = $user->userBadges()->with('badge');

        if ($category) {
            $query->whereHas('badge', function ($q) use ($category) {
                $q->where('category', $category);
            });
        }

        $userBadges = $query->latest('earned_at')->limit($limit)->get();

        return response()->json([
            'data' => $userBadges->map(function ($userBadge) {
                return [
                    'badge' => new BadgeResource($userBadge->badge),
                    'earned_at' => $userBadge->earned_at->toISOString(),
                    'progress' => $userBadge->progress,
                ];
            }),
            'meta' => [
                'total_earned' => $user->badges()->count(),
            ]
        ]);
    }

    /**
     * Get user's recent badges (last 7 days).
     */
    public function recent(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        $recentBadges = $user->userBadges()
            ->with('badge')
            ->recentlyEarned()
            ->get();

        return response()->json([
            'data' => $recentBadges->map(function ($userBadge) {
                return [
                    'badge' => new BadgeResource($userBadge->badge),
                    'earned_at' => $userBadge->earned_at->toISOString(),
                    'progress' => $userBadge->progress,
                ];
            }),
        ]);
    }

    /**
     * Get user's statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        $stats = $user->stats;

        if (!$stats) {
            return response()->json([
                'data' => null,
                'message' => 'No statistics available yet.'
            ]);
        }

        return response()->json([
            'data' => new UserStatsResource($stats),
        ]);
    }

    /**
     * Get badge categories.
     */
    public function categories(): JsonResponse
    {
        $categories = Badge::distinct()->pluck('category')->filter();

        return response()->json([
            'data' => $categories->map(function ($category) {
                return [
                    'key' => $category,
                    'name' => ucwords(str_replace('_', ' ', $category)),
                    'badge_count' => Badge::where('category', $category)->active()->count(),
                ];
            }),
        ]);
    }

    /**
     * Get leaderboard (top users by points).
     */
    public function leaderboard(Request $request): JsonResponse
    {
        $period = $request->get('period', 'all'); // all, month, week
        $limit = $request->get('limit', 10);

        $query = User::with('stats')
            ->whereHas('stats')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id');

        switch ($period) {
            case 'month':
                $query->orderBy('user_stats.monthly_points', 'desc');
                break;
            case 'week':
                $query->orderBy('user_stats.weekly_points', 'desc');
                break;
            default:
                $query->orderBy('user_stats.total_points', 'desc');
        }

        $users = $query->select('users.*')->limit($limit)->get();

        return response()->json([
            'data' => $users->map(function ($user, $index) use ($period) {
                $stats = $user->stats;
                $points = match($period) {
                    'month' => $stats->monthly_points,
                    'week' => $stats->weekly_points,
                    default => $stats->total_points,
                };

                return [
                    'rank' => $index + 1,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'points' => $points,
                    'level' => $stats->level,
                    'badges_count' => $user->badges()->count(),
                ];
            }),
            'meta' => [
                'period' => $period,
                'limit' => $limit,
            ]
        ]);
    }

    /**
     * Get badge details.
     */
    public function show(Request $request, Badge $badge): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        $hasEarned = $badge->hasBeenEarnedBy($user);
        $userBadge = $hasEarned ? $user->userBadges()->where('badge_id', $badge->id)->first() : null;
        $progress = !$hasEarned ? $this->badgeService->getBadgeProgress($user, $badge) : null;

        return response()->json([
            'data' => [
                'badge' => new BadgeResource($badge),
                'earned' => $hasEarned,
                'earned_at' => $userBadge?->earned_at?->toISOString(),
                'progress' => $progress,
                'total_earned_by' => $badge->userBadges()->count(),
            ]
        ]);
    }
}
