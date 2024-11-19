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

    protected $fillable = [
        'alias',
        'period_start',
        'duration',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => StartTimeIntervalCast::class,
            'duration' => 'integer',
        ];
    }

    protected function periodEnd(): Attribute
    {
        return Attribute::get(fn() => $this->period_start->clone()->addMinutes($this->duration));
    }

    /**
     * Сеттер для period_start
     * Метод устанавливает переданную дату
     */
    public function period_start(Carbon|string $value): self
    {
        if(!($value instanceof Carbon)) {
            $value = Carbon::parse($value);
        }

        $this->period_start = $this->period_start->setDate($value->year, $value->month, $value->day);
        return $this;
    }
}
