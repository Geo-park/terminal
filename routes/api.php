<?php

use App\Http\Controllers\Api\DesktopAgentController;
use Illuminate\Support\Facades\Route;

Route::prefix('desktop')->group(function () {
    Route::get('/status/{computer_code}', [DesktopAgentController::class, 'getComputerStatus']);
    Route::post('/check-in', [DesktopAgentController::class, 'checkIn']);
    Route::post('/heartbeat', [DesktopAgentController::class, 'heartbeat']);
    Route::post('/session/end', [DesktopAgentController::class, 'endSession']);
});

