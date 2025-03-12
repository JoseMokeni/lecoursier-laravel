<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;

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

    });
}

