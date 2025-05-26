# Sprint 8 - Système de badges et gamification

## Analyse du système existant

### Architecture actuelle

Le système utilise Laravel avec une architecture multi-tenant. Les éléments clés identifiés :

-   **Modèles principaux** : `User`, `Task`, `Reward`, `Milestone`
-   **Statuts de tâches** : `pending`, `in_progress`, `completed`
-   **Priorités** : `low`, `medium`, `high`
-   **Rôles utilisateurs** : `admin`, `user` (coursiers)
-   **Système d'événements** : `TaskCreated`, `TaskUpdated`, `TaskDeleted`
-   **Système de cache** : Redis pour les performances
-   **Notifications** : FCM pour le mobile

### État actuel du modèle Reward

Le modèle `Reward` existe déjà mais semble être conçu pour des récompenses simples :

```php
// Structure actuelle
- name (string)
- description (string)
- points (integer)
- user_id (foreign key)
```

## Plan d'implémentation du système de badges

### 1. Architecture de la base de données

#### 1.1 Nouvelle table `badges`

```sql
CREATE TABLE badges (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    icon VARCHAR(255), -- Nom du fichier icône
    category ENUM('task_completion', 'speed', 'consistency', 'milestone', 'special') NOT NULL,
    criteria JSON NOT NULL, -- Critères de déblocage
    points INTEGER DEFAULT 0,
    rarity ENUM('bronze', 'silver', 'gold', 'platinum') DEFAULT 'bronze',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### 1.2 Table pivot `user_badges`

```sql
CREATE TABLE user_badges (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    badge_id BIGINT NOT NULL,
    earned_at TIMESTAMP NOT NULL,
    progress JSON, -- Progression vers le badge suivant
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_badge (user_id, badge_id)
);
```

#### 1.3 Table `user_stats`

```sql
CREATE TABLE user_stats (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL UNIQUE,
    total_tasks_completed INTEGER DEFAULT 0,
    total_points INTEGER DEFAULT 0,
    avg_completion_time DECIMAL(8,2), -- En heures
    current_streak INTEGER DEFAULT 0, -- Jours consécutifs
    best_streak INTEGER DEFAULT 0,
    tasks_completed_today INTEGER DEFAULT 0,
    last_task_date DATE,
    level INTEGER DEFAULT 1,
    experience_points INTEGER DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 2. Modèles Eloquent

#### 2.1 Modèle Badge

```php
// app/Models/Badge.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    protected $fillable = [
        'name', 'description', 'icon', 'category',
        'criteria', 'points', 'rarity', 'is_active'
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
                    ->withPivot('earned_at', 'progress')
                    ->withTimestamps();
    }
}
```

#### 2.2 Modèle UserBadge

```php
// app/Models/UserBadge.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    protected $fillable = ['user_id', 'badge_id', 'earned_at', 'progress'];

    protected $casts = [
        'earned_at' => 'datetime',
        'progress' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }
}
```

#### 2.3 Modèle UserStats

```php
// app/Models/UserStats.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStats extends Model
{
    protected $fillable = [
        'user_id', 'total_tasks_completed', 'total_points',
        'avg_completion_time', 'current_streak', 'best_streak',
        'tasks_completed_today', 'last_task_date', 'level',
        'experience_points'
    ];

    protected $casts = [
        'last_task_date' => 'date',
        'avg_completion_time' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

#### 2.4 Mise à jour du modèle User

```php
// Ajouter ces relations au modèle User existant
public function badges(): BelongsToMany
{
    return $this->belongsToMany(Badge::class, 'user_badges')
                ->withPivot('earned_at', 'progress')
                ->withTimestamps();
}

public function stats(): HasOne
{
    return $this->hasOne(UserStats::class);
}

public function earnedBadges(): HasMany
{
    return $this->hasMany(UserBadge::class);
}
```

### 3. Service de gamification

#### 3.1 Service BadgeService

```php
// app/Services/BadgeService.php
<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserStats;
use App\Events\BadgeEarned;
use Illuminate\Support\Facades\Cache;

class BadgeService
{
    /**
     * Vérifier et attribuer les badges après completion d'une tâche
     */
    public function checkAndAwardBadges(User $user, $taskData = null): array
    {
        $earnedBadges = [];
        $badges = Cache::remember('active_badges', 3600, function () {
            return Badge::where('is_active', true)->get();
        });

        foreach ($badges as $badge) {
            if (!$user->badges()->where('badge_id', $badge->id)->exists()) {
                if ($this->checkBadgeCriteria($user, $badge, $taskData)) {
                    $this->awardBadge($user, $badge);
                    $earnedBadges[] = $badge;
                }
            }
        }

        return $earnedBadges;
    }

    /**
     * Vérifier si un utilisateur mérite un badge
     */
    private function checkBadgeCriteria(User $user, Badge $badge, $taskData = null): bool
    {
        $criteria = $badge->criteria;
        $stats = $user->stats ?: new UserStats(['user_id' => $user->id]);

        switch ($badge->category) {
            case 'task_completion':
                return $this->checkTaskCompletionCriteria($stats, $criteria);

            case 'speed':
                return $this->checkSpeedCriteria($user, $criteria, $taskData);

            case 'consistency':
                return $this->checkConsistencyCriteria($stats, $criteria);

            case 'milestone':
                return $this->checkMilestoneCriteria($user, $criteria);

            default:
                return false;
        }
    }

    private function checkTaskCompletionCriteria(UserStats $stats, array $criteria): bool
    {
        if (isset($criteria['min_tasks'])) {
            return $stats->total_tasks_completed >= $criteria['min_tasks'];
        }
        return false;
    }

    private function checkSpeedCriteria(User $user, array $criteria, $taskData): bool
    {
        if (!$taskData || !isset($criteria['max_completion_time'])) {
            return false;
        }

        $completionTime = $taskData['completion_time_hours'] ?? null;
        return $completionTime && $completionTime <= $criteria['max_completion_time'];
    }

    private function checkConsistencyCriteria(UserStats $stats, array $criteria): bool
    {
        if (isset($criteria['min_streak'])) {
            return $stats->current_streak >= $criteria['min_streak'];
        }
        return false;
    }

    private function checkMilestoneCriteria(User $user, array $criteria): bool
    {
        if (isset($criteria['specific_milestone_id'])) {
            return $user->tasks()
                       ->where('milestone_id', $criteria['specific_milestone_id'])
                       ->where('status', 'completed')
                       ->exists();
        }
        return false;
    }

    /**
     * Attribuer un badge à un utilisateur
     */
    private function awardBadge(User $user, Badge $badge): void
    {
        UserBadge::create([
            'user_id' => $user->id,
            'badge_id' => $badge->id,
            'earned_at' => now(),
        ]);

        // Mettre à jour les points totaux
        $user->stats()->updateOrCreate(
            ['user_id' => $user->id],
            ['total_points' => $user->stats->total_points + $badge->points]
        );

        // Déclencher l'événement
        event(new BadgeEarned($user, $badge));
    }

    /**
     * Calculer la progression vers les prochains badges
     */
    public function getBadgeProgress(User $user): array
    {
        $progress = [];
        $stats = $user->stats ?: new UserStats();

        $availableBadges = Badge::where('is_active', true)
            ->whereNotIn('id', $user->badges()->pluck('badge_id'))
            ->get();

        foreach ($availableBadges as $badge) {
            $progress[] = [
                'badge' => $badge,
                'progress_percentage' => $this->calculateProgressPercentage($stats, $badge),
                'current_value' => $this->getCurrentValue($stats, $badge),
                'target_value' => $this->getTargetValue($badge),
            ];
        }

        return collect($progress)->sortByDesc('progress_percentage')->take(3)->values()->all();
    }

    private function calculateProgressPercentage(UserStats $stats, Badge $badge): int
    {
        $current = $this->getCurrentValue($stats, $badge);
        $target = $this->getTargetValue($badge);

        if ($target <= 0) return 0;

        return min(100, round(($current / $target) * 100));
    }

    private function getCurrentValue(UserStats $stats, Badge $badge): int
    {
        $criteria = $badge->criteria;

        switch ($badge->category) {
            case 'task_completion':
                return $stats->total_tasks_completed ?? 0;
            case 'consistency':
                return $stats->current_streak ?? 0;
            default:
                return 0;
        }
    }

    private function getTargetValue(Badge $badge): int
    {
        $criteria = $badge->criteria;

        return $criteria['min_tasks'] ??
               $criteria['min_streak'] ??
               $criteria['max_completion_time'] ?? 1;
    }
}
```

#### 3.2 Service UserStatsService

```php
// app/Services/UserStatsService.php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserStats;
use App\Models\Task;
use Carbon\Carbon;

class UserStatsService
{
    /**
     * Mettre à jour les statistiques après completion d'une tâche
     */
    public function updateStatsAfterTaskCompletion(User $user, Task $task): array
    {
        $stats = $user->stats ?: new UserStats(['user_id' => $user->id]);

        // Calculer le temps de completion
        $completionTime = $this->calculateCompletionTime($task);

        // Mettre à jour les statistiques
        $stats->total_tasks_completed++;
        $stats->avg_completion_time = $this->updateAverageCompletionTime($stats, $completionTime);
        $stats->experience_points += $this->calculateExperiencePoints($task);

        // Gérer les streaks
        $this->updateStreak($stats);

        // Calculer le niveau
        $stats->level = $this->calculateLevel($stats->experience_points);

        $stats->save();

        return [
            'completion_time_hours' => $completionTime,
            'experience_gained' => $this->calculateExperiencePoints($task),
            'level_up' => $this->checkLevelUp($stats),
        ];
    }

    private function calculateCompletionTime(Task $task): float
    {
        if (!$task->completed_at || !$task->created_at) {
            return 0;
        }

        return $task->created_at->diffInHours($task->completed_at, true);
    }

    private function updateAverageCompletionTime(UserStats $stats, float $newTime): float
    {
        $currentTotal = ($stats->avg_completion_time ?? 0) * ($stats->total_tasks_completed - 1);
        return ($currentTotal + $newTime) / $stats->total_tasks_completed;
    }

    private function calculateExperiencePoints(Task $task): int
    {
        $basePoints = 10;

        // Bonus selon la priorité
        $priorityBonus = match($task->priority) {
            'high' => 15,
            'medium' => 10,
            'low' => 5,
            default => 10
        };

        // Bonus de rapidité (si terminé avant la date limite)
        $speedBonus = 0;
        if ($task->due_date && $task->completed_at < $task->due_date) {
            $speedBonus = 5;
        }

        return $basePoints + $priorityBonus + $speedBonus;
    }

    private function updateStreak(UserStats $stats): void
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        if ($stats->last_task_date) {
            if ($stats->last_task_date->isSameDay($yesterday)) {
                $stats->current_streak++;
            } elseif (!$stats->last_task_date->isSameDay($today)) {
                $stats->current_streak = 1;
            }
        } else {
            $stats->current_streak = 1;
        }

        $stats->best_streak = max($stats->best_streak, $stats->current_streak);
        $stats->last_task_date = $today;

        // Reset daily counter if new day
        if (!$stats->last_task_date || !$stats->last_task_date->isSameDay($today)) {
            $stats->tasks_completed_today = 1;
        } else {
            $stats->tasks_completed_today++;
        }
    }

    private function calculateLevel(int $experiencePoints): int
    {
        // Formule: niveau = floor(sqrt(XP / 100)) + 1
        return floor(sqrt($experiencePoints / 100)) + 1;
    }

    private function checkLevelUp(UserStats $stats): bool
    {
        $previousLevel = $this->calculateLevel($stats->experience_points - $this->calculateExperiencePoints(new Task()));
        return $stats->level > $previousLevel;
    }
}
```

### 4. Événements et Listeners

#### 4.1 Événement BadgeEarned

```php
// app/Events/BadgeEarned.php
<?php

