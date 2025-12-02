<?php

use App\Http\Controllers\ChannelController;
use App\Http\Controllers\CurrentChannelController;
use App\Models\Team;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Services\TelegramService;

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
        $user = request()->user();
        $currentTeam = $user->currentTeam;

        // Verificar se existem canais para o time atual
        $hasChannels = $currentTeam->channels()->exists();

        // Se não houver canais, redirecionar para a página de criação
        if (!$hasChannels) {
            return redirect()->route('channels.create');
        }

        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Channels
    Route::get('/channels/create', function () {
        return Inertia::render('Channels/Create', [
            'teams' => Team::all(),
        ]);
    })->name('channels.create');

    Route::post('/channels', [ChannelController::class, 'store'])->name('channels.store');
    Route::match(['put', 'post'], '/channels/{channel}', [ChannelController::class, 'update'])->name('channels.update');
    Route::delete('/channels/{channel}', [ChannelController::class, 'destroy'])->name('channels.destroy');
    Route::put('/current-channel', [CurrentChannelController::class, 'update'])->name('current-channel.update');

    Route::get('/chat', [App\Http\Controllers\ConversationController::class, 'index'])->name('chat');
    Route::get('/chat/{senderId}', [App\Http\Controllers\ConversationController::class, 'show'])->name('chat.messages');
    Route::post('/chat/{senderId}/send', [App\Http\Controllers\ConversationController::class, 'store'])->name('chat.send');

    Route::get('/test', function () {
        $webhookUrl = route('api.webhook.telegram', ['bot_token' => '123456789:ABCdefGHIjklMNOpqrsTUVwxyz']);

        echo $webhookUrl;
    })->name('test');

});
