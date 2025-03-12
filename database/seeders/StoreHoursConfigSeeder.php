<?php

namespace Database\Seeders;

use App\Models\StoreHoursConfig;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreHoursConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        foreach ($days as $day) {
            $isRegularDay = in_array($day, ['Monday', 'Wednesday', 'Friday']);
            $isSaturday = $day === 'Saturday';
            
            StoreHoursConfig::create([
                'day_of_week' => $day,
                'opening_time' => '08:00',
                'closing_time' => '16:00',
                'lunch_break_start' => $isRegularDay ? '12:00' : null,
                'lunch_break_end' => $isRegularDay ? '12:45' : null,
                'is_open' => $isRegularDay || $isSaturday,
                'alternate_weeks_only' => $isSaturday,
            ]);
        }
    }
}
