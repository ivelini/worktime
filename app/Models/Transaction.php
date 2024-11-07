<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Отметка приход / уход в системе
 *
 * @property int $id
 * @property int $emp_id
 * @property Carbon $punch_time //Временная отметка
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
     * Сотрудник
     */
    public function emploee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'empl_id', 'id');
    }
}
