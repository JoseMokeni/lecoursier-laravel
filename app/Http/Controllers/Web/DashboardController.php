<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\UserService;

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

        return view('pages.dashboard.index', [
            'users' => $users,
            'userCount' => $userCount
        ]);
    }
}
