<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Должность сотрудника системы учета
 *
 * @property string $alias
 */
class Position extends Model
{
    protected $connection = 'biotime';
    protected $table = 'personnel_position';
}
