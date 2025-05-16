<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use Illuminate\Http\Request;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Auth\Middleware\Authenticate;

Route::get('/', [MovieController::class, 'index'])->name('movies.index');
Route::get('/api/suggestions', [MovieController::class, 'suggestions']);
Route::get('/movie/{title}', [MovieController::class, 'show'])->name('movies.show');
Route::get('/category/{category}', [MovieController::class, 'category'])->name('movies.category');

// صفحة الأفلام الأكثر شهرة وتقييماً
Route::get('/popular', [\App\Http\Controllers\MovieController::class, 'popular'])->name('movies.popular');

// Contact Us page
Route::get('/contact', function() {
    return view('contact');
})->name('contact');

// Contact form POST route
Route::post('/contact', [\App\Http\Controllers\MovieController::class, 'contact'])->name('contact.send');

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

// Movie AI Assistant page
Route::get('/ai', [\App\Http\Controllers\MovieController::class, 'aiAssistant'])->name('movies.ai');
// AI chat API endpoint
Route::post('/ai/ask', [\App\Http\Controllers\MovieController::class, 'aiAsk']);

Route::middleware([Authenticate::class])->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar/delete', [App\Http\Controllers\ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
});

Route::middleware([Authenticate::class, AdminMiddleware::class])->prefix('admin')->group(function () {
    Route::get('/', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
    Route::delete('/users/{id}', [\App\Http\Controllers\AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::get('/contacts', [\App\Http\Controllers\AdminController::class, 'contacts'])->name('admin.contacts');
    Route::delete('/contacts/{id}', [\App\Http\Controllers\AdminController::class, 'deleteContact'])->name('admin.contacts.delete');
});

Route::get('/admin-test', function() {
    return 'You are in the admin test page!';
})->middleware([Authenticate::class, AdminMiddleware::class]);
