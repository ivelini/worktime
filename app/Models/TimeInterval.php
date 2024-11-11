<?php

namespace App\Models;

use App\Casts\MinEarlyInCast;
use App\Casts\MinEarlyOutCast;
use App\Casts\StartTimeIntervalCast;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * Временной интервал для смены
 *
 * @property int $id
 * @property string $alias              //Название
 * @property Carbon $in_time            //Время начала смены
 * @property Carbon $end_time           //Время окончания смены
 * @property int $work_time_duration    //Продолжительность
 * @property bool $early_in             //Признак смещения слева
 * @property Carbon $min_early_in       //Смещение слева обычной работы
 * @property bool $late_out             //Признак смещения справа
 * @property Carbon $min_late_out       //Смещение справа обычной работы
 * @property Collection $breaktimes
 * @property ?Breaktime $breaktime
 */
class TimeInterval extends Model
{
    protected $connection = 'biotime';
    protected $table = 'att_timeinterval';

    protected function casts(): array
    {
        return [
            'in_time' => StartTimeIntervalCast::class,
            'early_in' => 'boolean',
            'late_out' => 'boolean',
            'work_time_duration' => 'integer',
            'min_early_in' => MinEarlyInCast::class,
            'min_late_out' => MinEarlyOutCast::class,
        ];
    }

    /**
     * Перерывы
     */
    public function breaktimes(): BelongsToMany
    {
        return $this->belongsToMany(Breaktime::class, 'att_timeinterval_break_time', 'timeinterval_id', 'breaktime_id');
    }

    /**
     * Время окончания смены
     */
    protected function endTime(): Attribute
    {
        return Attribute::get(fn() => (clone $this->in_time)->addMinutes($this->work_time_duration));
    }

    protected function breaktime(): Attribute
    {
        return Attribute::get(fn() => $this->breaktimes->first());
    }
}
