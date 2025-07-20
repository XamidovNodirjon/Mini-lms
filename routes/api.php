<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\PaymentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/groups/{group}/students', [PaymentController::class, 'getStudentsByGroup'])->name('api.groups.students');
Route::get('/students/{student}/debts', [PaymentController::class, 'getStudentDebts'])->name('api.students.debts');
Route::post('/payments/get-student-group-debt', [PaymentController::class, 'getStudentGroupDebt']);
