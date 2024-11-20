<?php

namespace App\Exports;

use App\Repositories\SheetTimeRepository;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class SheetTimeExport implements FromView
{
    public function __construct(public Carbon $startAt, public Carbon $endAt)
    {}

    public function view(): View
    {
        return view('exports.timesheet', [
            'sheetTimeRows' => SheetTimeRepository::getReportSheetTime($this->startAt, $this->endAt)
        ]);
    }
}
