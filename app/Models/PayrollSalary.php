<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Структура месячной оплаты сотруднику
 *
 * @property int $id
 * @property int $employee_id
 * @property Carbon $effective_date
 * @property int $salary_amount //Оклад
 *
 * @property ?int $per_pay_hour
 * @property Collection $advances
 */
class PayrollSalary extends Model
{
    protected $connection = 'biotime';
    protected $table = 'payroll_salarystructure';

    //Тип аванса - ежемесячный
    public static $ADVANCE_AMOUNT_EVERYMONTH = 'Ежемесячный';

    public function casts(): array
    {
        return [
            'effective_date' => 'datetime',
            'salary_amount' => 'integer',
        ];
    }

    /**
     * Оплата за час работы
     */
    public function payPerHour(): Attribute
    {
        return Attribute::get(function () {

            $formula = DB::connection('biotime')
                ->table('payroll_increasementformula')
                ->where('id', DB::connection('biotime')
                    ->table('payroll_salarystructure_increasementformula')
                    ->where('salarystructure_id', $this->id)
                    ->first()
                    ?->increasementformula_id
                )
                ->first()
                ?->formula;

            return !empty($formula) ? (int) substr($formula, strpos($formula, '*') + 1) : null;
        });
    }

    /**
     * Авансы
     */
    public function advances(): Attribute
    {
        return Attribute::get(function () {
            return DB::connection('biotime')
                ->table('payroll_salaryadvance')
                ->where('employee_id', $this->employee_id)
                ->orderBy('advance_time', 'DESC')
                ->get()
                ->map(fn($item) => [
                    'id' => $item->id,
                    'advance_amount' => (int) $item->advance_amount,
                    'advance_time' => Carbon::parse($item->advance_time),
                    'is_every_month' => self::$ADVANCE_AMOUNT_EVERYMONTH == $item->advance_remark,
                ]);
        });
    }
}
