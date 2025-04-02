<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('recalculation:sheet-time')->hourly();
Schedule::command('get:photo-employs')->dailyAt('22:00:00');
