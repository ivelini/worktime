<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('recalculation:sheet-time')->hourly();
Schedule::command('get:photo-employes')->dailyAt('22:00:00');
