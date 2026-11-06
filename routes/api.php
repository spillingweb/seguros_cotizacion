<?php

use App\Http\Controllers\CountryController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\QuoteController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::get('/countries', [CountryController::class, 'index']);

Route::middleware(HandlePrecognitiveRequests::class)
    ->post('/quotes', [QuoteController::class, 'store']);

Route::get('/quotes/{insurance}', [QuoteController::class, 'show']);
Route::post('/quotes/{insurance}/contract', [QuoteController::class, 'contract']);
Route::get('/quotes/{insurance}/pdf', [QuoteController::class, 'pdf']);

Route::get('/insurances', [InsuranceController::class, 'index']);
Route::get('/insurances/{insurance}', [InsuranceController::class, 'show']);

