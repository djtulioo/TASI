<?php

use App\Http\Controllers\ChannelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rotas para gerenciamento de canais
Route::middleware('auth:sanctum')->prefix('channels')->group(function () {
    Route::get('/', [ChannelController::class, 'index'])->name('channels.index');
    Route::post('/', [ChannelController::class, 'store'])->name('channels.store');
    Route::get('/{id}', [ChannelController::class, 'show'])->name('channels.show');
    Route::put('/{id}', [ChannelController::class, 'update'])->name('channels.update');
    Route::delete('/{id}', [ChannelController::class, 'destroy'])->name('channels.destroy');
});
