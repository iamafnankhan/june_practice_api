<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FlightCategoriesController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\UserController;


$requestsPerMinute = ENV("REQUESTS_PER_MINUTE", 1000);


Route::group(['middleware' => ["auth:api", "throttle:$requestsPerMinute,1"]], function () {
    Route::prefix('flight-categories')->group(function () {
        Route::get('/', [FlightCategoriesController::class, 'index']);
        Route::post('/', [FlightCategoriesController::class, 'store']);
        Route::get('{id}', [FlightCategoriesController::class, 'show']);
        Route::put('/{id}', [FlightCategoriesController::class, 'update']);
        Route::delete('/{id}', [FlightCategoriesController::class, 'destroy']);
    });

    Route::prefix('flight')->group(function () {
        Route::get('/', [FlightController::class, 'index']);
        Route::get('{id}', [FlightController::class, 'show']);
        Route::post('/', [FlightController::class, 'store']);
        Route::post('by_law_id/{id}', [FlightController::class, 'update']);
        Route::delete('/{id}', [FlightController::class, 'destroy']);
    });

    Route::prefix('flight-attachments')->group(function () {
        Route::delete('/{id}', [FlightController::class, 'deleteAttachment']);
});

});

Route::post('/register', [UserController::class, 'register']);

Route::post('/login', [UserController::class, 'login']);
