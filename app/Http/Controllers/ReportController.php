<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SheetTime;
use App\Repositories\TransactionsRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

        $sheetTimeRows = SheetTime::query()
                ->selectRaw("
                    sheet_time.*,
                    SUM(sheet_time.duration) OVER (PARTITION BY sheet_time.emp_id) as month_duration
                ")
                ->whereBetween('date', [$startAt, $endAt])
                ->whereNotNull(['min_time', 'work_min_time'])
                ->orderBy('emp_code')
                ->orderBy('date')
                ->get()
                ->groupBy(fn($employee) => $employee->emp_code. ', ' .$employee->surname. ' ' .$employee->name. ', ' .$employee->position)
                ->map(function ($groupedMonthEmployee) use ($startAt, $endAt) {

                    /** @var Collection $groupedMonthEmployee */
                    $data = collect();
                    $i = clone $startAt;
                    for($i->dayOfCentury; $i->dayOfCentury <= $endAt->dayOfCentury; $i->addDay()) {
                        $sheetTimeCurrenDay = $groupedMonthEmployee->first(fn(SheetTime $sheetTime) => $i == $sheetTime->date);

                        $prepareSheetTimeCurrentDay = [
                            'date' => $i->format('d-m-Y'),
                            'dey_of_the_week' => $i->localeDayOfWeek,
                            'schedule_name' => $groupedMonthEmployee[0]->schedule_name,
                        ];

                        if(!empty($sheetTimeCurrenDay)) {
                            $prepareSheetTimeCurrentDay['min_time'] = $sheetTimeCurrenDay->prepare_min_time;
                            $prepareSheetTimeCurrentDay['max_time'] = $sheetTimeCurrenDay->prepare_max_time;
                            $prepareSheetTimeCurrentDay['duration'] = $sheetTimeCurrenDay->duration;
                        } else {
                            $prepareSheetTimeCurrentDay['min_time'] = '';
                            $prepareSheetTimeCurrentDay['max_time'] = '';
                            $prepareSheetTimeCurrentDay['duration'] = '';
                        }
                        $data->push($prepareSheetTimeCurrentDay);
                    }

                    $data->push([
                            'date' => 'Статистика',
                            'dey_of_the_week' => '',
                            'schedule_name' => '',
                            'min_time' => '',
                            'max_time' => '',
                            'duration' => $groupedMonthEmployee[0]->month_duration
                        ]
                    );
                    return $data;
                });

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

        $salaryPayEmployees = SheetTime::query()
                ->selectRaw("
                    MAX(emp_id) as emp_id,
                    MAX(emp_code) as emp_code,
                    concat(MAX(surname), ' ', MAX(name)) as fio,
                    MAX(position) as position,
                    SUM(duration) as month_duration,
                    MAX(salary_amount) as salary_amount,
                    MAX(advance) as advance,
                    MAX(per_pay_hour) as per_pay_hour,
                    GREATEST(
                        (COALESCE(MAX(salary_amount), 0) + COALESCE(SUM(duration), 0) * COALESCE(MAX(per_pay_hour), 0)) - COALESCE(MAX(advance), 0),
                        0
                    ) as salary_pay
                ")
                ->whereBetween('date', [$startAt, $endAt])
                ->groupBy('emp_id')
                ->orderBy('emp_id')
                ->get();

        $fullAdvance = $salaryPayEmployees->sum('advance');
        $fullSalaryPay = $salaryPayEmployees->sum('salary_pay');

        return view('payrollsheet', compact(
            'salaryPayEmployees',
            'fullAdvance',
            'fullSalaryPay',
            'startAt',
            'endAt'));
    }
}
