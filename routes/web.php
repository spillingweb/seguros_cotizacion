<?php

use Illuminate\Support\Facades\Route;

// Catch-all para la SPA Vue
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');

