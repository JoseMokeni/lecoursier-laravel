<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;

Route::get('/', function () {
    return view('pages/landing');
});

Route::get('/hello', function () {
    return 'Hello from Le Coursier Saas (Laravel)';
});

// Contact form submission
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
