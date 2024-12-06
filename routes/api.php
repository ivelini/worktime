<?php

use App\Http\Controllers\SheetTimeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/sheet-time', [SheetTimeController::class, 'store']);
Route::post('/sheet-time/{sheet_time}', [SheetTimeController::class, 'update']);
Route::delete('/sheet-time/{sheet_time}', [SheetTimeController::class, 'destroy']);
