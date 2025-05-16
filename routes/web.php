<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use Illuminate\Http\Request;

Route::get('/', [MovieController::class, 'index'])->name('movies.index');
Route::get('/api/suggestions', [MovieController::class, 'suggestions']);
Route::get('/movie/{title}', [MovieController::class, 'show'])->name('movies.show');
Route::get('/category/{category}', [MovieController::class, 'category'])->name('movies.category');

// صفحة الأفلام الأكثر شهرة وتقييماً
Route::get('/popular', [\App\Http\Controllers\MovieController::class, 'popular'])->name('movies.popular');

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
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar/delete', [App\Http\Controllers\ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
});
