<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    protected $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    public function index()
    {
        $stats = $this->statisticsService->getAllStats();

        return view('pages.statistics.index', [
            'taskStats' => $stats['task_stats'],
            'taskTimeStats' => $stats['task_time_stats'],
            'tasksByPriority' => $stats['tasks_by_priority'],
            'tasksByDay' => $stats['tasks_by_day'],
            'tasksByMonth' => $stats['tasks_by_month'],
            'milestoneStats' => $stats['milestone_stats'],
            'usersStats' => $stats['users_stats'],
        ]);
    }
}
