<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Models\Task;
use App\Models\User;

class DashboardController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $users = $this->userService->getUsers();
        $userCount = $this->userService->countUsers();

        // Task statistics
        $totalTasks = Task::count();
        $tasksInProgress = Task::where('status', 'in_progress')->count();
        $tasksCompleted = Task::where('status', 'completed')->count();
        $tasksPending = Task::where('status', 'pending')->count();

        return view('pages.dashboard.index', [
            'users' => $users,
            'userCount' => $userCount,
            'totalTasks' => $totalTasks,
            'tasksInProgress' => $tasksInProgress,
            'tasksCompleted' => $tasksCompleted,
            'tasksPending' => $tasksPending,
        ]);
    }
}
