<?php

return [
    'open_time' => '08:00',
    'close_time' => '16:00',
    'lunch_start_time' => '12:00',
    'lunch_end_time' => '12:45',
    'regular_business_days' => [
        Carbon\Carbon::MONDAY,    // 1
        Carbon\Carbon::WEDNESDAY, // 3
        Carbon\Carbon::FRIDAY,    // 5
    ],
    'is_alternate_saturday' => true,
]; 