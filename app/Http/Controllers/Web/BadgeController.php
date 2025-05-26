<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use App\Services\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BadgeController extends Controller
{
    protected BadgeService $badgeService;

    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    /**
     * Display the badges dashboard.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $category = $request->get('category');
        $rarity = $request->get('rarity');
        $search = $request->get('search');

        // Badge statistics
        $totalBadges = Badge::count();
        $activeBadges = Badge::active()->count();
        $totalBadgesEarned = UserBadge::count();
        $uniqueUsersWithBadges = UserBadge::distinct('user_id')->count();

        // Get badges with earned count
        $badgesQuery = Badge::withCount('userBadges')
            ->with(['userBadges' => function ($query) {
                $query->latest('earned_at')->take(5)->with('user');
            }]);

        if ($category) {
            $badgesQuery->where('category', $category);
        }

        if ($rarity) {
            $badgesQuery->where('rarity', $rarity);
        }

        if ($search) {
            $badgesQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $badges = $badgesQuery->orderBy('user_badges_count', 'desc')->paginate(12);

        // Most earned badges
        $mostEarnedBadges = Badge::withCount('userBadges')
            ->orderBy('user_badges_count', 'desc')
            ->take(5)
            ->get();

        // Recent badge achievements
        $recentAchievements = UserBadge::with(['user', 'badge'])
            ->latest('earned_at')
            ->take(10)
            ->get();

        // Badge categories with counts
        $categories = Badge::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get()
            ->pluck('count', 'category');

        // Top users by badge count
        $topUsers = User::whereHas('badges')
            ->withCount('badges')
            ->orderBy('badges_count', 'desc')
            ->take(10)
            ->get();

        // Badge distribution by rarity
        $rarityDistribution = Badge::select('rarity', DB::raw('count(*) as count'))
            ->groupBy('rarity')
            ->get()
            ->pluck('count', 'rarity');

        return view('pages.badges.index', compact(
            'badges',
            'totalBadges',
            'activeBadges',
            'totalBadgesEarned',
            'uniqueUsersWithBadges',
            'mostEarnedBadges',
            'recentAchievements',
            'categories',
            'topUsers',
            'rarityDistribution',
            'category',
            'rarity',
            'search'
        ));
    }

    /**
     * Show specific badge details.
     */
    public function show($badgeId)
    {
        $badge = Badge::findOrFail($badgeId);

        $badge->load(['userBadges' => function ($query) {
            $query->with('user')->latest('earned_at');
        }]);

        // Users who earned this badge
        $usersWithBadge = $badge->userBadges()
            ->with('user')
            ->latest('earned_at')
            ->paginate(20);

        // Badge statistics
        $stats = [
            'total_earned' => $badge->userBadges()->count(),
            'unique_users' => $badge->userBadges()->distinct('user_id')->count(),
            'first_earned' => $badge->userBadges()->oldest('earned_at')->first()?->earned_at,
            'last_earned' => $badge->userBadges()->latest('earned_at')->first()?->earned_at,
        ];

        // Users who can earn this badge (showing progress)
        $usersWithProgress = User::with('stats')
            ->whereDoesntHave('userBadges', function ($query) use ($badge) {
                $query->where('badge_id', $badge->id);
            })
            ->whereHas('stats')
            ->take(10)
            ->get()
            ->map(function ($user) use ($badge) {
                $progress = $this->badgeService->getBadgeProgress($user, $badge);
                return [
                    'user' => $user,
                    'progress' => $progress,
                ];
            })
            ->sortByDesc('progress.percentage');

        return view('pages.badges.show', compact(
            'badge',
            'usersWithBadge',
            'stats',
            'usersWithProgress'
        ));
    }

    /**
     * Show user progression dashboard.
     */
    public function userProgress(Request $request)
    {
        $search = $request->get('search');
        $sortBy = $request->get('sort', 'badges_count');

        // Get users with their stats and badge counts
        $usersQuery = User::with(['stats', 'userBadges.badge'])
            ->withCount('badges');

        if ($search) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        switch ($sortBy) {
            case 'points':
                $usersQuery->join('user_stats', 'users.id', '=', 'user_stats.user_id')
                    ->orderBy('user_stats.total_points', 'desc')
                    ->select('users.*');
                break;
            case 'level':
                $usersQuery->join('user_stats', 'users.id', '=', 'user_stats.user_id')
                    ->orderBy('user_stats.level', 'desc')
                    ->select('users.*');
                break;
            case 'tasks':
                $usersQuery->join('user_stats', 'users.id', '=', 'user_stats.user_id')
                    ->orderBy('user_stats.total_tasks_completed', 'desc')
                    ->select('users.*');
                break;
            default: // badges_count
                $usersQuery->orderBy('badges_count', 'desc');
                break;
        }

        $users = $usersQuery->paginate(20);

        // Overall statistics
        $overallStats = [
            'total_users' => User::count(),
            'users_with_badges' => User::has('userBadges')->count(),
            'avg_badges_per_user' => round(UserBadge::count() / max(User::count(), 1), 2),
            'total_points' => DB::table('user_stats')->sum('total_points'),
            'avg_level' => round(DB::table('user_stats')->avg('level'), 1),
        ];

        return view('pages.badges.user-progress', compact(
            'users',
            'overallStats',
            'search',
            'sortBy'
        ));
    }

    /**
     * Show leaderboard.
     */
    public function leaderboard(Request $request)
    {
        $period = $request->get('period', 'all'); // all, month, week
        $type = $request->get('type', 'points'); // points, badges, tasks

        // Points leaderboard
        $pointsColumn = match($period) {
            'month' => 'monthly_points',
            'week' => 'weekly_points',
            default => 'total_points',
        };

        $pointsLeaderboard = User::with('stats')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy("user_stats.{$pointsColumn}", 'desc')
            ->select('users.*')
            ->take(50)
            ->get();

        // Badges leaderboard
        $badgesLeaderboard = User::with('stats')
            ->whereHas('badges')
            ->withCount('badges')
            ->orderBy('badges_count', 'desc')
            ->take(50)
            ->get();

        // Tasks leaderboard
        $tasksLeaderboard = User::with('stats')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy('user_stats.total_tasks_completed', 'desc')
            ->select('users.*')
            ->take(50)
            ->get();

        // Level leaderboard
        $levelLeaderboard = User::with('stats')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy('user_stats.level', 'desc')
            ->select('users.*')
            ->take(50)
            ->get();

        $leaderboards = [
            'points' => $pointsLeaderboard,
            'badges' => $badgesLeaderboard,
            'tasks' => $tasksLeaderboard,
            'level' => $levelLeaderboard,
        ];

        return view('pages.badges.leaderboard', compact(
            'leaderboards',
            'period',
            'type'
        ));
    }
}
