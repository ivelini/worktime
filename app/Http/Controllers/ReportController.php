<?php

namespace App\Http\Controllers;

use App\Exports\PayrollExport;
use App\Exports\SheetTimeExport;
use App\Models\Employee;
use App\Models\SheetTime;
use App\Repositories\SheetTimeRepository;
use App\Repositories\TransactionsRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function timeSheet(Request $request)
    {
        $startAt = !empty($request->input('start_at'))
            ? Carbon::parse($request->input('start_at'))->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $endAt = !empty($request->input('end_at'))
            ? Carbon::parse($request->input('end_at'))->endOfDay()
            : now()->endOfDay();

        $sheetTimeRows = SheetTimeRepository::getReportSheetTime($startAt, $endAt);

        return view('timesheet', compact('sheetTimeRows', 'startAt', 'endAt'));
    }

    public function payrollSheet(Request $request)
    {
        $startAt = !empty($request->input('start_at'))
            ? Carbon::parse($request->input('start_at'))->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $endAt = !empty($request->input('end_at'))
            ? Carbon::parse($request->input('end_at'))->endOfDay()
            : now()->endOfMonth()->endOfDay();

        $salaryPayEmployees = SheetTimeRepository::getPayEmployee($startAt, $endAt);

        $fullAdvance = $salaryPayEmployees->sum('advance');
        $fullSalaryPay = $salaryPayEmployees->sum('salary_pay');

        $groupedPayEmployees = $salaryPayEmployees->groupBy('department');

        return view('payrollsheet', compact(
            'groupedPayEmployees',
            'fullAdvance',
            'fullSalaryPay',
            'startAt',
            'endAt'));
    }

    public function exportSheetTime(Request $request)
    {
        $startAt = !empty($request->input('start_at'))
            ? Carbon::parse($request->input('start_at'))->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $endAt = !empty($request->input('end_at'))
            ? Carbon::parse($request->input('end_at'))->endOfDay()
            : now()->endOfMonth()->endOfDay();

        return Excel::download(
            new SheetTimeExport($startAt, $endAt),
            'worktime_' .$startAt->format('Y-m-d'). '_' .$endAt->format('Y-m-d'). '.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    public function exportPayroll(Request $request)
    {
        $startAt = !empty($request->input('start_at'))
            ? Carbon::parse($request->input('start_at'))->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $endAt = !empty($request->input('end_at'))
            ? Carbon::parse($request->input('end_at'))->endOfDay()
            : now()->endOfMonth()->endOfDay();

        return Excel::download(
            new PayrollExport($startAt, $endAt),
            'payroll_' .$startAt->format('Y-m-d'). '_' .$endAt->format('Y-m-d'). '.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }
}
