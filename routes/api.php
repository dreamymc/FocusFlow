<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\WorkspaceController;
use App\Http\Controllers\Api\V1\InvitationController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\CommentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    // Guest routes
    Route::post('/register', RegisterController::class)->name('register')->middleware('throttle:auth');
    Route::post('/login', LoginController::class)->name('login')->middleware('throttle:auth');

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', LogoutController::class)->name('logout');

        // Workspace CRUD
        Route::apiResource('workspaces', WorkspaceController::class);

        // Accept invitation
        Route::post('invitations/accept', [InvitationController::class, 'accept'])->name('invitations.accept');

        // Workspace-scoped routes
        Route::prefix('workspaces/{workspace}')
            ->middleware('scope.workspace')
            ->group(function () {
                Route::post('/invite', [InvitationController::class, 'invite'])->name('workspaces.invite');
                Route::get('/billing-portal', [WorkspaceController::class, 'billingPortal'])->name('workspaces.billing-portal');
                Route::apiResource('projects', ProjectController::class);
                Route::apiResource('projects.tasks', TaskController::class);
                Route::apiResource('projects.tasks.comments', CommentController::class)->only(['index', 'store']);
                Route::put('tasks/{task}/move', [TaskController::class, 'move'])->name('tasks.move');
            });
    });
});
