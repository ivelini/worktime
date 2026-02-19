<?php

namespace App\Console\Commands;

use App\Jobs\HasWorkTimeTrait;
use App\Models\Employee;
use App\Models\SheetTime;
use App\Repositories\TransactionsRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RecalculationSheetTimeCommand extends Command
{
    use HasWorkTimeTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recalculation:sheet-time
                                    {start? : Начало периода}
                                    {end? : Конец периода}
                                    {emp_code? : ID сотрудника}';

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
        $start = !empty($this->argument('start'))
            ? Carbon::parse($this->argument('start'))->startOfDay()
            : now()->startOfMonth()->startOfDay();
        $end = !empty($this->argument('end'))
            ? Carbon::parse($this->argument('end'))->endOfDay()
            : now()->endOfDay();

        //Отмечающиеся сотрудники
        $punchEmployeeIds = collect();
        TransactionsRepository::getGroupedUserAndDayIntoMaxAndMinPunchTimePoint($start, $end, $this->argument('emp_code'))
            ->each(function ($rawPunchDay, $index) use(&$punchEmployeeIds) {

                //Проверяем не помечена ли данная смена как ночная или отредактированная вручную
                $isNotModify = SheetTime::query()
                    ->where('emp_id', $rawPunchDay->emp_id)
                    ->where('date', $rawPunchDay->date)
                    ->where(function (Builder $query) {
                        $query->orWhere('is_night', true);
                        $query->orWhereNotNull('corrected');
                    })
                    ->exists();

                // Пропускаем такие смены
                if($isNotModify) {
                    return;
                }

                $punchEmployeeIds->push($rawPunchDay->emp_id);

                /** @var Employee $employee */
                $employee = Employee::findOrFail($rawPunchDay->emp_id);

                $rawPunchDay->department = $employee->department;

                //Загружаем отношения, если не загружены
                if(count(array_diff(['shifts'], array_keys($employee->getRelations()))) > 0) {
                    $employee->load(['shifts']);
                }

                //Получаем смену
                $shift = $employee->getCurrentShift($rawPunchDay->date);            //Смена
                $shift->timeInterval->in_time($rawPunchDay->date);                  //Устанавливаем дату
                $shift->timeInterval->breaktime?->period_start($rawPunchDay->date);  //Устанавливаем дату для перерыва

                $minTime = Carbon::parse($rawPunchDay->date . ' ' .$rawPunchDay->min_time)->setSeconds(0);
                $maxTime = Carbon::parse($rawPunchDay->date . ' ' .$rawPunchDay->max_time)->setSeconds(0);

                $rawPunchDay->schedule_name = $shift->timeInterval->alias;

                // Если время между началом и концом смены меньше часа
                if($minTime->diff($maxTime)->totalMinutes < 60) {

                    $rawPunchDay->max_time = $maxTime->diff($minTime)->seconds <= 60 ? '' :$rawPunchDay->max_time;
                    $rawPunchDay->work_min_time = $minTime->format('H:i');
                    $rawPunchDay->work_max_time = $maxTime->diff($minTime)->seconds <= 60 ? '' : $maxTime->format('H:i');
                    $rawPunchDay->duration = 0;
                } else {

                    $minTime = $this->minTimeWork($shift->timeInterval, $minTime);
                    $maxTime = $this->maxTimeWork($shift->timeInterval, $maxTime);
                    $workDuration = $this->durationWork($minTime, $maxTime, $shift->timeInterval->breaktime);

                    $rawPunchDay->work_min_time = $minTime->format('H:i');
                    $rawPunchDay->work_max_time = $maxTime->format('H:i');
                    $rawPunchDay->duration = $workDuration;
                }

                $payroll = $employee->getCurrentPayroll($rawPunchDay->date);

                $rawPunchDay->advance = $employee->getCurrentAdvance($rawPunchDay->date)?->advance_amount;
                $rawPunchDay->salary_amount = $payroll?->salary_amount == 1 ? null : $payroll?->salary_amount;
                $rawPunchDay->per_pay_hour = $payroll?->pay_per_hour;
                $rawPunchDay->salary_supplement = $payroll?->salary_supplement;

                SheetTime::updateOrCreate(
                    [
                        'emp_id' => $rawPunchDay->emp_id,
                        'date' => $rawPunchDay->date,
                    ],
                    (array) $rawPunchDay
                );

                $this->info('index: ' .$index);
            });

        //Не обсчитываем остальных сотрудников, если запустили для одного
        if($this->argument('emp_code') != null) {
            return;
        }

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
                    'department' => $employee->department,
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
