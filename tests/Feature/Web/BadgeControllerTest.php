<?php

namespace Tests\Feature\Web;

use App\Models\Badge;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserStats;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Utilities\DatabaseRefresh;

class BadgeControllerTest extends TestCase
{
    use DatabaseMigrations, DatabaseRefresh;

    private User $user;
    private User $admin;
    private Badge $badge1;
    private Badge $badge2;
    private Badge $inactiveBadge;
    private string $tenantId = 'badge-web-test';

    protected function setUp(): void
    {
        parent::setUp();

        $this->refreshTenantDatabase();

        // Create a tenant
        $tenant = Tenant::create([
            'id' => $this->tenantId,
            'name' => 'Badge Web Test Tenant',
        ]);

        tenancy()->initialize($tenant);

        // Create test users
        $this->user = User::create([
            'name' => 'Badge Test User',
            'username' => 'badge_test_user',
            'email' => 'badge@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        $this->admin = User::create([
            'name' => 'Badge Test Admin',
            'username' => 'badge_test_admin',
            'email' => 'badgeadmin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create user stats
        UserStats::create([
            'user_id' => $this->user->id,
            'total_points' => 1000,
            'weekly_points' => 200,
            'monthly_points' => 800,
            'total_tasks_completed' => 50,
            'level' => 5,
        ]);

        UserStats::create([
            'user_id' => $this->admin->id,
            'total_points' => 2000,
            'weekly_points' => 400,
            'monthly_points' => 1600,
            'total_tasks_completed' => 100,
            'level' => 8,
        ]);

        // Create test badges
        $this->badge1 = Badge::create([
            'name' => 'First Badge',
            'description' => 'Description for first badge',
            'category' => 'task_completion',
            'rarity' => 'bronze',
            'icon' => 'fas fa-star',
            'criteria' => ['type' => 'task_completion', 'value' => 10],
            'points' => 100,
            'is_active' => true,
        ]);

        $this->badge2 = Badge::create([
            'name' => 'Speed Badge',
            'description' => 'Description for speed badge',
            'category' => 'speed',
            'rarity' => 'silver',
            'icon' => 'fas fa-rocket',
            'criteria' => ['type' => 'speed', 'value' => 5],
            'points' => 200,
            'is_active' => true,
        ]);

        $this->inactiveBadge = Badge::create([
            'name' => 'Inactive Badge',
            'description' => 'This badge is inactive',
            'category' => 'special',
            'rarity' => 'platinum',
            'icon' => 'fas fa-crown',
            'criteria' => ['type' => 'special', 'value' => 1],
            'points' => 500,
            'is_active' => false,
        ]);

        // Create some user badges
        UserBadge::create([
            'user_id' => $this->user->id,
            'badge_id' => $this->badge1->id,
            'earned_at' => now()->subDays(2),
            'progress' => 100,
        ]);

        UserBadge::create([
            'user_id' => $this->admin->id,
            'badge_id' => $this->badge1->id,
            'earned_at' => now()->subDays(1),
            'progress' => 100,
        ]);

        UserBadge::create([
            'user_id' => $this->admin->id,
            'badge_id' => $this->badge2->id,
            'earned_at' => now(),
            'progress' => 100,
        ]);

        session(['tenant_id' => $this->tenantId]);
    }

    #[Test]
    public function index_displays_badges_dashboard_for_authenticated_user()
    {
        $response = $this->actingAs($this->user)->get('/badges');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.index');
        $response->assertViewHasAll([
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
        ]);
    }

    #[Test]
    public function index_displays_badges_dashboard_for_authenticated_admin()
    {
        $response = $this->actingAs($this->admin)->get('/badges');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.index');
    }

    #[Test]
    public function index_redirects_unauthenticated_users()
    {
        $response = $this->get('/badges');

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function index_filters_badges_by_category()
    {
        $response = $this->actingAs($this->user)->get('/badges?category=task_completion');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.index');
        $response->assertViewHas('category', 'task_completion');
    }

    #[Test]
    public function index_filters_badges_by_rarity()
    {
        $response = $this->actingAs($this->user)->get('/badges?rarity=bronze');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.index');
        $response->assertViewHas('rarity', 'bronze');
    }

    #[Test]
    public function index_searches_badges_by_name()
    {
        $response = $this->actingAs($this->user)->get('/badges?search=First');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.index');
        $response->assertViewHas('search', 'First');
    }

    #[Test]
    public function index_applies_multiple_filters()
    {
        $response = $this->actingAs($this->user)->get('/badges?category=task_completion&rarity=bronze&search=First');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.index');
        $response->assertViewHas('category', 'task_completion');
        $response->assertViewHas('rarity', 'bronze');
        $response->assertViewHas('search', 'First');
    }

    #[Test]
    public function show_displays_badge_details_for_authenticated_user()
    {
        $response = $this->actingAs($this->user)->get("/badges/{$this->badge1->id}");

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.show');
        $response->assertViewHasAll([
            'badge',
            'usersWithBadge',
            'stats',
            'usersWithProgress'
        ]);
    }

    #[Test]
    public function show_displays_badge_details_for_authenticated_admin()
    {
        $response = $this->actingAs($this->admin)->get("/badges/{$this->badge1->id}");

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.show');
    }

    #[Test]
    public function show_redirects_unauthenticated_users()
    {
        $response = $this->get("/badges/{$this->badge1->id}");

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function show_returns_404_for_nonexistent_badge()
    {
        $response = $this->actingAs($this->user)->get('/badges/99999');

        $response->assertStatus(404);
    }

    #[Test]
    public function user_progress_displays_user_progression_dashboard()
    {
        $response = $this->actingAs($this->admin)->get('/badges/users/progress');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.user-progress');
        $response->assertViewHasAll([
            'users',
            'overallStats',
            'search',
            'sortBy'
        ]);
    }

    #[Test]
    public function user_progress_redirects_unauthenticated_users()
    {
        $response = $this->get('/badges/users/progress');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function user_progress_searches_users_by_name()
    {
        $response = $this->actingAs($this->admin)->get('/badges/users/progress?search=Badge Test User');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.user-progress');
        $response->assertViewHas('search', 'Badge Test User');
    }

    #[Test]
    public function user_progress_searches_users_by_email()
    {
        $response = $this->actingAs($this->admin)->get('/badges/users/progress?search=badge@example.com');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.user-progress');
        $response->assertViewHas('search', 'badge@example.com');
    }

    #[Test]
    public function user_progress_searches_users_by_username()
    {
        $response = $this->actingAs($this->admin)->get('/badges/users/progress?search=badge_test_user');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.user-progress');
        $response->assertViewHas('search', 'badge_test_user');
    }

    #[Test]
    public function user_progress_sorts_by_badges_count()
    {
        $response = $this->actingAs($this->admin)->get('/badges/users/progress?sort=badges_count');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.user-progress');
        $response->assertViewHas('sortBy', 'badges_count');
    }

    #[Test]
    public function user_progress_sorts_by_points()
    {
        $response = $this->actingAs($this->admin)->get('/badges/users/progress?sort=points');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.user-progress');
        $response->assertViewHas('sortBy', 'points');
    }

    #[Test]
    public function user_progress_sorts_by_level()
    {
        $response = $this->actingAs($this->admin)->get('/badges/users/progress?sort=level');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.user-progress');
        $response->assertViewHas('sortBy', 'level');
    }

    #[Test]
    public function user_progress_sorts_by_tasks()
    {
        $response = $this->actingAs($this->admin)->get('/badges/users/progress?sort=tasks');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.user-progress');
        $response->assertViewHas('sortBy', 'tasks');
    }

    #[Test]
    public function user_progress_defaults_to_badges_count_sorting()
    {
        $response = $this->actingAs($this->admin)->get('/badges/users/progress');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.user-progress');
        $response->assertViewHas('sortBy', 'badges_count');
    }

    #[Test]
    public function leaderboard_displays_leaderboard_dashboard()
    {
        $response = $this->actingAs($this->admin)->get('/badges/users/leaderboard');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.leaderboard');
        $response->assertViewHasAll([
            'leaderboards',
            'period',
            'type'
        ]);
    }

    #[Test]
    public function leaderboard_redirects_unauthenticated_users()
    {
        $response = $this->get('/badges/users/leaderboard');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function leaderboard_filters_by_period_all()
    {
        $response = $this->actingAs($this->admin)->get('/badges/users/leaderboard?period=all');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.leaderboard');
        $response->assertViewHas('period', 'all');
    }

    #[Test]
    public function leaderboard_filters_by_period_month()
    {
        $response = $this->actingAs($this->admin)->get('/badges/users/leaderboard?period=month');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.leaderboard');
        $response->assertViewHas('period', 'month');
    }

    #[Test]
    public function leaderboard_filters_by_period_week()
    {
        $response = $this->actingAs($this->admin)->get('/badges/users/leaderboard?period=week');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.leaderboard');
        $response->assertViewHas('period', 'week');
    }

    #[Test]
    public function leaderboard_filters_by_type_points()
    {
        $response = $this->actingAs($this->admin)->get('/badges/users/leaderboard?type=points');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.leaderboard');
        $response->assertViewHas('type', 'points');
    }

    #[Test]
    public function leaderboard_filters_by_type_badges()
    {
        $response = $this->actingAs($this->admin)->get('/badges/users/leaderboard?type=badges');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.leaderboard');
        $response->assertViewHas('type', 'badges');
    }

    #[Test]
    public function leaderboard_filters_by_type_tasks()
    {
        $response = $this->actingAs($this->admin)->get('/badges/users/leaderboard?type=tasks');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.leaderboard');
        $response->assertViewHas('type', 'tasks');
    }

    #[Test]
    public function leaderboard_defaults_to_all_period_and_points_type()
    {
        $response = $this->actingAs($this->admin)->get('/badges/users/leaderboard');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.leaderboard');
        $response->assertViewHas('period', 'all');
        $response->assertViewHas('type', 'points');
    }

    #[Test]
    public function leaderboard_combines_period_and_type_filters()
    {
        $response = $this->actingAs($this->admin)->get('/badges/users/leaderboard?period=month&type=badges');

        $response->assertStatus(200);
        $response->assertViewIs('pages.badges.leaderboard');
        $response->assertViewHas('period', 'month');
        $response->assertViewHas('type', 'badges');
    }

    // #[Test]
    // public function index_contains_expected_statistics()
    // {
    //     $response = $this->actingAs($this->admin)->get('/badges');

    //     $response->assertStatus(200);

    //     // Check that view data contains expected structure
    //     $viewData = $response->viewData();

    //     $this->assertGreaterThan(0, $viewData['totalBadges']);
    //     $this->assertGreaterThan(0, $viewData['activeBadges']);
    //     $this->assertGreaterThan(0, $viewData['totalBadgesEarned']);
    //     $this->assertGreaterThan(0, $viewData['uniqueUsersWithBadges']);
    //     $this->assertNotNull($viewData['mostEarnedBadges']);
    //     $this->assertNotNull($viewData['recentAchievements']);
    //     $this->assertNotNull($viewData['categories']);
    //     $this->assertNotNull($viewData['topUsers']);
    //     $this->assertNotNull($viewData['rarityDistribution']);
    // }

    // #[Test]
    // public function show_contains_expected_badge_statistics()
    // {
    //     $response = $this->actingAs($this->admin)->get("/badges/{$this->badge1->id}");

    //     $response->assertStatus(200);

    //     // Check that view data contains expected structure
    //     $viewData = $response->viewData();

    //     $this->assertEquals($this->badge1->id, $viewData['badge']->id);
    //     $this->assertNotNull($viewData['usersWithBadge']);
    //     $this->assertNotNull($viewData['stats']);
    //     $this->assertArrayHasKey('total_earned', $viewData['stats']);
    //     $this->assertArrayHasKey('unique_users', $viewData['stats']);
    //     $this->assertNotNull($viewData['usersWithProgress']);
    // }

    // #[Test]
    // public function user_progress_contains_expected_statistics()
    // {
    //     $response = $this->actingAs($this->admin)->get('/badges/users/progress');

    //     $response->assertStatus(200);

    //     // Check that view data contains expected structure
    //     $viewData = $response->viewData();

    //     $this->assertNotNull($viewData['users']);
    //     $this->assertNotNull($viewData['overallStats']);
    //     $this->assertArrayHasKey('total_users', $viewData['overallStats']);
    //     $this->assertArrayHasKey('users_with_badges', $viewData['overallStats']);
    //     $this->assertArrayHasKey('avg_badges_per_user', $viewData['overallStats']);
    //     $this->assertArrayHasKey('total_points', $viewData['overallStats']);
    //     $this->assertArrayHasKey('avg_level', $viewData['overallStats']);
    // }

    // #[Test]
    // public function leaderboard_contains_all_leaderboard_types()
    // {
    //     $response = $this->actingAs($this->admin)->get('/badges/users/leaderboard');

    //     $response->assertStatus(200);

    //     // Check that view data contains expected structure
    //     $viewData = $response->viewData();

    //     $this->assertNotNull($viewData['leaderboards']);
    //     $this->assertArrayHasKey('points', $viewData['leaderboards']);
    //     $this->assertArrayHasKey('badges', $viewData['leaderboards']);
    //     $this->assertArrayHasKey('tasks', $viewData['leaderboards']);
    //     $this->assertArrayHasKey('level', $viewData['leaderboards']);
    // }
}
