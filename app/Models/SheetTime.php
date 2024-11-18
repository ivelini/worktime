<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schedule;

/**
 * Проставленная смена за день
 *
 * @property int $emp_id
 * @property string $surname
 * @property string $name
 * @property string $schedule_name
 * @property string $position
 * @property string $min_time
 * @property string $max_time
 * @property string $prepare_min_time
 * @property string $prepare_max_time
 * @property string $work_min_time
 * @property string $work_max_time
 * @property ?Carbon $work_min_time_carbon
 * @property ?Carbon $work_max_time_carbon
 * @property int $duration
 * @property Carbon $date
 * @property Employee $employee
 */
class SheetTime extends Model
{
    protected $table = 'sheet_time';

    protected $fillable = [
        'emp_id',
        'emp_code',
        'surname',
        'name',
        'date',
        'schedule_name',
        'position',
        'min_time',
        'max_time',
        'work_min_time',
        'work_max_time',
        'duration',
        'advance',
        'salary_amount',
        'per_pay_hour',
        'is_night',
    ];

    protected function casts()
    {
        return [
            'date' => 'datetime'
        ];
    }

    protected function prepareMinTime(): Attribute
    {
        return Attribute::get(fn() => !empty($this->min_time)
            ? Carbon::parse($this->min_time)->format('H:i')
            : ''
        );
    }

    protected function prepareMaxTime(): Attribute
    {
        return Attribute::get(fn() => !empty($this->max_time)
            ? Carbon::parse($this->max_time)->format('H:i')
            : ''
        );
    }

    protected function workMinTimeCarbon(): Attribute
    {
        return Attribute::get(fn() => !empty($this->work_min_time)
            ? Carbon::parse($this->work_min_time)->setDate($this->date->year, $this->date->month, $this->date->day)
            : null
        );
    }

    protected function workMaxTimeCarbon(): Attribute
    {
        return Attribute::get(fn() => !empty($this->work_max_time)
            ? Carbon::parse($this->work_max_time)->setDate($this->date->year, $this->date->month, $this->date->day)
            : null
        );
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'emp_id', 'id');
    }
}
