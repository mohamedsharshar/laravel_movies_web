<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use Illuminate\Http\Request;

Route::get('/', [MovieController::class, 'index'])->name('movies.index');
Route::get('/api/suggestions', [MovieController::class, 'suggestions']);
Route::get('/movie/{title}', [MovieController::class, 'show'])->name('movies.show');
Route::get('/category/{category}', [MovieController::class, 'category'])->name('movies.category');
