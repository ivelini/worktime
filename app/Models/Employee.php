<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Сотрудник системы учета
 *
 * @property int $status
 * @property string $first_name
 * @property string $last_name
 * @property Position $position
 * @property Collection $shifts
 * @property Collection $transactions
 */
class Employee extends Model
{
    protected $connection = 'biotime';
    protected $table = 'personnel_employee';

    public function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }

    /**
     * Должность
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }

    /**
     * Отметки о приходе / уходе
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'emp_id', 'id');
    }

    /**
     * Смены
     */
    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class, 'att_attschedule')
            ->withPivot(['start_date', 'end_date'])
            ->as('schedule')
            ->using(EmployeeShift::class);
    }
}