namespace App\Events;

use App\Models\User;
use App\Models\Badge;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BadgeEarned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Badge $badge,
        public string $tenantId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('badges.' . $this->tenantId),
            new Channel('badges.' . $this->tenantId . '.' . $this->user->username),
        ];
    }

    public function broadcastAs(): string
    {
        return 'badge.earned';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'badge' => [
                'id' => $this->badge->id,
                'name' => $this->badge->name,
                'description' => $this->badge->description,
                'icon' => $this->badge->icon,
                'rarity' => $this->badge->rarity,
                'points' => $this->badge->points,
            ],
            'message' => "Félicitations ! Vous avez obtenu le badge '{$this->badge->name}'"
        ];
    }
}
```

#### 4.2 Listener TaskCompletedListener

```php
// app/Listeners/TaskCompletedListener.php
<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use App\Services\BadgeService;
use App\Services\UserStatsService;
use App\Jobs\SendFcmNotification;
use App\Events\BadgeEarned;

class TaskCompletedListener
{
    public function __construct(
        private BadgeService $badgeService,
        private UserStatsService $statsService
    ) {}

    public function handle(TaskUpdated $event): void
    {
        $task = $event->task;

        // Vérifier si la tâche vient d'être complétée
        if ($task->status === 'completed' && $task->completed_at) {
            $user = $task->user;

            // Mettre à jour les statistiques
            $taskData = $this->statsService->updateStatsAfterTaskCompletion($user, $task);

            // Vérifier et attribuer les badges
            $earnedBadges = $this->badgeService->checkAndAwardBadges($user, $taskData);

            // Envoyer des notifications pour les nouveaux badges
            foreach ($earnedBadges as $badge) {
                SendFcmNotification::dispatch(
                    $user->id,
                    'Nouveau badge obtenu !',
                    "Félicitations ! Vous avez obtenu le badge '{$badge->name}'"
                );
            }
        }
    }
}
```

### 5. Contrôleurs API

#### 5.1 BadgeController

```php
// app/Http/Controllers/Api/BadgeController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Services\BadgeService;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function __construct(private BadgeService $badgeService) {}

    /**
     * Liste des badges de l'utilisateur connecté
     */
    public function index(Request $request)
    {
        $user = $request->user('api');

        $earnedBadges = $user->badges()
            ->withPivot('earned_at')
            ->orderBy('pivot_earned_at', 'desc')
            ->get();

        $progress = $this->badgeService->getBadgeProgress($user);

        return response()->json([
            'earned_badges' => $earnedBadges,
            'badge_progress' => $progress,
            'total_points' => $user->stats->total_points ?? 0,
            'level' => $user->stats->level ?? 1,
        ]);
    }

    /**
     * Tous les badges disponibles
     */
    public function available()
    {
        $badges = Badge::where('is_active', true)
            ->orderBy('rarity')
            ->orderBy('points')
            ->get();

        return response()->json(['badges' => $badges]);
    }

    /**
     * Statistiques détaillées de l'utilisateur
     */
    public function stats(Request $request)
    {
        $user = $request->user('api');
        $stats = $user->stats;

        if (!$stats) {
            return response()->json([
                'level' => 1,
                'experience_points' => 0,
                'total_points' => 0,
                'total_tasks_completed' => 0,
                'current_streak' => 0,
                'best_streak' => 0,
            ]);
        }

        return response()->json($stats);
    }
}
```

#### 5.2 AdminBadgeController

```php
// app/Http/Controllers/Api/AdminBadgeController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminBadgeController extends Controller
{
    /**
     * Créer un nouveau badge
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:255',
            'category' => 'required|in:task_completion,speed,consistency,milestone,special',
            'criteria' => 'required|array',
            'points' => 'integer|min:0',
            'rarity' => 'required|in:bronze,silver,gold,platinum',
        ]);

        $badge = Badge::create($validated);

        return response()->json(['badge' => $badge], 201);
    }

    /**
     * Mettre à jour un badge
     */
    public function update(Request $request, Badge $badge)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'icon' => 'nullable|string|max:255',
            'category' => 'sometimes|in:task_completion,speed,consistency,milestone,special',
            'criteria' => 'sometimes|array',
            'points' => 'sometimes|integer|min:0',
            'rarity' => 'sometimes|in:bronze,silver,gold,platinum',
            'is_active' => 'sometimes|boolean',
        ]);

        $badge->update($validated);

        return response()->json(['badge' => $badge]);
    }

    /**
     * Liste tous les badges (admin)
     */
    public function index()
    {
        $badges = Badge::withCount('users')->get();
        return response()->json(['badges' => $badges]);
    }

    /**
     * Statistiques des badges
     */
    public function statistics()
    {
        $totalBadges = Badge::count();
        $activeBadges = Badge::where('is_active', true)->count();
        $totalBadgesEarned = \DB::table('user_badges')->count();

        $badgesByCategory = Badge::selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->get();

        $topEarnedBadges = Badge::withCount('users')
            ->orderBy('users_count', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'total_badges' => $totalBadges,
            'active_badges' => $activeBadges,
            'total_badges_earned' => $totalBadgesEarned,
            'badges_by_category' => $badgesByCategory,
            'top_earned_badges' => $topEarnedBadges,
        ]);
    }
}
```

### 6. Resources API

#### 6.1 BadgeResource

```php
// app/Http/Resources/BadgeResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BadgeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'category' => $this->category,
            'rarity' => $this->rarity,
            'points' => $this->points,
            'earned_at' => $this->whenPivotLoaded('user_badges', function () {
                return $this->pivot->earned_at;
            }),
            'progress' => $this->whenPivotLoaded('user_badges', function () {
                return $this->pivot->progress;
            }),
        ];
    }
}
```

#### 6.2 UserStatsResource

```php
// app/Http/Resources/UserStatsResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserStatsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'level' => $this->level ?? 1,
            'experience_points' => $this->experience_points ?? 0,
            'total_points' => $this->total_points ?? 0,
            'total_tasks_completed' => $this->total_tasks_completed ?? 0,
            'avg_completion_time' => $this->avg_completion_time ?? 0,
            'current_streak' => $this->current_streak ?? 0,
            'best_streak' => $this->best_streak ?? 0,
            'tasks_completed_today' => $this->tasks_completed_today ?? 0,
            'experience_to_next_level' => $this->experienceToNextLevel(),
        ];
    }

    private function experienceToNextLevel(): int
    {
        $currentLevel = $this->level ?? 1;
        $nextLevelRequirement = pow($currentLevel, 2) * 100;
        return $nextLevelRequirement - ($this->experience_points ?? 0);
    }
}
```

### 7. Routes API

```php
// Ajouter dans routes/api.php

