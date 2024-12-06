<?php

namespace App\Models\SheetTimeDto;

use App\Casts\CorrectedSheetTimeCast;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Castable;

class IntervalDto
{
    public function __construct(public string $start, public string $end)
    {
        if(!empty($this->start)) {
            $this->start = Carbon::parse($start)->format('H:i');
        }

        if(!empty($this->end)) {
            $this->end = Carbon::parse($end)->setSeconds(0)->format('H:i');
        }
    }
}
