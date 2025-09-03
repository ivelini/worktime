<?php

use Carbon\Carbon;

$inTime = Carbon::parse(now())->setTime(20, 0);

return [
    'night' => [
        'alias' => 'Ночная для протяжников',
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
            'time_start' => '00:00:00',
            'duration' => 60,
        ]
    ],

    'night_not_norm' => [
        'alias' => 'Ночная не нормированная',
        'time_interval' => [
            'alias' => 'Ночная не нормированная',
            'in_time' => Carbon::parse(now())->setTime(8, 0),
            'work_time_duration' => 1380,
            'early_in' => true,
            'min_early_in' => 60,
            'late_out' => true,
            'min_late_out' => 60,
        ],
        'break_time' => [
            'alias' => 'Ночной перерыв',
            'time_start' => '00:00:00',
            'duration' => 60,
        ]
    ]
];
