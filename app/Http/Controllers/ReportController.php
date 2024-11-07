<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollSalary;
use App\Models\TimeInterval;
use App\Models\Shift;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct()
    {}
    public function timeSheet(Request $request)
    {
        $payrollsalary = PayrollSalary::query()
            ->where('id', 1039)
            ->first();

        dd(
            $payrollsalary, $payrollsalary->advances
        );
    }
}
