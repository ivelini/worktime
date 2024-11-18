<?php

namespace App\Console\Commands;

use App\Jobs\RecalculateNightSheetTimeJob;
use App\Models\Employee;
use App\Models\SheetTime;
use App\Repositories\TransactionsRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculationNightSheetTimeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recalculation:night-sheet-time
                                    {start? : Начало периода}
                                    {end? : Конец периода}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Перерасчет табеля ночных смен учета рабочего времени за текущий месяц';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //Проверка входящий параметров
        $start = !empty($this->argument('start'))
            ? Carbon::parse($this->argument('start'))->startOfDay()
            : now()->startOfMonth()->startOfDay();
        $end = !empty($this->argument('end'))
            ? Carbon::parse($this->argument('end'))->endOfDay()
            : now()->endOfDay();

        SheetTime::query()
            ->whereBetween('date', [$start->format('Y-m-d H:i'), $end->format('Y-m-d H:i')])
            ->where('is_night', true)
            ->get()
            ->each(function (SheetTime $sheetTime) {

                RecalculateNightSheetTimeJob::dispatch($sheetTime);
            });
    }
}
