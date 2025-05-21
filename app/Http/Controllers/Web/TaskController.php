<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TaskController extends Controller
{
    public function history(Request $request)
    {
        // Create cache key based on all current filters and pagination
        $cacheKey = 'tasks.web.history.' . md5(json_encode([
            'status' => $request->status,
            'priority' => $request->priority,
            'search' => $request->search,
            'date_filter' => $request->date_filter,
            'page' => $request->page ?? 1
        ]));

        // Get tasks from cache or database (cache for 1 hour)
        $tasks = Cache::remember($cacheKey, 1800, function () use ($request) {
            $query = Task::query()->with('user');

            // Apply filters if they exist in the request
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('user', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->filled('date_filter')) {
                switch ($request->date_filter) {
                    case 'today':
                        $query->whereDate('created_at', now()->toDateString());
                        break;
                    case 'week':
                        $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'month':
                        $query->whereMonth('created_at', now()->month)
                              ->whereYear('created_at', now()->year);
                        break;
                }
            }

            return $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        });

        return view('pages.tasks.history', compact('tasks'));
    }
}
