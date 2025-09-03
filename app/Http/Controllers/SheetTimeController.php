<?php

namespace App\Http\Controllers;


use App\Jobs\HasWorkTimeTrait;
use App\Jobs\RecalculateDalySheetTimeJob;
use App\Jobs\RecalculateNightSheetTimeJob;
use App\Models\Breaktime;
use App\Models\Employee;
use App\Models\SheetTime;
use App\Models\SheetTimeDto\CorrectedDto;
use App\Models\SheetTimeDto\IntervalDto;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Работа со сменами из таблицы "sheet_time"
 */
class SheetTimeController extends Controller
{
    use HasWorkTimeTrait;
    /*
     * Перевод в ночную смену
     */
    public function setNightShift(Request $request)
    {
        try {
            $shitTime = SheetTime::findOrFail($request->input('sheet_time_id'));
            RecalculateNightSheetTimeJob::dispatchSync($shitTime->refresh());
        } catch (\Throwable $exception) {

        } finally {
            return redirect(empty($request->input('anchor'))
                ? url()->previous()
                : url()->previous(). '#' .$request->input('anchor'));
        }
    }

    /*
     * Перевод в дневную смену
     */
    public function setDayShift(Request $request)
    {
        $shitTime = SheetTime::findOrFail($request->input('sheet_time_id'));

        RecalculateDalySheetTimeJob::dispatchSync($shitTime->refresh());

        return redirect(empty($request->input('anchor'))
            ? url()->previous()
            : url()->previous(). '#' .$request->input('anchor'));
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $sheetTime = $this->prepareSheetTime($data);

        $sheetTime->save();

        return response()->json(['status' => 'success'], 200);
    }

    public function update(Request $request, SheetTime $sheetTime)
    {
        $data = $request->all();

        $sheetTime = $this->prepareSheetTime($data, $sheetTime);

        //Убираем корректировку, если езмененное время равно оригиналу
        if(
            Carbon::parse($sheetTime->min_time)->format('H:i') == Carbon::parse($sheetTime->corrected->original->start)->format('H:i') &&
            Carbon::parse($sheetTime->max_time)->format('H:i') == Carbon::parse($sheetTime->corrected->original->end)->format('H:i')) {
            $sheetTime->corrected = null;
        }

        $sheetTime->save();

        return response()->json(['status' => 'success'], 200);
    }

    /**
     *  Обнуление записи за смену
     */
    public function destroy(Request $request, SheetTime $sheetTime): JsonResponse
    {
        if((!empty($sheetTime->min_time) || !empty($sheetTime->max_time)) && empty($sheetTime->corrected->userName)) {
            $original = new IntervalDto($sheetTime->min_time, $sheetTime->max_time);
        } else {
            $original = new IntervalDto($sheetTime->corrected->original->start, $sheetTime->corrected->original->end);
        }

        $sheetTime->corrected = new CorrectedDto(
            User::findOrFail($request->query('user_id'))->name,
            new IntervalDto('', ''),
            $original,
            $request->query('comment')
        );

        $sheetTime->work_min_time = null;
        $sheetTime->work_max_time = null;
        $sheetTime->min_time = null;
        $sheetTime->max_time = null;
        $sheetTime->duration = null;

        $sheetTime->save();

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Удаление посещаемости сотрудника за месяц
     */
    public function clearSheetTimeCurrentMonth(Request $request)
    {
        SheetTime::query()
            ->where('emp_code', $request->get('emp_code'))
            ->whereBetween('date', [
                Carbon::parse($request->get('date'))->startOfMonth()->startOfDay(),
                Carbon::parse($request->get('date'))->endOfMonth()->endOfDay()
                ])
            ->delete();

        return redirect(url()->previous());
    }

    private function prepareSheetTime(array $data, ?SheetTime $sheetTime = null): SheetTime
    {
        /** @var Employee $employee */
        $employee = Employee::findOrFail($data['emp_id']);

        $shift = $employee->getCurrentShift($data['date']);

        $shift->timeInterval->in_time($data['date']);
        $shift->timeInterval->breaktime?->period_start($data['date']);

        if(empty($sheetTime)) {
            $sheetTime = new SheetTime();

            $sheetTime->corrected = new CorrectedDto(
                User::findOrFail($data['user_id'])->name,
                new IntervalDto($data['min_time'], $data['max_time']),
                new IntervalDto('', ''),
                $data['comment']
            );

            $sheetTime->work_min_time = $data['min_time'];
            $sheetTime->work_max_time = $data['max_time'];
            $sheetTime->emp_id = $employee->id;
            $sheetTime->emp_code = $employee->emp_code;
            $sheetTime->surname = $employee->last_name;
            $sheetTime->name = $employee->first_name;
            $sheetTime->position = $employee->position->position_name;
            $sheetTime->date = $data['date'];
            $sheetTime->schedule_name = $shift->timeInterval->alias;
            $sheetTime->advance = $employee->getCurrentAdvance($data['date'])?->advance_amount;
            $sheetTime->salary_amount = $employee->getCurrentPayroll($data['date'])?->salary_amount;
            $sheetTime->per_pay_hour = $employee->getCurrentPayroll($data['date'])?->pay_per_hour;
            $sheetTime->is_night = false;
        } else {

            $sheetTime->corrected = new CorrectedDto(
                User::findOrFail($data['user_id'])->name,
                new IntervalDto($data['min_time'], $data['max_time']),
                empty($sheetTime->corrected->original->start) && empty($sheetTime->corrected->original->end) && empty($sheetTime->corrected->userName)
                    ? new IntervalDto($sheetTime->min_time, $sheetTime->max_time)
                    : $sheetTime->corrected->original,
                $data['comment']
            );
        }

        $sheetTime->min_time = $data['min_time'];
        $sheetTime->max_time = $data['max_time'];

        $start = $this->minTimeWork($shift->timeInterval, Carbon::parse($data['date'])->setTimeFrom($data['min_time']));
        $end = $this->maxTimeWork($shift->timeInterval, Carbon::parse($data['date'])->setTimeFrom($data['max_time']));
        $breaktime = $shift->timeInterval->breaktime;

        //Если это ночная смена
        if($start > $end) {
            $end->addDay();

            //Получаем перерыв для данной смены
            $breakTime = new BreakTime(config('shift.night.break_time'));

            //Разбиваем time_start в break_time на часы, минуты, секунды
            preg_match('/^([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/', config('shift.night.break_time.time_start'), $matches);

            //Задаем время начала перерыва
            $breakTime->period_start = $end
                ->clone()
                ->setTime($matches[1], $matches[2], $matches[3]);
        }

        $sheetTime->duration = $this->durationWork($start, $end, $breaktime);

        return $sheetTime;
    }
}
