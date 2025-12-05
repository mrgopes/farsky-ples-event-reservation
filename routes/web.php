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

Route::post('event/{event:url_slug}/order', [OrderController::class, 'store'])->name('order.store');
Route::get('event/{event:url_slug}/order', [OrderController::class, 'create'])->name('order.create');
Route::get('event/{event:url_slug}', [EventController::class, 'show'])->name('event.show');

Route::get('order/{order:url_slug}', [OrderController::class, 'sent'])->name('order.sent');

Route::get('order/{order:url_slug}/confirm', [OrderController::class, 'confirm'])->middleware(['auth'])->name('order.confirm');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
