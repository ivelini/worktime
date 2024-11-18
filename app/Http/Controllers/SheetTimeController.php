<?php

namespace App\Http\Controllers;


use App\Jobs\RecalculateDalySheetTimeJob;
use App\Jobs\RecalculateNightSheetTimeJob;
use App\Models\SheetTime;
use App\Models\Transaction;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;


class SheetTimeController extends Controller
{
    public function setNightShift(Request $request)
    {
        $shitTime = SheetTime::findOrFail($request->input('sheet_time_id'));

        RecalculateNightSheetTimeJob::dispatchSync($shitTime->refresh());

        return redirect(empty($request->input('anchor'))
            ? url()->previous()
            : url()->previous(). '#' .$request->input('anchor'));
    }

    public function setDayShift(Request $request)
    {
        $shitTime = SheetTime::findOrFail($request->input('sheet_time_id'));

        RecalculateDalySheetTimeJob::dispatchSync($shitTime->refresh());

        return redirect(empty($request->input('anchor'))
            ? url()->previous()
            : url()->previous(). '#' .$request->input('anchor'));
    }
}
