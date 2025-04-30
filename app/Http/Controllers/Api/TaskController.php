<?php

namespace App\Http\Controllers\Api;

use App\Events\TaskCreated;
use App\Events\TaskDeleted;
use App\Events\TaskUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $role = request()->user('api')->role;
        if ($role === "admin"){
            $tasks = Task::with(['milestone', 'user'])->get();
            return TaskResource::collection($tasks);
        }
        else {
            $userId = request()->user('api')->id;
            $tasks = Task::with(['milestone', 'user'])->where('user_id', $userId)->get();
            return TaskResource::collection($tasks);
        }

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

        // get tenantId from x-tenant-id header
        $tenantId = request()->header('x-tenant-id');

        broadcast(new TaskCreated(new TaskResource($task), $tenantId, $task->user->username));

        // Return the created task with camelCase attributes
        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        return new TaskResource($task->load(['milestone', 'user']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, $task)
    {
        $taskInstance = Task::findOrFail($task);

        // Update the task with the validated data
        $taskInstance->update($request->validated());

        // get tenantId from x-tenant-id header
        $tenantId = request()->header('x-tenant-id');

        // Broadcast the task update event
        broadcast(new TaskUpdated(new TaskResource($taskInstance->fresh()->load(['milestone', 'user'])), $tenantId));

        // Return the updated task with camelCase attributes
        return new TaskResource($taskInstance->fresh()->load(['milestone', 'user']));
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
            // get tenantId from x-tenant-id header
            $tenantId = request()->header('x-tenant-id');
            // Broadcast the task update event
            broadcast(new TaskUpdated(new TaskResource($taskInstance->fresh()->load(['milestone', 'user'])), $tenantId));

            return new TaskResource($taskInstance->fresh()->load(['milestone', 'user']));
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
            // get tenantId from x-tenant-id header
            $tenantId = request()->header('x-tenant-id');
            // Broadcast the task update event
            broadcast(new TaskUpdated(new TaskResource($taskInstance->fresh()->load(['milestone', 'user'])), $tenantId));

            return new TaskResource($taskInstance->fresh()->load(['milestone', 'user']));
        } else {
            throw new AccessDeniedHttpException();
        }
    }
}
