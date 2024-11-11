<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schedule;


/**
 * Смена
 *
 * @property int $id
 * @property string $alias              //Название
 * @property ?EmployeeShift $graph     //График смен для сотрудника
 * @property Collection $schedules
 * @property TimeInterval $timeInterval
 */
class Shift extends Model
{
    protected $connection = 'biotime';
    protected $table = 'att_attshift';

    /**
     * Расписания
     */
    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(TimeInterval::class, 'att_shiftdetail', 'shift_id', 'time_interval_id');
    }

    /**
     * Расписание для смены
     */
    protected function timeInterval(): Attribute
    {
        return Attribute::get(fn () => $this->schedules->first());
    }
}
