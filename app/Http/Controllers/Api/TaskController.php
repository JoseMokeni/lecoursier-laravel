<?php

namespace App\Http\Controllers\Api;

use App\Events\TaskCreated;
use App\Events\TaskDeleted;
use App\Events\TaskUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Jobs\SendFcmNotification;
use App\Models\Task;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TaskController extends Controller
{
    protected FcmService $fcmService;

    public function __construct()
    {
        $this->fcmService = new FcmService();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $role = request()->user('api')->role;
        $userId = request()->user('api')->id;
        $status = request()->query('status');

        // Create cache key based on role, user ID and status
        $cacheKey = "tasks.{$role}.{$userId}";
        if ($role === "admin") {
            $cacheKey = "tasks.admin";
        }
        if ($status) {
            $cacheKey .= ".{$status}";
        }

        // Get tasks from cache or database
        $tasks = Cache::remember($cacheKey, 3600, function () use ($role, $userId, $status) {
            if ($role === "admin"){
                switch ($status) {
                    case 'in_progress':
                        return Task::with(['milestone', 'user'])->where('status', 'in_progress')->get();
                    case 'completed':
                        return Task::with(['milestone', 'user'])->where('status', 'completed')->get();
                    case 'pending':
                        return Task::with(['milestone', 'user'])->where('status', 'pending')->get();
                    default:
                        return Task::with(['milestone', 'user'])->get();
                }
            }
            else {
                switch ($status) {
                    case 'in_progress':
                        return Task::with(['milestone', 'user'])->where('user_id', $userId)->where('status', 'in_progress')->get();
                    case 'completed':
                        return Task::with(['milestone', 'user'])->where('user_id', $userId)->where('status', 'completed')->get();
                    case 'pending':
                        return Task::with(['milestone', 'user'])->where('user_id', $userId)->where('status', 'pending')->get();
                    default:
                        return Task::with(['milestone', 'user'])->where('user_id', $userId)->get();
                }
            }
        });

        // Convert to resources when returning
        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        // Validate the request
        $validatedData = $request->validated();

        // Create a new task
        $task = Task::create($validatedData);
        $task->load(['milestone', 'user']);

        // Add the new task to the relevant caches
        $this->updateTaskInCache($task);

        // get tenantId from x-tenant-id header
        $tenantId = request()->header('x-tenant-id');

        broadcast(new TaskCreated(new TaskResource($task), $tenantId, $task->user->username));

        // send fcm notification to the user
        SendFcmNotification::dispatch($task->user_id, 'Nouvelle tâche assignée', 'Une nouvelle tâche vous a été assignée: ' . $task->name);

        // Return the created task with camelCase attributes
        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show($task)
    {
        // Get task from cache or database
        $taskModel = Cache::remember('task.'.$task, 3600, function () use ($task) {
            return Task::with(['milestone', 'user'])->findOrFail($task);
        });

        // Convert to resource when returning
        return new TaskResource($taskModel);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, $task)
    {
        $taskInstance = Task::findOrFail($task);

        // Update the task with the validated data
        $taskInstance->update($request->validated());
        $taskInstance = $taskInstance->fresh()->load(['milestone', 'user']);

        // Update the task in cache
        $this->updateTaskInCache($taskInstance);

        // get tenantId from x-tenant-id header
        $tenantId = request()->header('x-tenant-id');

        // Broadcast the task update event
        broadcast(new TaskUpdated(new TaskResource($taskInstance), $tenantId));

        // Return the updated task with camelCase attributes
        return new TaskResource($taskInstance);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($task)
    {
        $taskInstance = Task::findOrFail($task);

        // load the user to get the user's username
        $taskInstance->load('user');

        if (request()->user('api')->can('delete', $taskInstance)) {
            // Remove task from cache before deleting
            $this->removeTaskFromCache($taskInstance);

            $taskInstance->delete();

            // get tenantId from x-tenant-id header
            $tenantId = request()->header('x-tenant-id');
            broadcast(new TaskDeleted($taskInstance->id, $tenantId, $taskInstance->user->username));
            return response()->json(['message' => 'Task deleted successfully'], 200);
        } else {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * Change the status of the task to in progress.
     */
    public function start($task)
    {
        $taskInstance = Task::findOrFail($task);

        if (request()->user('api')->can('update', $taskInstance)) {
            $taskInstance->update(['status' => 'in_progress']);
            $taskInstance = $taskInstance->fresh()->load(['milestone', 'user']);

            // Update the task in cache
            $this->updateTaskInCache($taskInstance);

            // get tenantId from x-tenant-id header
            $tenantId = request()->header('x-tenant-id');

            // send fcm notification to all admins
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                SendFcmNotification::dispatch($admin->id, 'Tâche en cours', 'La tâche ' . $taskInstance->name . ' est maintenant en cours.');
            }

            broadcast(new TaskUpdated(new TaskResource($taskInstance), $tenantId));

            return new TaskResource($taskInstance);
        } else {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * Change the status of the task to completed.
     */
    public function complete($task)
    {
        $taskInstance = Task::findOrFail($task);

        if (request()->user('api')->can('update', $taskInstance)) {
            $taskInstance->update(['status' => 'completed', 'completed_at' => now()]);
            $taskInstance = $taskInstance->fresh()->load(['milestone', 'user']);

            // Update the task in cache
            $this->updateTaskInCache($taskInstance);

            // get tenantId from x-tenant-id header
            $tenantId = request()->header('x-tenant-id');

            // send fcm notification to all admins
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                SendFcmNotification::dispatch($admin->id, 'Tâche terminée', 'La tâche ' . $taskInstance->name . ' est maintenant terminée.');
            }

            // Broadcast the task update event
            broadcast(new TaskUpdated(new TaskResource($taskInstance), $tenantId));

            return new TaskResource($taskInstance);
        } else {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * Update a task in all relevant caches
     */
    private function updateTaskInCache(Task $task)
    {
        // Update single task cache - store the model, not the resource
        Cache::put('task.' . $task->id, $task, 3600);

        // Update task in collection caches
        $this->updateTaskInCollectionCache($task);
    }

    /**
     * Remove a task from all relevant caches
     */
    private function removeTaskFromCache(Task $task)
    {
        // Remove from individual task cache
        Cache::forget('task.' . $task->id);

        // Update collection caches to remove this task
        $this->updateTaskInCollectionCache($task, true);
    }

    /**
     * Update task in collection caches (or remove it if $remove is true)
     */
    private function updateTaskInCollectionCache(Task $task, bool $remove = false)
    {
        $adminCacheKeys = [
            'tasks.admin',
            'tasks.admin.' . $task->status,
        ];

        $userCacheKeys = [
            'tasks.user.' . $task->user_id,
            'tasks.user.' . $task->user_id . '.' . $task->status,
        ];

        $allCacheKeys = array_merge($adminCacheKeys, $userCacheKeys);

        foreach ($allCacheKeys as $cacheKey) {
            if (Cache::has($cacheKey)) {
                $cachedTasks = Cache::get($cacheKey);

                if ($remove) {
                    // Remove task from collection
                    $updatedTasks = $cachedTasks->filter(function ($cachedTask) use ($task) {
                        return $cachedTask->id !== $task->id;
                    })->values();
                } else {
                    // Update or add task to collection
                    $taskExists = false;

                    $updatedTasks = $cachedTasks->map(function ($cachedTask) use ($task, &$taskExists) {
                        if ($cachedTask->id === $task->id) {
                            $taskExists = true;
                            return $task;
                        }
                        return $cachedTask;
                    });

                    // If task wasn't in the collection, add it (when it matches the collection criteria)
                    if (!$taskExists && $this->taskMatchesCollectionCriteria($task, $cacheKey)) {
                        $updatedTasks->push($task);
                    }
                }

                // Put back the updated collection
                Cache::put($cacheKey, $updatedTasks, 3600);
            }
        }
    }

    /**
     * Determine if a task matches the criteria for a collection cache key
     */
    private function taskMatchesCollectionCriteria(Task $task, string $cacheKey): bool
    {
        // Admin cache keys contain all tasks
        if (strpos($cacheKey, 'tasks.admin') === 0) {
            // Check if there's a status filter
            if (strpos($cacheKey, '.in_progress') !== false) {
                return $task->status === 'in_progress';
            } elseif (strpos($cacheKey, '.completed') !== false) {
                return $task->status === 'completed';
            } elseif (strpos($cacheKey, '.pending') !== false) {
                return $task->status === 'pending';
            }
            // No status filter - all tasks match
            return true;
        }

        // User-specific cache keys
        if (strpos($cacheKey, 'tasks.user.' . $task->user_id) === 0) {
            // Check if there's a status filter
            if (strpos($cacheKey, '.in_progress') !== false) {
                return $task->status === 'in_progress';
            } elseif (strpos($cacheKey, '.completed') !== false) {
                return $task->status === 'completed';
            } elseif (strpos($cacheKey, '.pending') !== false) {
                return $task->status === 'pending';
            }
            // No status filter - all user tasks match
            return true;
        }

        return false;
    }

    /**
     * Helper method to clear task-related cache (kept for fallback)
     */
    private function clearTaskCache($taskId = null)
    {
        // Clear specific task cache if task ID is provided
        if ($taskId) {
            Cache::forget('task.' . $taskId);
        }

        // Clear the admin tasks cache
        Cache::forget('tasks.admin');
        Cache::forget('tasks.admin.in_progress');
        Cache::forget('tasks.admin.completed');
        Cache::forget('tasks.admin.pending');

        // Clear cache for every user
        $userIds = User::pluck('id')->toArray();
        foreach ($userIds as $userId) {
            Cache::forget('tasks.user.' . $userId);
            Cache::forget('tasks.user.' . $userId . '.in_progress');
            Cache::forget('tasks.user.' . $userId . '.completed');
            Cache::forget('tasks.user.' . $userId . '.pending');
        }
    }
}
