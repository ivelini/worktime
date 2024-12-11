<?php

namespace App\Models\SheetTimeDto;

use App\Casts\CorrectedSheetTimeCast;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Castable;

class CorrectedDto implements Castable
{
    public bool $is_isset;
    public function __construct(
        public string $userName,
        public IntervalDto $modify,
        public IntervalDto $original,
        public ?string $comment = null,
    ){
        $this->is_isset = $this->userName != '';
    }

    public static function castUsing(array $arguments)
    {
        return CorrectedSheetTimeCast::class;
    }
}
