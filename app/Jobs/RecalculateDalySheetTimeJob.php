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
class RecalculateDalySheetTimeJob implements ShouldQueue
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
        $timeInterval = $this->sheetTime->employee->getCurrentShift($this->sheetTime->date)->timeInterval;
        $timeInterval->in_time = $timeInterval->in_time->setDate($this->sheetTime->date->year, $this->sheetTime->date->month, $this->sheetTime->date->day);

        if(!empty($timeInterval->breaktime)) {
            $timeInterval->breaktime->period_start = $timeInterval->breaktime->period_start->setDate($this->sheetTime->date->year, $this->sheetTime->date->month, $this->sheetTime->date->day);
        }

        //Крайняя левая отметка за следующий день от $sheetTime
        $minTime = Transaction::query()
            ->where('iclock_transaction.emp_id', '=', $this->sheetTime->emp_id)
            ->whereBetween('punch_time', [
                $this->sheetTime->date->startOfDay()->format('Ymd H:i'),
                $this->sheetTime->date->endOfDay()->format('Ymd H:i')
            ])
            ->get()
            ->sortBy(fn(Transaction $transaction) => $transaction->punch_time)
            ->first()
            ?->punch_time;

        //Крайняя правая отметка за текущий день в $sheetTime
        $maxTime = Transaction::query()
            ->where('iclock_transaction.emp_id', '=', $this->sheetTime->emp_id)
            ->whereBetween('punch_time', [
                $this->sheetTime->date->startOfDay()->format('Ymd H:i'),
                $this->sheetTime->date->endOfDay()->format('Ymd H:i')
            ])
            ->get()
            ->sortByDesc(fn(Transaction $transaction) => $transaction->punch_time)
            ->first()
            ?->punch_time;

        if(empty($minTime) || empty($maxTime)) {
            return;
        }

        //Вычисляем время прихода
        $workMinTime = $this->minTimeWork($timeInterval, $minTime);
        //Вычисляем время ухода
        $workMaxTime = $this->maxTimeWork($timeInterval, $maxTime);
        //Вычитаем перерыв
        $workDuration = $this->durationWork($workMinTime, $workMaxTime, $timeInterval->breaktime);

        //Записываем смену в $sheetTime
        $this->sheetTime->update([
            'is_night' => false,
            'min_time' => $minTime->format('H:i'),
            'max_time' => $workMaxTime->diff($workMinTime, true)->totalMinutes > 60 ? $maxTime->format('H:i') : '',
            'work_min_time' => $workMinTime->format('H:i'),
            'work_max_time' => $workMaxTime->format('H:i'),
            'duration' => $workMaxTime->diff($workMinTime, true)->totalMinutes > 60 ? $workDuration : 0,
            'corrected' => null,
        ]);
    }
}
