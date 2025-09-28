<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\EventController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('event/{event:url_slug}', [EventController::class, 'show'])->name('event.show');
Route::get('event/{event:url_slug}/order', [OrderController::class, 'create'])->name('order.create');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
