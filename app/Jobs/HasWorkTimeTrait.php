<?php

namespace App\Jobs;

use App\Models\Breaktime;
use App\Models\Shift;
use App\Models\TimeInterval;
use Carbon\Carbon;

/**
 *  Расчет рабочего времени
 */
trait HasWorkTimeTrait
{
    /**
     * Расчет времени начала смены
     *
     * @param TimeInterval $timeInterval //Временной интервал смены
     * @param Carbon $punchTime //Временная отметка
     * @return Carbon
     */
    protected function minTimeWork(TimeInterval $timeInterval, Carbon $punchTime): Carbon
    {
        return match(true) {
            //Если время прихода меньше смещения слева или Если время прихода больше времени началом работы
            ($punchTime < $timeInterval->min_early_in || $punchTime > $timeInterval->in_time) => $punchTime->minute <= 15
                ? $punchTime->clone()->setMinutes(0)->setSeconds(0)
                : $punchTime->clone()->addHour()->setMinutes(0)->setSeconds(0),

            //Если время прихода лежит в рамках между смещением слева и началом работы
            ($punchTime >= $timeInterval->min_early_in && $punchTime <= $timeInterval->in_time) => $timeInterval->in_time,
            default => null,
        };
    }

    /**
     * Расчет времени конца смены
     *
     * @param TimeInterval $timeInterval //Временной интервал смены
     * @param Carbon $punchTime //Временная отметка
     * @return Carbon
     */
    protected function maxTimeWork(TimeInterval $timeInterval, Carbon $punchTime): Carbon
    {
        return match(true) {
            //Если время ухода меньше времени конца смены или Если время ухода больше смещения справа
            ($punchTime < $timeInterval->end_time || $punchTime >= $timeInterval->min_late_out) => $punchTime->minute >= 45
                ? $punchTime->clone()->addHour()->setMinutes(0)->setSeconds(0)
                : $punchTime->clone()->setMinutes(0)->setSeconds(0),

            //Если время ухода больше времени конца смены, но меньше смещения справа
            $punchTime >= $timeInterval->end_time && $punchTime < $timeInterval->min_late_out => $timeInterval->end_time,
            default => null,
        };
    }

    /**
     * Расчет продолжительности смены
     *
     * @param Carbon $startTime //Начало смены
     * @param Carbon $endTime   //Конец смены
     * @param Breaktime|null $breaktime //Перерыв, если есть
     * @return int //Количество отработанных часов
     */
    protected function durationWork(Carbon $startTime, Carbon $endTime, Breaktime|null $breaktime = null): int
    {
        return (!empty($breaktime) && $endTime >= $breaktime->period_end)
            ? $endTime->clone()
                ->subMinutes($breaktime->duration)
                ->diff($startTime)
                ->hours
            : $endTime
                ->diff($startTime)
                ->hours;
    }
}
