<?php

namespace App\Jobs;

use App\Models\Breaktime;
use App\Models\SheetTime;
use App\Models\TimeInterval;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Пересчет ночной смены
 */
class RecalculateNightSheetTimeJob implements ShouldQueue
{
    use Queueable;

    /**
     * Пересчет ночной смены
     *
     */
    public function __construct(public SheetTime $sheetTime)
    {}

    public function handle(): void
    {
        //Получаем интервал ночной смены и перерыв
        $nightTimeInterval = new TimeInterval(config('shift.night.time_interval'));
        $nightTimeInterval->in_time = $nightTimeInterval->in_time->setDate($this->sheetTime->date->year, $this->sheetTime->date->month, $this->sheetTime->date->day);

        //Крайняя правая отметка за текущий день в $sheetTime
        $minTime = Transaction::query()
            ->where('iclock_transaction.emp_id', '=', $this->sheetTime->emp_id)
            ->whereBetween('punch_time', [
                $this->sheetTime->date->startOfDay()->format('Ymd H:i'),
                $this->sheetTime->date->endOfDay()->format('Ymd H:i')
            ])
            ->get()
            ->sortByDesc(fn(Transaction $transaction) => $transaction->punch_time)
            ->first()
            ?->punch_time;

        //Крайняя левая отметка за следующий день от $sheetTime
        $maxTime = Transaction::query()
            ->where('iclock_transaction.emp_id', '=', $this->sheetTime->emp_id)
            ->whereBetween('punch_time', [
                $this->sheetTime->date->addDay()->startOfDay()->format('Ymd H:i'),
                $this->sheetTime->date->addDay()->endOfDay()->format('Ymd H:i')
            ])
            ->get()
            ->sortBy(fn(Transaction $transaction) => $transaction->punch_time)
            ->first()
            ?->punch_time;

        if(empty($minTime) || empty($maxTime)) {
            return;
        }

        //Вычисляем время прихода
        $workMinTime = match (true) {
            //Если время прихода меньше смещения слева или Если время прихода больше времени началом работы
            $minTime < $nightTimeInterval->min_early_in || $minTime > $nightTimeInterval->in_time => $minTime->minute <= 10
                ? $minTime->clone()->setMinutes(0)
                : $minTime->clone()->addHour()->setMinutes(0),

            //Если время прихода лежит в рамках между смещением слева и началом работы
            ($minTime >= $nightTimeInterval->min_early_in && $minTime <= $nightTimeInterval->in_time) => $nightTimeInterval->in_time,
            default => null,
        };

        //Вычисляем время ухода
        $workMaxTime = match(true) {
            //Если время ухода меньше времени конца смены или Если время ухода больше смещения справа
            ($maxTime < $nightTimeInterval->end_time || $maxTime > $nightTimeInterval->min_late_out) => $maxTime->minute >= 50
                ? $maxTime->clone()->addHour()->setMinutes(0)
                : $maxTime->clone()->setMinutes(0),

            //Если время ухода больше времени конца смены, но меньше смещения справа
            $maxTime >= $nightTimeInterval->end_time && $maxTime <= $nightTimeInterval->min_late_out => $nightTimeInterval->end_time,
            default => null,
        };

        //Расчет рабочего времени
        $workDuration = $workMaxTime->diff($workMinTime)->hours;

        //Записываем смену в $sheetTime
        $this->sheetTime->update([
            'is_night' => true,
            'min_time' => $minTime->format('H:i'),
            'max_time' => $workDuration < 16 ? $maxTime->format('H:i') : '',
            'work_min_time' => $workMinTime->format('H:i'),
            'work_max_time' => $workMaxTime->format('H:i'),
            'duration' => $workDuration < 16 ? $workDuration : 0,
        ]);
    }
}
