<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollSalary;
use App\Models\TimeInterval;
use App\Models\Shift;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function timeSheet(Request $request)
    {
        $startAt = Carbon::parse($request->input('start_at'))->startOfDay()->format('Ymd H:i');
        $endAt = Carbon::parse($request->input('end_at'))->endOfDay()->format('Ymd H:i');


        dd(DB::connection('biotime')
            ->table('iclock_transaction')
            ->selectRaw("
                iclock_transaction.emp_id,
                max(personnel_employee.last_name) as surname,
                max(personnel_employee.first_name) as name,
                max(personnel_position.position_name) as position,
                CAST(iclock_transaction.punch_time as date) as date,
                MIN(CAST(iclock_transaction.punch_time as time)) as min_time,
                MAX(CAST(iclock_transaction.punch_time as time)) as max_time
            ")
            ->join('personnel_employee', 'iclock_transaction.emp_id', '=', 'personnel_employee.id')
            ->join('personnel_position', 'personnel_employee.position_id', '=', 'personnel_position.id')
            ->whereBetween('punch_time', [$startAt, $endAt])
            ->groupByRaw("iclock_transaction.emp_id, CAST(iclock_transaction.punch_time as date)")
            ->orderBy('iclock_transaction.emp_id')
            ->get()
            ->map(function ($rawPunchDay) {
                /** @var Employee $employee */
                $employee = Employee::findOrFail($rawPunchDay->emp_id);

                //Загружаем отношения, если не загружены
                if(count(array_diff(['shifts'], array_keys($employee->getRelations()))) > 0) {
                    $employee->load(['shifts']);
                }

                //Получаем смену
                $shift = $employee->getCurrentShift($rawPunchDay->date);        //Смена

                dd($shift->graph);

                return [
                    'id' => $rawPunchDay->emp_id
                ];
            }));
    }
}
