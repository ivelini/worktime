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
    use Queueable, HasWorkTimeTrait;

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
        $workMinTime = $this->minTimeWork($nightTimeInterval, $minTime);
        //Вычисляем время ухода
        $workMaxTime = $this->maxTimeWork($nightTimeInterval, $maxTime);
        //Расчет рабочего времени
        $workDuration = $this->durationWork($workMinTime, $workMaxTime);


        //Записываем смену в $sheetTime
        $this->sheetTime->update([
            'is_night' => true,
            'min_time' => $minTime->format('H:i'),
            'max_time' => $workDuration < 16 ? $maxTime->format('H:i') : '',
            'work_min_time' => $workMinTime->format('H:i'),
            'work_max_time' => $workMaxTime->format('H:i'),
            'duration' => $workDuration < 16 ? $workDuration : 0,
            'corrected' => null,
        ]);
    }
}
