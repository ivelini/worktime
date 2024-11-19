<?php

namespace App\Repositories;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TransactionsRepository
{
    /**
     * Получение крайних отметок за день в интервале дней, сгруппированных по сотрудникам.
     *
     * @param Carbon|string $startAt
     * @param Carbon|string $endAt
     * @return Collection
     */
    public static function getGroupedUserAndDayIntoMaxAndMinPunchTimePoint(Carbon|string $startAt, Carbon|string $endAt, int $enpCode = null): Collection
    {
        $startAt = self::typeCastCarbon($startAt);
        $endAt = self::typeCastCarbon($endAt);

        return DB::connection('biotime')
            ->table('iclock_transaction')
            ->selectRaw("
                iclock_transaction.emp_id,
                max(iclock_transaction.emp_code) as emp_code,
                max(personnel_employee.last_name) as surname,
                max(personnel_employee.first_name) as name,
                max(personnel_position.position_name) as position,
                CAST(iclock_transaction.punch_time as date) as date,
                MIN(CAST(iclock_transaction.punch_time as time)) as min_time,
                MAX(CAST(iclock_transaction.punch_time as time)) as max_time
            ")
            ->join('personnel_employee', 'iclock_transaction.emp_id', '=', 'personnel_employee.id')
            ->join('personnel_position', 'personnel_employee.position_id', '=', 'personnel_position.id')
            ->whereExists(function (Builder $query) {
                $query->select('id')
                    ->from('att_attschedule')
                    ->whereColumn('att_attschedule.employee_id', 'iclock_transaction.emp_id');
            })
            ->whereBetween('punch_time', [$startAt->startOfDay()->format('Ymd H:i'), $endAt->endOfDay()->format('Ymd H:i')])
            ->when(isset($enpCode), fn(Builder $query) => $query->where('iclock_transaction.emp_code', '=', $enpCode))
            ->groupByRaw("iclock_transaction.emp_id, CAST(iclock_transaction.punch_time as date)")
            ->orderBy('iclock_transaction.emp_id')
            ->get();
    }

    /**
     * Приводим дату к типу Carbon
     */
    private static function typeCastCarbon(Carbon|string $date): Carbon
    {
        return !($date instanceof Carbon) ? Carbon::parse($date) : $date;
    }
}