// Badge routes (utilisateurs)
Route::middleware(['api.auth'])
    ->group(function () {
        Route::get('/badges', [BadgeController::class, 'index']);
        Route::get('/badges/available', [BadgeController::class, 'available']);
        Route::get('/badges/stats', [BadgeController::class, 'stats']);
    });

// Admin badge routes
Route::middleware(['api.auth', 'api.admin.only'])
    ->group(function () {
        Route::apiResource('admin/badges', AdminBadgeController::class);
        Route::get('/admin/badges/statistics', [AdminBadgeController::class, 'statistics']);
    });
```

### 8. Seeders pour les badges par défaut

#### 8.1 BadgeSeeder

```php
// database/seeders/BadgeSeeder.php
<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            // Badges de completion de tâches
            [
                'name' => 'Premier pas',
                'description' => 'Complétez votre première tâche',
                'icon' => 'first_step.png',
                'category' => 'task_completion',
                'criteria' => ['min_tasks' => 1],
                'points' => 10,
                'rarity' => 'bronze',
            ],
            [
                'name' => 'Travailleur dévoué',
                'description' => 'Complétez 10 tâches',
                'icon' => 'dedicated_worker.png',
                'category' => 'task_completion',
                'criteria' => ['min_tasks' => 10],
                'points' => 50,
                'rarity' => 'bronze',
            ],
            [
                'name' => 'Expert coursier',
                'description' => 'Complétez 50 tâches',
                'icon' => 'expert_courier.png',
                'category' => 'task_completion',
                'criteria' => ['min_tasks' => 50],
                'points' => 200,
                'rarity' => 'silver',
            ],
            [
                'name' => 'Maître coursier',
                'description' => 'Complétez 100 tâches',
                'icon' => 'master_courier.png',
                'category' => 'task_completion',
                'criteria' => ['min_tasks' => 100],
                'points' => 500,
                'rarity' => 'gold',
            ],

            // Badges de rapidité
            [
                'name' => 'Rapide comme l\'éclair',
                'description' => 'Complétez une tâche en moins de 2 heures',
                'icon' => 'lightning_fast.png',
                'category' => 'speed',
                'criteria' => ['max_completion_time' => 2],
                'points' => 25,
                'rarity' => 'silver',
            ],
            [
                'name' => 'Vitesse de la lumière',
                'description' => 'Complétez une tâche en moins d\'1 heure',
                'icon' => 'light_speed.png',
                'category' => 'speed',
                'criteria' => ['max_completion_time' => 1],
                'points' => 50,
                'rarity' => 'gold',
            ],

            // Badges de consistance
            [
                'name' => 'Régularité',
                'description' => 'Complétez des tâches 3 jours consécutifs',
                'icon' => 'consistency.png',
                'category' => 'consistency',
                'criteria' => ['min_streak' => 3],
                'points' => 30,
                'rarity' => 'bronze',
            ],
            [
                'name' => 'Semaine parfaite',
                'description' => 'Complétez des tâches 7 jours consécutifs',
                'icon' => 'perfect_week.png',
                'category' => 'consistency',
                'criteria' => ['min_streak' => 7],
                'points' => 100,
                'rarity' => 'silver',
            ],
            [
                'name' => 'Machine infatigable',
                'description' => 'Complétez des tâches 14 jours consécutifs',
                'icon' => 'tireless_machine.png',
                'category' => 'consistency',
                'criteria' => ['min_streak' => 14],
                'points' => 250,
                'rarity' => 'gold',
            ],
            [
                'name' => 'Légende',
                'description' => 'Complétez des tâches 30 jours consécutifs',
                'icon' => 'legend.png',
                'category' => 'consistency',
                'criteria' => ['min_streak' => 30],
                'points' => 1000,
                'rarity' => 'platinum',
            ],
        ];

        foreach ($badges as $badgeData) {
            Badge::create($badgeData);
        }
    }
}
```

### 9. Interface Web d'administration

#### 9.1 Contrôleur Web AdminBadgeController

```php
// app/Http/Controllers/Web/AdminBadgeController.php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Http\Request;

