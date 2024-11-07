<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


/**
 * Смена
 *
 * @property int $id
 * @property string $alias      //Название
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
}
