<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SheetTimeController;
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

        Route::get('payrollheet', fn() => redirect()->route('report.payrollsheet'));
        Route::get('payrollsheet', [ReportController::class, 'payrollSheet'])
            ->middleware(UserTypeMiddleware::class. ':' .User::$ADMIN)
            ->name('report.payrollsheet');

        Route::get('export/timesheet', [ReportController::class, 'exportSheetTime'])->name('report.export.timesheet');
        Route::get('export/payroll', [ReportController::class, 'exportPayroll'])->name('report.export.payroll');
    });

    Route::prefix('sheet-time')->group(function () {
        Route::post('set-night-shift', [SheetTimeController::class, 'setNightShift'])->name('sheet-time.set-night-shift');
        Route::post('set-day-shift', [SheetTimeController::class, 'setDayShift'])->name('sheet-time.set-day-shift');
    });
});

require __DIR__.'/auth.php';
