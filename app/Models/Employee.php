<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Сотрудник системы учета
 *
 * @property int $status
 * @property int $emp_code
 * @property string $first_name
 * @property string $last_name
 * @property Position $position
 * @property Collection $shifts
 * @property Collection $transactions
 * @property Collection $advances
 * @property Collection $payrolls
 */
class Employee extends Model
{
    protected $connection = 'biotime';
    protected $table = 'personnel_employee';

    public function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }

    /**
     * Должность
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }

    /**
     * Отметки о приходе / уходе
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'emp_id', 'id');
    }

    /**
     * Смены
     */
    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class, 'att_attschedule')
            ->withPivot(['start_date', 'end_date'])
            ->as('graph')
            ->using(EmployeeShift::class);
    }

    /**
     * Схемы ежемесячных оплат сотруднику
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(PayrollSalary::class, 'employee_id', 'id');
    }

    /**
     * Авансы
     */
    public function advances(): HasMany
    {
        return $this->hasMany(Advance::class, 'employee_id', 'id');
    }

    /**
     * Получить аванс на текущий месяц
     */
    public function getCurrentAdvance(Carbon|string $currentDay): Advance|null
    {
        if(!$this->relationLoaded('advances')) {
            $this->load('advances');
        }

        if(!($currentDay instanceof Carbon)) {
            $currentDay = Carbon::parse($currentDay);
        }

        return $this
            ->advances
            ->sortByDesc('advance_time')
            ->first(
                fn(Advance $advance) =>
                    $advance->advance_time->monthOfCentury == $currentDay->monthOfCentury || $advance->advance_remark == Advance::$EVERY_MONTH
            );
    }

    /**
     * Получить смену на текущий день
     */
    public function getCurrentShift(Carbon|string $currentDay): Shift|null
    {
        if(!$this->relationLoaded('shifts')) {
            $this->load('shifts');
        }

        if(!($currentDay instanceof Carbon)) {
            $currentDay = Carbon::parse($currentDay);
        }

        return $this
            ->shifts
            ->first(fn(Shift $shift) => $currentDay >= $shift->graph->start_date && $currentDay <= $shift->graph->end_date);
    }

    /**
     * Получаем схему оплаты на текущий день
     */
    public function getCurrentPayroll(Carbon|string $currentDay): PayrollSalary|null
    {
        if(!$this->relationLoaded('payrolls')) {
            $this->load('payrolls');
        }

        if(!($currentDay instanceof Carbon)) {
            $currentDay = Carbon::parse($currentDay);
        }

        return $this
            ->payrolls
            ->sortByDesc('effective_date')
            ->first(fn(PayrollSalary $payroll) => $currentDay >= $payroll->effective_date);
    }
}