class AdminBadgeController extends Controller
{
    public function index()
    {
        $badges = Badge::withCount('users')->get();
        return view('admin.badges.index', compact('badges'));
    }

    public function create()
    {
        return view('admin.badges.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:task_completion,speed,consistency,milestone,special',
            'criteria' => 'required|array',
            'points' => 'integer|min:0',
            'rarity' => 'required|in:bronze,silver,gold,platinum',
        ]);

        Badge::create($validated);
        return redirect()->route('admin.badges.index')
                        ->with('success', 'Badge créé avec succès');
    }

    public function statistics()
    {
        $stats = [
            'total_badges' => Badge::count(),
            'active_badges' => Badge::where('is_active', true)->count(),
            'total_earned' => \DB::table('user_badges')->count(),
            'top_users' => User::withCount('badges')
                              ->orderBy('badges_count', 'desc')
                              ->limit(10)
                              ->get(),
        ];

        return view('admin.badges.statistics', compact('stats'));
    }
}
```

### 10. Tests

#### 10.1 Test du BadgeService

```php
// tests/Unit/BadgeServiceTest.php
<?php

namespace Tests\Unit;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserStats;
use App\Services\BadgeService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BadgeServiceTest extends TestCase
{
    use RefreshDatabase;

    private BadgeService $badgeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->badgeService = new BadgeService();
    }

    public function test_awards_first_task_badge()
    {
        $user = User::factory()->create();
        $badge = Badge::create([
            'name' => 'Premier pas',
            'category' => 'task_completion',
            'criteria' => ['min_tasks' => 1],
            'points' => 10,
            'rarity' => 'bronze',
            'is_active' => true,
        ]);

        UserStats::create([
            'user_id' => $user->id,
            'total_tasks_completed' => 1,
        ]);

        $earnedBadges = $this->badgeService->checkAndAwardBadges($user);

        $this->assertCount(1, $earnedBadges);
        $this->assertTrue($user->badges()->where('badge_id', $badge->id)->exists());
    }

    public function test_calculates_badge_progress()
    {
        $user = User::factory()->create();
        UserStats::create([
            'user_id' => $user->id,
            'total_tasks_completed' => 5,
        ]);

        Badge::create([
            'name' => 'Travailleur dévoué',
            'category' => 'task_completion',
            'criteria' => ['min_tasks' => 10],
            'points' => 50,
            'rarity' => 'bronze',
            'is_active' => true,
        ]);

        $progress = $this->badgeService->getBadgeProgress($user);

        $this->assertCount(1, $progress);
        $this->assertEquals(50, $progress[0]['progress_percentage']);
    }
}
```

### 11. Étapes de déploiement

#### Phase 1: Infrastructure (Semaine 1)

1. **Créer les migrations**

    ```bash
    php artisan make:migration create_badges_table
    php artisan make:migration create_user_badges_table
    php artisan make:migration create_user_stats_table
    ```

2. **Créer les modèles**

    ```bash
    php artisan make:model Badge
    php artisan make:model UserBadge
    php artisan make:model UserStats
    ```

3. **Créer les services**
    ```bash
    php artisan make:service BadgeService
    php artisan make:service UserStatsService
    ```

#### Phase 2: Logique métier (Semaine 1-2)

4. **Implémenter les événements**

    ```bash
    php artisan make:event BadgeEarned
    php artisan make:listener TaskCompletedListener
    ```

5. **Créer les contrôleurs API**

    ```bash
    php artisan make:controller Api/BadgeController
    php artisan make:controller Api/AdminBadgeController
    ```

6. **Implémenter les resources**
    ```bash
    php artisan make:resource BadgeResource
    php artisan make:resource UserStatsResource
    ```

#### Phase 3: Tests et interface (Semaine 2)

7. **Créer les tests**

    ```bash
    php artisan make:test BadgeServiceTest --unit
    php artisan make:test BadgeControllerTest
    ```

8. **Implémenter l'interface web admin**

    ```bash
    php artisan make:controller Web/AdminBadgeController
    ```

9. **Seeder pour badges par défaut**
    ```bash
    php artisan make:seeder BadgeSeeder
    ```

### 12. Points d'intégration avec l'app mobile

#### 12.1 Endpoints API essentiels

-   `GET /api/badges` - Liste des badges de l'utilisateur
-   `GET /api/badges/available` - Tous les badges disponibles
-   `GET /api/badges/stats` - Statistiques détaillées
-   `WebSocket` - Notifications en temps réel pour nouveaux badges

#### 12.2 Structure JSON pour le mobile

```json
{
  "earned_badges": [
    {
      "id": 1,
      "name": "Premier pas",
      "description": "Complétez votre première tâche",
      "icon": "first_step.png",
      "rarity": "bronze",
      "points": 10,
      "earned_at": "2025-05-24T10:30:00Z"
    }
  ],
  "badge_progress": [
    {
      "badge": {...},
      "progress_percentage": 50,
      "current_value": 5,
      "target_value": 10
    }
  ],
  "user_stats": {
    "level": 3,
    "experience_points": 450,
    "total_points": 180,
    "current_streak": 5,
    "experience_to_next_level": 450
  }
}
```

### 13. Considérations de performance

1. **Cache Redis**

    - Cache des badges actifs (TTL: 1h)
    - Cache des statistiques utilisateur (TTL: 30min)
    - Invalidation intelligente lors des updates

2. **Optimisations base de données**

    - Index sur `user_badges.user_id` et `user_badges.badge_id`
    - Index composé sur `badges.category` et `badges.is_active`
    - Requêtes optimisées avec `eager loading`

3. **Jobs en arrière-plan**
    - Traitement des badges via des jobs pour éviter de ralentir les API
    - Notifications FCM en mode asynchrone

### 14. Évolutions futures possibles

1. **Badges temporaires/saisonniers**
2. **Système de leaderboard**
3. **Badges collaboratifs (équipe)**
4. **Récompenses physiques liées aux badges**
5. **Analytics avancés des badges**

Ce système de badges offre une base solide pour la gamification tout en restant extensible et performant.
