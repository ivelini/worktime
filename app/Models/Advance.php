<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Аванс
 *
 * @property int $id
 * @property int $emp_id
 * @property int $advance_amount
 * @property Carbon $advance_time //Временная отметка
 * @property string $advance_remark
 */
class Advance extends Model
{
    protected $connection = 'biotime';
    protected $table = 'payroll_salaryadvance';

    public static string $EVERY_MONTH = 'Ежемесячный'; // Аванс ежемесячный

    protected function casts(): array
    {
        return [
            'advance_time' => 'datetime',
            'advance_amount' => 'integer'
        ];
    }

    /**
     * Сотрудник
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
