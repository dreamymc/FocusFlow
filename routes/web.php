<?php

use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Http\Middleware\VerifyWebhookSignature;
use App\Http\Controllers\Webhooks\StripeController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\RegisterController;
use App\Http\Controllers\Web\Auth\LogoutController;

use App\Http\Controllers\Web\WorkspaceSwitchController;

// Stripe webhook — must be BEFORE auth middleware
Route::post('/stripe/webhook', [StripeController::class, 'handleWebhook'])
    ->middleware(VerifyWebhookSignature::class);

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'destroy'])->name('logout');
    Route::post('/workspaces/switch', [WorkspaceSwitchController::class, 'store'])->name('workspaces.switch');
    Route::get('/dashboard', [\App\Http\Controllers\Web\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/workspaces/create', [\App\Http\Controllers\Web\WorkspaceController::class, 'create'])->name('workspaces.create');
    Route::post('/workspaces', [\App\Http\Controllers\Web\WorkspaceController::class, 'store'])->name('workspaces.store');

    Route::middleware('scope.workspace')->group(function () {
        Route::get('/workspaces/{workspace}/settings', [\App\Http\Controllers\Web\WorkspaceController::class, 'settings'])->name('workspaces.settings');
        Route::put('/workspaces/{workspace}', [\App\Http\Controllers\Web\WorkspaceController::class, 'update'])->name('workspaces.update');
        Route::post('/workspaces/{workspace}/invite', [\App\Http\Controllers\Web\WorkspaceController::class, 'invite'])->name('workspaces.invite');
    });
});

// Root redirect
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

