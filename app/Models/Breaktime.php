<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Перерыв
 *
 * @property int $id
 * @property string $alias              //Название
 * @property Carbon $period_start       //Время начала
 * @property int $duration              //Продолжительность

 */
class Breaktime extends Model
{
    protected $connection = 'biotime';
    protected $table = 'att_breaktime';

    protected function casts(): array
    {
        return [
            'period_start' => 'datetime',
            'duration' => 'integer',
        ];
    }
}
