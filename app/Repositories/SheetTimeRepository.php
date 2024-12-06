<?php

namespace App\Repositories;

use App\Models\SheetTime;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SheetTimeRepository
{
    public static function getReportSheetTime(Carbon $startAt, Carbon $endAt)
    {
        return SheetTime::query()
            ->selectRaw("
                    sheet_time.*,
                    SUM(sheet_time.duration) OVER (PARTITION BY sheet_time.emp_id) as month_duration
                ")
            ->whereBetween('date', [$startAt, $endAt])
            ->where(function (\Illuminate\Database\Eloquent\Builder $query) {
                $query->orWhereNotNull('min_time');
                $query->orWhereNotNull('max_time');
                $query->orWhereNotNull('corrected');
            })
            ->orderBy('emp_code')
            ->orderBy('date')
            ->get()
            ->groupBy(fn($employee) => $employee->emp_code. ', ' .$employee->surname. ' ' .$employee->name. ', ' .$employee->position)
            ->map(function ($groupedMonthEmployee) use ($startAt, $endAt) {

//                dd($groupedMonthEmployee);
                /** @var Collection $groupedMonthEmployee */
                $data = collect();
                $i = clone $startAt;
                for($i->dayOfCentury; $i->dayOfCentury <= $endAt->dayOfCentury; $i->addDay()) {
                    $sheetTimeCurrentDay = $groupedMonthEmployee->first(fn(SheetTime $sheetTime) => $i == $sheetTime->date);

                    $prepareSheetTimeCurrentDay = [
                        'user_id' => Auth::id(),
                        'emp_id' => $groupedMonthEmployee[0]->emp_id,
                        'emp_name' => $groupedMonthEmployee[0]->surname. ' ' .$groupedMonthEmployee[0]->name. ', ' .$groupedMonthEmployee[0]->position,
                        'date' => $i->format('d-m-Y'),
                        'date_for_form' => $i->format('Y-m-d'),
                        'dey_of_the_week' => $i->localeDayOfWeek,
                        'schedule_name' => $groupedMonthEmployee[0]->schedule_name,
                    ];

                    if(!empty($sheetTimeCurrentDay)) {
                        $prepareSheetTimeCurrentDay['sheet_time_id'] = $sheetTimeCurrentDay->id;
                        $prepareSheetTimeCurrentDay['min_time'] = $sheetTimeCurrentDay->prepare_min_time;
                        $prepareSheetTimeCurrentDay['max_time'] = $sheetTimeCurrentDay->prepare_max_time;
                        $prepareSheetTimeCurrentDay['duration'] = $sheetTimeCurrentDay->duration;
                        $prepareSheetTimeCurrentDay['is_night'] = $sheetTimeCurrentDay->is_night;
                        $prepareSheetTimeCurrentDay['corrected'] = $sheetTimeCurrentDay->corrected;
                    } else {
                        $prepareSheetTimeCurrentDay['sheet_time_id'] = null;
                        $prepareSheetTimeCurrentDay['min_time'] = '';
                        $prepareSheetTimeCurrentDay['max_time'] = '';
                        $prepareSheetTimeCurrentDay['duration'] = '';
                        $prepareSheetTimeCurrentDay['is_night'] = false;
                        $prepareSheetTimeCurrentDay['corrected'] = '';
                    }

                    $data->push($prepareSheetTimeCurrentDay);
                }

                $data->push([
                        'date' => 'Статистика',
                        'dey_of_the_week' => '',
                        'schedule_name' => '',
                        'sheet_time_id' => null,
                        'min_time' => '',
                        'max_time' => '',
                        'is_night' => '',
                        'duration' => $groupedMonthEmployee[0]->month_duration,
                        'corrected' => '',
                    ]
                );
                return $data;
            });
    }

    public static function getPayEmployee(Carbon $startAt, Carbon $endAt)
    {
        return SheetTime::query()
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
            ->orderBy('position')
            ->orderBy('fio')
            ->get();
    }
}
