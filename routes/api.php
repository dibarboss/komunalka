<?php

use App\Http\Controllers\API\AuthTokenController;
use App\Http\Controllers\API\MeterController;
use App\Http\Controllers\API\MeterReadingController;
use App\Http\Controllers\API\MeterStatisticsController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/token', [AuthTokenController::class, 'store']);

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::delete('/auth/token', [AuthTokenController::class, 'destroy']);

    Route::get('/meters', [MeterController::class, 'index']);
    Route::post('/meters', [MeterController::class, 'store']);
    Route::get('/meters/{meter}', [MeterController::class, 'show']);
    Route::put('/meters/{meter}', [MeterController::class, 'update']);
    Route::delete('/meters/{meter}', [MeterController::class, 'destroy']);

    Route::get('/meters/{meter}/readings', [MeterReadingController::class, 'index']);
    Route::post('/meters/{meter}/readings', [MeterReadingController::class, 'store']);
    Route::put('/meters/{meter}/readings/{reading}', [MeterReadingController::class, 'update']);
    Route::delete('/meters/{meter}/readings/{reading}', [MeterReadingController::class, 'destroy']);

    Route::get('/meters/{meter}/statistics', [MeterStatisticsController::class, 'show']);
});
