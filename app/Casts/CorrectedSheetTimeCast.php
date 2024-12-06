<?php

namespace App\Casts;

use App\Models\SheetTimeDto\CorrectedDto;
use App\Models\SheetTimeDto\IntervalDto;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class CorrectedSheetTimeCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if($value instanceof CorrectedDto) {
            return $value;
        } else {

            $data = json_decode($value, true);

            if(empty($data)) {
                return new CorrectedDto('', new IntervalDto('', ''), new IntervalDto( '', ''));
            } else {

                return new CorrectedDto(
                    $data['userName'] ?? '',
                    new IntervalDto($data['modify']['start'] ?? '', $data['modify']['end']) ?? '',
                    new IntervalDto($data['original']['start'] ?? '', $data['original']['end'] ?? '')
                );
            }
        }
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return json_encode($value);
    }
}
