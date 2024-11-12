<?php

namespace App\Models;

use App\Casts\SalaryAmountCast;
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
 * @property Carbon $effective_date     //Дата начала начисления
 * @property int $salary_amount         //Оклад
 *
 * @property ?int $pay_per_hour         //Оплата за час работы
 */
class PayrollSalary extends Model
{
    protected $connection = 'biotime';
    protected $table = 'payroll_salarystructure';

    public function casts(): array
    {
        return [
            'effective_date' => 'datetime',
            'salary_amount' => SalaryAmountCast::class,
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
}
