<?php

namespace App\Models;

use App\Casts\StartTimeIntervalCast;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Перерыв
 *
 * @property int $id
 * @property string $alias              //Название
 * @property Carbon $period_start       //Время начала
 * @property Carbon $period_end       //Время окончания перерыва
 * @property int $duration             //Продолжительность
 */
class Breaktime extends Model
{
    protected $connection = 'biotime';
    protected $table = 'att_breaktime';

    protected function casts(): array
    {
        return [
            'period_start' => StartTimeIntervalCast::class,
            'duration' => 'integer',
        ];
    }

    protected function periodEnd(): Attribute
    {
        return Attribute::get(fn() => $this->period_start->addMinutes($this->duration));
    }
}
