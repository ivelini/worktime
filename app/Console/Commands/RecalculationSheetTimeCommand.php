<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\SheetTime;
use App\Repositories\TransactionsRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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

        //Отмечающиеся сотрудники
        $punchEmployeeIds = collect();
        TransactionsRepository::getGroupedUserAndDayIntoMaxAndMinPunchTimePoint($start, $end)
            ->each(function ($rawPunchDay, $index) use(&$punchEmployeeIds) {

                $punchEmployeeIds->push($rawPunchDay->emp_id);

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

        //Получаем пользователей, у которых нет отметок в системе
        Employee::query()
            ->whereIn('id', DB::connection('biotime')
                ->table('personnel_employee')
                ->select('personnel_employee.id')
                ->where('personnel_employee.status', '=', 0)
                ->pluck('id')
                ->diff($punchEmployeeIds->unique()->values())
                ->values()
            )
            ->with('position')
            ->get()
            ->each(function (Employee $employee) use ($start, $end) {

                $sheetTime = [
                    'emp_id' => $employee->id,
                    'emp_code' => $employee->emp_code,
                    'surname' => !empty($employee->last_name) ? $employee->last_name : '',
                    'name' => $employee->first_name,
                    'position' => $employee->position->position_name,
                    'schedule_name' => '',
                    'min_time' => null,
                    'max_time' => null,
                    'work_min_time' => null,
                    'work_max_time' => null,
                    'duration' => null,
                    'salary_amount' => $employee->getCurrentPayroll(now())?->salary_amount,
                    'per_pay_hour' => $employee->getCurrentPayroll(now())?->pay_per_hour,
                    'advance' => $employee->getCurrentAdvance(now())?->advance_amount,
                ];

                $i = clone $start;
                for($i->dayOfCentury; $i->dayOfCentury <= $end->dayOfCentury; $i->addDay()) {

                    SheetTime::updateOrCreate(
                        [
                            'emp_id' => $sheetTime['emp_id'],
                            'date' => $i,
                        ],
                        $sheetTime
                    );
                }

                $this->info('userId: ' .$sheetTime['emp_id']);
            });
    }
}
