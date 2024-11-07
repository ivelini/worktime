<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Назначенная смена сотруднику
 *
 * @property int $id
 * @property int $employee_id
 * @property int $shift_id
 * @property Carbon $start_date      //Начало
 * @property Carbon $end_date        //Окончание
 */
class EmployeeShift extends Pivot
{
    protected $connection = 'biotime';
    protected $table = 'att_attschedule';
    public $incrementing = true;

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }
}
