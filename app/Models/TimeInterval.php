<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Временной интервал для смены
 *
 * @property int $id
 * @property string $alias              //Название
 * @property Carbon $in_time            // Время начала
 * @property int $work_time_duration    // Продолжительность
 * @property bool $early_in             //Признак смещения слева
 * @property int $min_early_in          //Смещение слева обычной работы
 * @property bool $late_out             //Признак смещения справа
 * @property int $min_late_out          //Смещение справа обычной работы
 */
class TimeInterval extends Model
{
    protected $connection = 'biotime';
    protected $table = 'att_timeinterval';

    protected function casts(): array
    {
        return [
            'in_time' => 'datetime',
            'early_in' => 'boolean',
            'late_out' => 'boolean',
            'work_time_duration' => 'integer',
            'min_early_in' => 'integer',
            'min_late_out' => 'integer',
        ];
    }

    /**
     * Перерывы
     */
    public function breaktimes(): BelongsToMany
    {
        return $this->belongsToMany(Breaktime::class, 'att_timeinterval_break_time', 'timeinterval_id', 'breaktime_id');
    }
}
