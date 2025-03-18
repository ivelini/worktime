<?php

namespace App\Console\Commands;

use App\Models\SheetTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GetPhotoEmployesCommand extends Command
{
    protected $signature = 'get:photo-employes';

    protected $description = 'Получение фоторгафии сотрудника с biotime';

    public function handle(): void
    {
        $empIds = SheetTime::select(['emp_id'])->distinct()->pluck('emp_id')->toArray();

        foreach ($empIds as $empId) {

            //Если изображение уже есть, то пропускаем загрузку
            if(Storage::disk('public')->exists('img/' . $empId . '.jpg')) {
                continue;
            }

            $response = Http::get(config('biotime.photo_employee'). '/' . $empId. '.jpg');

            if ($response->status() == 200) {
                Storage::disk('public')->put('img/' . $empId . '.jpg', $response->body());
            }
        }
    }
}
