<?php

use App\Http\Controllers\NewsController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [NewsController::class, 'welcome'])->name('home');
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{id}', [NewsController::class, 'show'])->name('news.show');

// Tracking API Proxy Endpoint
Route::get('/api/track', [TrackingController::class, 'track'])->name('api.track');
