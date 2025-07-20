<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use \App\Http\Controllers\TeacherController;
use \App\Http\Controllers\StudentController;
use \App\Http\Controllers\GroupController;
use \App\Http\Controllers\DebtController;
use \App\Http\Controllers\PaymentController;
use \App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
Route::resource('teachers', TeacherController::class);
Route::resource('students', StudentController::class);
Route::resource('groups', GroupController::class);
Route::resource('debts', DebtController::class);

Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
Route::post('/payments/store', [PaymentController::class, 'store'])->name('payments.store');
Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');


Route::resource('payments', PaymentController::class);

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
