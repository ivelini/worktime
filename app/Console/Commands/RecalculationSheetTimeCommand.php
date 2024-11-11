<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\SheetTime;
use App\Repositories\TransactionsRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RecalculationSheetTimeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recalculation:sheet-time {--start= : Начало периода} {--end= : Конец периода}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Перерасчет табеля учета рабочего времени за текущий месяц';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //Проверка входящий параметров
        if (!empty($this->option('start')) && !empty($this->option('end'))) {

            $start = Carbon::parse($this->option('start'));
            $end = Carbon::parse($this->option('end'));
        } else {

            $start = now()->startOfMonth();
            $end = now();
        }

        TransactionsRepository::getGroupedUserAndDayIntoMaxAndMinPunchTimePoint($start, $end)
            ->each(function ($rawPunchDay, $index) {
                /** @var Employee $employee */
                $employee = Employee::findOrFail($rawPunchDay->emp_id);

                //Загружаем отношения, если не загружены
                if(count(array_diff(['shifts'], array_keys($employee->getRelations()))) > 0) {
                    $employee->load(['shifts']);
                }

                //Получаем смену
                $shift = $employee->getCurrentShift($rawPunchDay->date);        //Смена

                $minTime = Carbon::parse($rawPunchDay->min_time)->setSeconds(0);
                $maxTime = Carbon::parse($rawPunchDay->max_time)->setSeconds(0);

                $rawPunchDay->schedule_name = $shift->timeInterval->alias;

                // Если время между началом и концом смены меньше часа
                if($minTime->diff($maxTime)->totalMinutes < 60) {

                    $rawPunchDay->max_time = $rawPunchDay->max_time == $rawPunchDay->min_time ? '' :$rawPunchDay->max_time;
                    $rawPunchDay->work_min_time = $minTime->format('H:i');
                    $rawPunchDay->work_max_time = $minTime == $maxTime ? '' : $maxTime->format('H:i');
                    $rawPunchDay->duration = 0;
                } else {

                    $minTime = match(true) {
                        //Если время прихода меньше смещения слева или Если время прихода больше времени началом работы
                        ($minTime < $shift->timeInterval->min_early_in || $minTime > $shift->timeInterval->in_time) => $minTime->minute <= 10
                            ? $minTime->setMinutes(0)
                            : $minTime->addHour()->setMinutes(0),

                        //Если время прихода лежит в рамках между смещением слева и началом работы
                        ($minTime >= $shift->timeInterval->min_early_in && $minTime <= $shift->timeInterval->in_time) => $shift->timeInterval->in_time,
                        default => false,
                    };

                    $maxTime = match(true) {
                        //Если время ухода меньше времени конца смены или Если время ухода больше смещения справа
                        ($maxTime < $shift->timeInterval->end_time || $maxTime > $shift->timeInterval->min_late_out) => $maxTime->minute >= 50
                            ? $maxTime->addHour()->setMinutes(0)
                            : $maxTime->setMinutes(0),

                        //Если время ухода больше времени конца смены, но меньше смещения справа
                        $maxTime >= $shift->timeInterval->end_time && $maxTime <= $shift->timeInterval->min_late_out => $shift->timeInterval->end_time,
                        default => false,
                    };

                    try {
                        //Вычитаем перерыв, если есть
                        $workDuration = (!empty($shift->timeInterval->breaktime) && $maxTime > $shift->timeInterval->breaktime->period_end)
                            ? (clone $maxTime)
                                ->subMinutes($shift
                                    ->timeInterval
                                    ->breaktime
                                    ->duration)
                                ->diff($minTime)
                                ->hours
                            : $maxTime
                                ->diff($minTime)
                                ->hours;
                    } catch (\Throwable $exception) {
                        dd($rawPunchDay, $maxTime, $minTime);
                    }

                    $rawPunchDay->work_min_time = $minTime->format('H:i');
                    $rawPunchDay->work_max_time = $maxTime->format('H:i');
                    $rawPunchDay->duration = $workDuration;
                }

                $payroll = $employee->getCurrentPayroll($rawPunchDay->date);

                $rawPunchDay->advance = $employee->getCurrentAdvance($rawPunchDay->date)?->advance_amount;
                $rawPunchDay->salary_amount = $payroll?->salary_amount == 1 ? null : $payroll?->salary_amount;
                $rawPunchDay->per_pay_hour = $payroll?->pay_per_hour;

                SheetTime::updateOrCreate(
                    [
                        'emp_id' => $rawPunchDay->emp_id,
                        'date' => $rawPunchDay->date,
                    ],
                    (array) $rawPunchDay
                );
                $this->info('index: ' .$index);
            });
    }
}
