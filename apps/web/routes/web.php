<?php

use App\Http\Controllers\ChannelController;
use App\Http\Controllers\CurrentChannelController;
use App\Models\Team;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Channels
    Route::get('/channels/create', function () {
        return Inertia::render('Channels/Create', [
            'teams' => Team::all(),
        ]);
    })->name('channels.create');

    Route::post('/channels', [ChannelController::class, 'store'])->name('channels.store');
    Route::put('/current-channel', [CurrentChannelController::class, 'update'])->name('current-channel.update');
});
