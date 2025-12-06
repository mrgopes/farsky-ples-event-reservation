<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CollaboratorController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\EventController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('event/create', [EventController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('event.create');
Route::post('event', [EventController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('event.store');
Route::get('event/{event:url_slug}/manage', [EventController::class, 'manage'])
    ->middleware(['auth', 'verified'])
    ->name('event.manage');
Route::get('event/{event:url_slug}/edit', [EventController::class, 'edit'])
    ->middleware(['auth', 'verified'])
    ->name('event.edit');
Route::put('event/{event:url_slug}', [EventController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('event.update');

// Ticket management routes
Route::post('event/{event:url_slug}/ticket', [TicketController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('ticket.store');
Route::put('event/{event:url_slug}/ticket/{ticket}', [TicketController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('ticket.update');
Route::delete('event/{event:url_slug}/ticket/{ticket}', [TicketController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('ticket.destroy');

// Collaborator management routes
Route::post('event/{event:url_slug}/collaborator', [CollaboratorController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('collaborator.store');
Route::put('event/{event:url_slug}/collaborator/{user}', [CollaboratorController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('collaborator.update');
Route::delete('event/{event:url_slug}/collaborator/{user}', [CollaboratorController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('collaborator.destroy');

Route::post('event/{event:url_slug}/order', [OrderController::class, 'store'])->name('order.store');
Route::get('event/{event:url_slug}/order', [OrderController::class, 'create'])->name('order.create');
Route::get('event/{event:url_slug}', [EventController::class, 'show'])->name('event.show');

Route::get('order/{order:url_slug}', [OrderController::class, 'sent'])->name('order.sent');

Route::get('order/{order:url_slug}/confirm', [OrderController::class, 'confirm'])->middleware(['auth'])->name('order.confirm');
Route::post('order/{order:url_slug}/confirm', [OrderController::class, 'confirmOrder'])->middleware(['auth'])->name('order.confirm.post');
Route::post('order/{order:url_slug}/cancel', [OrderController::class, 'cancelOrder'])->middleware(['auth'])->name('order.cancel');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
