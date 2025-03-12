<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreHoursConfig extends Model
{
    protected $table = 'store_hours_config';

    protected $fillable = [
        'day_of_week',
        'opening_time',
        'closing_time',
        'lunch_break_start',
        'lunch_break_end',
        'is_open',
        'alternate_weeks_only',
    ];

    protected $casts = [
        'opening_time' => 'datetime:H:i',
        'closing_time' => 'datetime:H:i',
        'lunch_break_start' => 'datetime:H:i',
        'lunch_break_end' => 'datetime:H:i',
        'is_open' => 'boolean',
        'alternate_weeks_only' => 'boolean',
    ];
}
