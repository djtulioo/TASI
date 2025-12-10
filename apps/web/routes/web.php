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
        
        // Coletar estatísticas do canal selecionado
        $channel = $currentTeam->lastSelectedChannel;
        $botUserId = $channel->phone_number_id ?? $channel->telegram_bot_token;
        
        $query = \App\Models\FeedbackEntry::query();
        if ($botUserId) {
            $query->where('bot_user_id', $botUserId);
        } else {
            $query->whereIn('channel_id', $channel->sameBotChannelIds());
        }

        $stats = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pendente')->count(),
            'resolved' => (clone $query)->where('status', 'resolvido')->count(),
            'analyzing' => (clone $query)->where('status', 'em_analise')->count(),
            'recent' => (clone $query)->with('conversation')->latest()->take(5)->get()
                ->map(function($entry) {
                    return [
                        'id' => $entry->id,
                        'title' => $entry->titulo ?? 'Sem título',
                        'description' => \Illuminate\Support\Str::limit($entry->descricao, 50),
                        'status' => $entry->status,
                        'date' => $entry->created_at->diffForHumans(),
                        'type' => $entry->tipo
                    ];
                })
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats
        ]);
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

    // Feedback Entries (Ouvidoria)
    Route::get('/feedback-entries', [App\Http\Controllers\FeedbackEntryController::class, 'index'])->name('feedback-entries.index');
    Route::get('/feedback-entries/{feedbackEntry}', [App\Http\Controllers\FeedbackEntryController::class, 'show'])->name('feedback-entries.show');
    Route::put('/feedback-entries/{feedbackEntry}', [App\Http\Controllers\FeedbackEntryController::class, 'update'])->name('feedback-entries.update');
    Route::delete('/feedback-entries/{feedbackEntry}', [App\Http\Controllers\FeedbackEntryController::class, 'destroy'])->name('feedback-entries.destroy');

    // Analysis (Resumo e Chat RAG)
    Route::get('/analysis', function () {
        return Inertia::render('Analysis/Index');
    })->name('analysis.index');
    Route::post('/analysis/summary', [App\Http\Controllers\ChatAnalysisController::class, 'summary'])->name('analysis.summary');
    Route::post('/analysis/chat', [App\Http\Controllers\ChatAnalysisController::class, 'chat'])->name('analysis.chat');

});
