<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use Illuminate\Http\Request;

Route::get('/', [MovieController::class, 'index'])->name('movies.index');
Route::get('/api/suggestions', [MovieController::class, 'suggestions']);
Route::get('/movie/{title}', [MovieController::class, 'show'])->name('movies.show');
Route::get('/category/{category}', [MovieController::class, 'category'])->name('movies.category');

// Registration form
Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// Registration POST
Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register.post');

// Login form
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Login POST
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login.post');

// Logout
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', function () {
        return view('auth.profile');
    })->name('profile');
});
