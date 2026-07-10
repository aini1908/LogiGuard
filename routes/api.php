<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\RiskAssesmentController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\CountryDashboardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/countries', [CountryController::class, 'index']);
Route::post('/risk/analyze', [RiskAssesmentController::class, 'analyze']);
Route::get('/ports', [PortController::class, 'index']);
Route::get('/countries/{code}/detail', [CountryDashboardController::class, 'getDetail']);