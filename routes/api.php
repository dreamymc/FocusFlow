<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\WorkspaceController;
use App\Http\Controllers\Api\V1\InvitationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Guest routes
    Route::post('/register', RegisterController::class)->name('register');
    Route::post('/login', LoginController::class)->name('login');

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', LogoutController::class)->name('logout');

        // Workspace CRUD
        Route::apiResource('workspaces', WorkspaceController::class);

        // Accept invitation
        Route::post('/invitations/accept', [InvitationController::class, 'accept'])->name('invitations.accept');

        // Workspace-scoped routes
        Route::prefix('workspaces/{workspace}')
            ->middleware('scope.workspace')
            ->group(function () {
                Route::post('/invite', [InvitationController::class, 'invite'])->name('workspaces.invite');
            });
    });
});
