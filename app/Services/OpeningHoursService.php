<?php

namespace App\Services;

use App\Models\StoreHoursConfig;
use Carbon\Carbon;

class OpeningHoursService
{
    public function isStoreOpen(Carbon $dateTime): bool
    {
        $dayConfig = $this->getDayConfig($dateTime);

        if (!$dayConfig || !$dayConfig->is_open) {
            return false;
        }

        if ($dayConfig->alternate_weeks_only && !$this->isAlternateWeek($dateTime)) {
            return false;
        }

        $time = $dateTime->format('H:i');
        return $time >= $dayConfig->opening_time->format('H:i') &&
               $time <= $dayConfig->closing_time->format('H:i');
    }

    public function isLunchBreak(Carbon $dateTime): bool
    {
        $dayConfig = $this->getDayConfig($dateTime);

        if (!$dayConfig || !$dayConfig->lunch_break_start || !$dayConfig->lunch_break_end) {
            return false;
        }

        $time = $dateTime->format('H:i');
        return $time >= $dayConfig->lunch_break_start->format('H:i') &&
               $time <= $dayConfig->lunch_break_end->format('H:i');
    }

    public function getNextOpeningTime(Carbon $dateTime): ?Carbon
    {
        $currentDay = $dateTime->copy();
        $time = $dateTime->format('H:i');

        // Check current day first
        $dayConfig = $this->getDayConfig($currentDay);
        if ($dayConfig && $dayConfig->is_open) {
            // If we're in lunch break, return lunch break end time
            if ($this->isLunchBreak($dateTime)) {
                return Carbon::parse($dayConfig->lunch_break_end);
            }

            // If before opening time
            if ($time < $dayConfig->opening_time->format('H:i')) {
                return Carbon::parse($dayConfig->opening_time);
            }
        }

        // Look for next open day
        for ($i = 1; $i <= 7; $i++) {
            $nextDay = $dateTime->copy()->addDays($i);
            $nextDayConfig = $this->getDayConfig($nextDay);

            if ($nextDayConfig && $nextDayConfig->is_open) {
                if ($nextDayConfig->alternate_weeks_only && !$this->isAlternateWeek($nextDay)) {
                    continue;
                }
                return Carbon::parse($nextDayConfig->opening_time)->setDateFrom($nextDay);
            }
        }

        return null;
    }

    public function getOpeningHoursForDay(Carbon $date): array
    {
        $dayConfig = $this->getDayConfig($date);
        
        if (!$dayConfig || !$dayConfig->is_open || 
            ($dayConfig->alternate_weeks_only && !$this->isAlternateWeek($date))) {
            return [];
        }

        $hours = [[
            'open' => $dayConfig->opening_time->format('H:i'),
            'close' => $dayConfig->closing_time->format('H:i'),
        ]];

        if ($dayConfig->lunch_break_start && $dayConfig->lunch_break_end) {
            $hours[] = [
                'open' => $dayConfig->lunch_break_start->format('H:i'),
                'close' => $dayConfig->lunch_break_end->format('H:i'),
                'isLunchBreak' => true,
            ];
        }

        return $hours;
    }

    protected function getDayConfig(Carbon $date): ?StoreHoursConfig
    {
        return StoreHoursConfig::where('day_of_week', $date->format('l'))->first();
    }

    protected function isAlternateWeek(Carbon $date): bool
    {
        // Consider weeks starting from the first week of the year
        return $date->weekOfYear % 2 === 0;
    }
} 