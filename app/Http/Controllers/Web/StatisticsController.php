<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    protected $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    public function index(Request $request)
    {
        // Récupération des dates depuis la requête
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $filterType = $request->get('filter_type', 'all');

        // Application des filtres prédéfinis
        if ($filterType !== 'custom') {
            list($startDate, $endDate) = $this->getDateRangeFromFilterType($filterType);
        }

        // Application des filtres de dates au service
        $this->statisticsService->setDateRange($startDate, $endDate);

        // Récupération des statistiques filtrées
        $stats = $this->statisticsService->getAllStats();

        // Obtenir l'information du filtre actuel
        $filterInfo = $this->statisticsService->getCurrentFilterInfo();

        return view('pages.statistics.index', [
            'taskStats' => $stats['task_stats'],
            'taskTimeStats' => $stats['task_time_stats'],
            'tasksByPriority' => $stats['tasks_by_priority'],
            'tasksByDay' => $stats['tasks_by_day'],
            'tasksByMonth' => $stats['tasks_by_month'],
            'milestoneStats' => $stats['milestone_stats'],
            'usersStats' => $stats['users_stats_top_5'],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filterType' => $filterType,
            'filterInfo' => $filterInfo,
        ]);
    }

    public function couriers(Request $request)
    {
        // Récupération des dates depuis la requête
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $filterType = $request->get('filter_type', 'all');

        // Application des filtres prédéfinis
        if ($filterType !== 'custom') {
            list($startDate, $endDate) = $this->getDateRangeFromFilterType($filterType);
        }

        // Application des filtres de dates au service
        $this->statisticsService->setDateRange($startDate, $endDate);

        // Récupération des statistiques des coursiers
        $couriers = $this->statisticsService->getTasksByUserPaginated($request->get('page', 1));

        // Obtenir l'information du filtre actuel
        $filterInfo = $this->statisticsService->getCurrentFilterInfo();

        return view('pages.statistics.couriers', [
            'couriers' => $couriers,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filterType' => $filterType,
            'filterInfo' => $filterInfo,
        ]);
    }

    /**
     * Get date range from predefined filter type
     *
     * @param string $filterType
     * @return array [startDate, endDate]
     */
    private function getDateRangeFromFilterType($filterType)
    {
        $startDate = null;
        $endDate = null;

        switch ($filterType) {
            case 'today':
                $startDate = Carbon::today()->format('Y-m-d');
                $endDate = Carbon::today()->format('Y-m-d');
                break;

            case 'yesterday':
                $startDate = Carbon::yesterday()->format('Y-m-d');
                $endDate = Carbon::yesterday()->format('Y-m-d');
                break;

            case 'this_week':
                $startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
                $endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
                break;

            case 'last_week':
                $startDate = Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d');
                $endDate = Carbon::now()->subWeek()->endOfWeek()->format('Y-m-d');
                break;

            case 'this_month':
                $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;

            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
                $endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;

            case 'this_year':
                $startDate = Carbon::now()->startOfYear()->format('Y-m-d');
                $endDate = Carbon::now()->endOfYear()->format('Y-m-d');
                break;

            case 'last_year':
                $startDate = Carbon::now()->subYear()->startOfYear()->format('Y-m-d');
                $endDate = Carbon::now()->subYear()->endOfYear()->format('Y-m-d');
                break;

            case 'all':
            default:
                // Pas de filtrage par date
                break;
        }

        return [$startDate, $endDate];
    }
}
