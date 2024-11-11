<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('recalculation:sheet-time')->hourly();
