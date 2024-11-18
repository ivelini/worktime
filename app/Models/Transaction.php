<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Отметка приход / уход в системе
 *
 * @property int $id
 * @property int $emp_id
 * @property Carbon $punch_time         //Временная отметка
 * @property string $time_to_string     //Время строкой
 * @property string $date_to_string     //Дата строкой
 */
class Transaction extends Model
{
    protected $connection = 'biotime';
    protected $table = 'iclock_transaction';

    protected function casts(): array
    {
        return [
            'punch_time' => 'datetime'
        ];
    }

    /**
     * Время строкой
     */
    protected function timeToString(): Attribute
    {
        return Attribute::get(fn() => $this->punch_time->format('H:i'));
    }

    /**
     * Дата строкой
     */
    protected function dateToString(): Attribute
    {
        return Attribute::get(fn() => $this->punch_time->format('Y-m-d'));
    }

    /**
     * Сотрудник
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'empl_id', 'id');
    }
}
