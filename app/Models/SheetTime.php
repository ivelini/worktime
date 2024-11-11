<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
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
 * @property int $duration
 * @property Carbon $date
 */
class SheetTime extends Model
{
    protected $table = 'sheet_time';

    protected $fillable = [
        'emp_id',
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
}
