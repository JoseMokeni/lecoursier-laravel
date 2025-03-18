<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            return view('pages/landing');
        })->name('landing');

        Route::get('/hello', function () {
            return 'Hello from Le Coursier Saas (Laravel)';
        });

        // Contact form submission
        Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

        // Privacy Policy route
        Route::get('/privacy-policy', function () {
            return view('pages.privacy');
        })->name('privacy.policy');

        // Registration routes
        Route::get('/register', [RegisterController::class, 'create'])->name('register');
        Route::post('/register', [RegisterController::class, 'store']);

        // Login routes
        Route::get('/login', [LoginController::class, 'create'])->name('login');
        Route::post('/login', [LoginController::class, 'store']);
        Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

    });
}

