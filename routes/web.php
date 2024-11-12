<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Middleware\UserTypeMiddleware;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return to_route('report.timesheet');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('report')->group(function () {
        Route::get('timesheet', [ReportController::class, 'timeSheet'])->name('report.timesheet');

        Route::get('payrollheet', [ReportController::class, 'payrollSheet'])
            ->middleware(UserTypeMiddleware::class. ':' .User::$ADMIN)
            ->name('report.payrollsheet');

    });
});




require __DIR__.'/auth.php';
