<?php

use Carbon\Carbon;

$inTime = Carbon::parse(now())->setTime(20, 0);

return [
    'night' => [
        'alias' => 'Ночная',
        'time_interval' => [
            'alias' => '20:00-08:00 (ночная)',
            'in_time' => $inTime,
            'work_time_duration' => 720,
            'early_in' => true,
            'min_early_in' => 60,
            'late_out' => true,
            'min_late_out' => 60,
        ],
        'break_time' => [
            'alias' => 'Ночной перерыв',
            'period_start' => (clone $inTime)->addDay()->setTime(0,0),
            'duration' => 60,
        ]
    ]
];
