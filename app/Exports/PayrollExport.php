<?php

namespace App\Exports;

use App\Repositories\SheetTimeRepository;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class PayrollExport implements FromView
{
    public function __construct(public Carbon $startAt, public Carbon $endAt)
    {}

    public function view(): View
    {
        $salaryPayEmployees = SheetTimeRepository::getPayEmployee($this->startAt, $this->endAt);

        $fullAdvance = $salaryPayEmployees->sum('advance');
        $fullSalaryPay = $salaryPayEmployees->sum('salary_pay');

        return view('exports.payroll', [
            'salaryPayEmployees' => $salaryPayEmployees,
            'fullAdvance' => $fullAdvance,
            'fullSalaryPay' => $fullSalaryPay,
            'startAt' => $this->startAt,
            'endAt' => $this->endAt
        ]);
    }
}
