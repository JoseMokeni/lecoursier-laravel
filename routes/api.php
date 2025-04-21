<?php

use App\Http\Controllers\Api\MilestoneController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::middleware(['api.tenant.context', 'api.active.tenant', 'api.tenant.subscribed'])
    ->group(function () {
        // test route
        Route::get('/test', function (Request $request) {
            $users = \App\Models\User::all();
            return response()->json([
                'message' => 'Tenant context is set',
                'tenant_id' => $request->attributes->get('tenant_id'),
                'users' => $users,
            ]);
        });

        // login route
        Route::post('/login', [\App\Http\Controllers\Api\LoginController::class, 'login'])
            ->name('api.login');

        // Auth routes
        Route::middleware('api.auth')
            ->group(function () {
            Route::get('/locked', function (Request $request) {
                return response()->json([
                    'message' => 'Locked route',
                    'user' => $request->user('api'),
                ]);
            });
        });

        // Admin routes
        Route::middleware(['api.auth', 'api.admin.only'])
            ->group(function () {
            Route::get('/admin', function (Request $request) {
                return response()->json([
                    'message' => 'Admin only route',
                    'user' => $request->user('api'),
                ]);
            });

            Route::get('/users', function (Request $request) {
                return \App\Http\Resources\UserResource::collection(\App\Models\User::where('role', 'user')->get());
            });
        });

        // Milestone routes
        Route::apiResource('milestones', MilestoneController::class)
            ->middleware('api.auth');

        // Task routes
        Route::apiResource('tasks', TaskController::class)
            ->middleware('api.auth');

    });



