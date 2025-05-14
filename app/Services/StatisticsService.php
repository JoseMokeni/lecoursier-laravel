<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Milestone;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    protected $startDate = null;
    protected $endDate = null;

    /**
     * Set date range for filtering statistics
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return $this
     */
    public function setDateRange($startDate = null, $endDate = null)
    {
        if ($startDate) {
            $this->startDate = Carbon::parse($startDate)->startOfDay();
        }

        if ($endDate) {
            $this->endDate = Carbon::parse($endDate)->endOfDay();
        }

        return $this;
    }

    /**
     * Apply date filters to query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $dateField
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyDateFilters($query, $dateField = 'created_at')
    {
        if ($this->startDate) {
            $query->where($dateField, '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->where($dateField, '<=', $this->endDate);
        }

        return $query;
    }

    /**
     * Obtenir les statistiques globales des tâches
     *
     * @return array
     */
    public function getTaskStats()
    {
        $baseQuery = Task::query();
        $this->applyDateFilters($baseQuery);

        $pendingQuery = clone $baseQuery;
        $inProgressQuery = clone $baseQuery;
        $completedQuery = clone $baseQuery;
        $canceledQuery = clone $baseQuery;

        return [
            'total' => $baseQuery->count(),
            'pending' => $pendingQuery->where('status', 'pending')->count(),
            'in_progress' => $inProgressQuery->where('status', 'in_progress')->count(),
            'completed' => $completedQuery->where('status', 'completed')->count(),
            'canceled' => $canceledQuery->where('status', 'canceled')->count(),
            'completion_rate' => $this->calculateCompletionRate(),
        ];
    }

    /**
     * Calcule le taux de complétion des tâches (%)
     *
     * @return float
     */
    private function calculateCompletionRate()
    {
        $baseQuery = Task::query();
        $this->applyDateFilters($baseQuery);
        $total = $baseQuery->count();

        if ($total === 0) {
            return 0;
        }

        $completedQuery = Task::query()->where('status', 'completed');
        $this->applyDateFilters($completedQuery);
        $completed = $completedQuery->count();

        return round(($completed / $total) * 100, 2);
    }

    /**
     * Obtenir les statistiques de temps de réalisation des tâches
     *
     * @return array
     */
    public function getTaskTimeStats()
    {
        $completedTasks = Task::where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereNotNull('created_at');

        $this->applyDateFilters($completedTasks, 'completed_at');
        $completedTasks = $completedTasks->get();

        if ($completedTasks->isEmpty()) {
            return [
                'avg_completion_time' => 0,
                'max_completion_time' => 0,
                'min_completion_time' => 0,
                'avg_completion_seconds' => 0,
                'max_completion_seconds' => 0,
                'min_completion_seconds' => 0,
            ];
        }

        $completionTimes = $completedTasks->map(function ($task) {
            $created = Carbon::parse($task->created_at);
            $completed = Carbon::parse($task->completed_at);
            return $created->diffInSeconds($completed);
        });

        $avgSeconds = round($completionTimes->avg());
        $maxSeconds = $completionTimes->max();
        $minSeconds = $completionTimes->min();

        return [
            'avg_completion_time' => round($avgSeconds / 3600, 2), // Conversion en heures pour compatibilité
            'max_completion_time' => round($maxSeconds / 3600, 2),
            'min_completion_time' => round($minSeconds / 3600, 2),
            'avg_completion_seconds' => $avgSeconds,
            'max_completion_seconds' => $maxSeconds,
            'min_completion_seconds' => $minSeconds,
        ];
    }

    /**
     * Obtenir les statistiques des tâches par utilisateur
     *
     * @return array
     */
    public function getTasksByUser()
    {
        $users = User::where('role', '!=', 'admin')
        ->withCount([
            'tasks as total_tasks' => function ($query) {
                $this->applyDateFilters($query);
            },
            'tasks as completed_tasks' => function ($query) {
                $query->where('status', 'completed');
                $this->applyDateFilters($query);
            },
            'tasks as pending_tasks' => function ($query) {
                $query->where('status', 'pending');
                $this->applyDateFilters($query);
            },
            'tasks as in_progress_tasks' => function ($query) {
                $query->where('status', 'in_progress');
                $this->applyDateFilters($query);
            }
        ])->get();

        return $users->map(function ($user) {
            $completionRate = 0;
            if ($user->total_tasks > 0) {
                $completionRate = round(($user->completed_tasks / $user->total_tasks) * 100, 2);
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'total_tasks' => $user->total_tasks,
                'completed_tasks' => $user->completed_tasks,
                'pending_tasks' => $user->pending_tasks,
                'in_progress_tasks' => $user->in_progress_tasks,
                'completion_rate' => $completionRate,
            ];
        });
    }

    /**
     * Obtenir les statistiques des tâches par utilisateur avec pagination
     *
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTasksByUserPaginated($page = 1, $perPage = 15)
    {
        $users = User::where('role', '!=', 'admin')
            ->withCount([
                'tasks as total_tasks' => function ($query) {
                    $this->applyDateFilters($query);
                },
                'tasks as completed_tasks' => function ($query) {
                    $query->where('status', 'completed');
                    $this->applyDateFilters($query);
                },
                'tasks as pending_tasks' => function ($query) {
                    $query->where('status', 'pending');
                    $this->applyDateFilters($query);
                },
                'tasks as in_progress_tasks' => function ($query) {
                    $query->where('status', 'in_progress');
                    $this->applyDateFilters($query);
                }
            ])
            ->paginate($perPage, ['*'], 'page', $page);

        $users->setCollection($users->getCollection()->map(function ($user) {
            $completionRate = 0;
            if ($user->total_tasks > 0) {
                $completionRate = round(($user->completed_tasks / $user->total_tasks) * 100, 2);
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'total_tasks' => $user->total_tasks,
                'completed_tasks' => $user->completed_tasks,
                'pending_tasks' => $user->pending_tasks,
                'in_progress_tasks' => $user->in_progress_tasks,
                'completion_rate' => $completionRate,
            ];
        }));

        return $users;
    }

    /**
     * Obtenir les statistiques des tâches des 5 meilleurs coursiers
     *
     * @return array
     */
    public function getTasksByUserTop5()
    {
        $users = User::where('role', '!=', 'admin')
        ->withCount([
            'tasks as total_tasks' => function ($query) {
                $this->applyDateFilters($query);
            },
            'tasks as completed_tasks' => function ($query) {
                $query->where('status', 'completed');
                $this->applyDateFilters($query);
            },
            'tasks as pending_tasks' => function ($query) {
                $query->where('status', 'pending');
                $this->applyDateFilters($query);
            },
            'tasks as in_progress_tasks' => function ($query) {
                $query->where('status', 'in_progress');
                $this->applyDateFilters($query);
            }
        ])->get();

        return $users->map(function ($user) {
            $completionRate = 0;
            if ($user->total_tasks > 0) {
                $completionRate = round(($user->completed_tasks / $user->total_tasks) * 100, 2);
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'total_tasks' => $user->total_tasks,
                'completed_tasks' => $user->completed_tasks,
                'pending_tasks' => $user->pending_tasks,
                'in_progress_tasks' => $user->in_progress_tasks,
                'completion_rate' => $completionRate,
            ];
        })
        ->sortByDesc('completion_rate')
        ->take(5)
        ->values()
        ->all();
    }

    /**
     * Obtenir les statistiques de tâches par priorité
     *
     * @return array
     */
    public function getTasksByPriority()
    {
        $priorities = ['low', 'medium', 'high'];
        $result = [];

        foreach ($priorities as $priority) {
            $query = Task::where('priority', $priority);
            $this->applyDateFilters($query);
            $result[$priority] = $query->count();
        }

        return $result;
    }

    /**
     * Obtenir les statistiques de tâches par jour de la semaine
     *
     * @return array
     */
    public function getTasksByDayOfWeek()
    {
        $days = [
            'Lundi', 'Mardi', 'Mercredi', 'Jeudi',
            'Vendredi', 'Samedi', 'Dimanche'
        ];

        // PostgreSQL utilise EXTRACT(DOW) plutôt que DAYOFWEEK
        // DOW: 0 (dimanche) à 6 (samedi)
        $stats = Task::select(DB::raw('EXTRACT(DOW FROM created_at) as day'), DB::raw('count(*) as count'));
        $this->applyDateFilters($stats);
        $stats = $stats->groupBy('day')
            ->get()
            ->keyBy(function ($item) {
                // Convertir le format PostgreSQL (0=dimanche, 6=samedi)
                // vers notre format (0=lundi, 6=dimanche)
                $day = $item->day;
                // Si dimanche (0), on le place à la fin (6)
                if ($day == 0) return 6;
                // Sinon, on décale de 1 (lundi=1 devient lundi=0, etc.)
                return $day - 1;
            });

        $result = [];
        for ($i = 0; $i < 7; $i++) {
            $result[$days[$i]] = $stats->has($i) ? $stats[$i]->count : 0;
        }

        return $result;
    }

    /**
     * Obtenir les statistiques de tâches sur les 12 derniers mois
     *
     * @return array
     */
    public function getTasksByMonth()
    {
        $result = [];
        $now = Carbon::now();

        for ($i = 0; $i < 12; $i++) {
            $month = $now->copy()->subMonths($i);
            $monthName = $month->translatedFormat('F Y');

            $createdQuery = Task::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month);
            $this->applyDateFilters($createdQuery);
            $completedQuery = Task::whereYear('completed_at', $month->year)
                ->whereMonth('completed_at', $month->month)
                ->where('status', 'completed');
            $this->applyDateFilters($completedQuery);

            $result[$monthName] = [
                'created' => $createdQuery->count(),
                'completed' => $completedQuery->count(),
            ];
        }

        return array_reverse($result);
    }

    /**
     * Obtenir les statistiques des jalons (milestones)
     *
     * @return array
     */
    public function getMilestoneStats()
    {
        // For milestone stats, we don't apply date filtering directly to the milestones
        // but rather to the tasks associated with those milestones
        $milestonesWithTasks = Milestone::withCount(['tasks' => function ($query) {
            $this->applyDateFilters($query);
        }])->get();

        return [
            'total' => Milestone::count(),
            'favorites' => Milestone::where('favorite', true)->count(),
            'tasks_per_milestone' => $this->getAvgTasksPerMilestone(),
            'most_used' => $this->getMostUsedMilestone(),
        ];
    }

    /**
     * Obtenir la moyenne de tâches par jalon
     *
     * @return float
     */
    private function getAvgTasksPerMilestone()
    {
        $milestoneCount = Milestone::count();
        if ($milestoneCount === 0) {
            return 0;
        }

        $taskQuery = Task::query();
        $this->applyDateFilters($taskQuery);
        $taskCount = $taskQuery->count();

        return round($taskCount / $milestoneCount, 2);
    }

    /**
     * Obtenir le jalon le plus utilisé
     *
     * @return array|null
     */
    private function getMostUsedMilestone()
    {
        $milestone = Milestone::withCount(['tasks' => function ($query) {
            $this->applyDateFilters($query);
        }])
            ->orderBy('tasks_count', 'desc')
            ->first();

        if (!$milestone) {
            return null;
        }

        return [
            'id' => $milestone->id,
            'name' => $milestone->name,
            'tasks_count' => $milestone->tasks_count,
        ];
    }

    /**
     * Obtenir l'information sur le filtre de date actuel
     *
     * @return array
     */
    public function getCurrentFilterInfo()
    {
        if (!$this->startDate && !$this->endDate) {
            return [
                'is_filtered' => false,
                'description' => 'Toutes les périodes',
            ];
        }

        $startDateStr = $this->startDate ? $this->startDate->format('d/m/Y') : '';
        $endDateStr = $this->endDate ? $this->endDate->format('d/m/Y') : '';

        if ($this->startDate && $this->endDate) {
            if ($this->startDate->isSameDay($this->endDate)) {
                return [
                    'is_filtered' => true,
                    'description' => 'Pour le ' . $startDateStr,
                ];
            } else {
                return [
                    'is_filtered' => true,
                    'description' => 'Du ' . $startDateStr . ' au ' . $endDateStr,
                ];
            }
        } elseif ($this->startDate) {
            return [
                'is_filtered' => true,
                'description' => 'À partir du ' . $startDateStr,
            ];
        } else {
            return [
                'is_filtered' => true,
                'description' => 'Jusqu\'au ' . $endDateStr,
            ];
        }
    }

    /**
     * Obtenir toutes les statistiques pour le tableau de bord
     *
     * @return array
     */
    public function getAllStats()
    {
        return [
            'task_stats' => $this->getTaskStats(),
            'task_time_stats' => $this->getTaskTimeStats(),
            'tasks_by_priority' => $this->getTasksByPriority(),
            'tasks_by_day' => $this->getTasksByDayOfWeek(),
            'tasks_by_month' => $this->getTasksByMonth(),
            'milestone_stats' => $this->getMilestoneStats(),
            'users_stats_top_5' => $this->getTasksByUserTop5(),
        ];
    }
}
