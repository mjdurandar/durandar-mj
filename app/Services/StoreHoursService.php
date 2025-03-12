<?php

namespace App\Services;

use App\Models\StoreHoursConfig;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Carbon\CarbonInterface;

class StoreHoursService
{
    /**
     * Get the store's current open/closed status
     */
    public function getCurrentStatus(): array
    {
        $now = Carbon::now();
        $dayOfWeek = $now->format('l');
        $currentTime = $now->format('H:i:s');
        
        $todayConfig = StoreHoursConfig::where('day_of_week', $dayOfWeek)->first();
        
        if (!$todayConfig || !$todayConfig->is_open) {
            return [
                'is_open' => false,
                'message' => 'Store is currently closed',
                'next_opening' => $this->getNextOpeningTime(),
                'next_opening_friendly' => $this->getNextOpeningTimeFriendly()
            ];
        }

        // Check if it's an alternate week Saturday
        if ($todayConfig->alternate_weeks_only && $dayOfWeek === 'Saturday') {
            $weekNumber = $now->weekOfYear;
            if ($weekNumber % 2 !== 0) { // Closed on odd weeks
                return [
                    'is_open' => false,
                    'message' => 'Store is closed on Alternate week',
                    'next_opening' => $this->getNextOpeningTime(),
                    'next_opening_friendly' => $this->getNextOpeningTimeFriendly()
                ];
            }
        }

        // Check if current time is within opening hours
        $openingTime = Carbon::parse($todayConfig->opening_time)->format('H:i:s');
        $closingTime = Carbon::parse($todayConfig->closing_time)->format('H:i:s');
        
        if ($currentTime < $openingTime || $currentTime > $closingTime) {
            return [
                'is_open' => false,
                'message' => 'Store is currently closed',
                'next_opening' => $this->getNextOpeningTime(),
                'next_opening_friendly' => $this->getNextOpeningTimeFriendly()
            ];
        }

        // Check if currently in lunch break
        if ($todayConfig->lunch_break_start && $todayConfig->lunch_break_end) {
            $lunchStart = Carbon::parse($todayConfig->lunch_break_start)->format('H:i:s');
            $lunchEnd = Carbon::parse($todayConfig->lunch_break_end)->format('H:i:s');
            
            if ($currentTime >= $lunchStart && $currentTime <= $lunchEnd) {
                return [
                    'is_open' => false,
                    'message' => 'Store is on lunch break',
                    'next_opening' => Carbon::parse($lunchEnd)->format('H:i'),
                    'next_opening_friendly' => Carbon::parse($lunchEnd)->format('h:i A')
                ];
            }
        }

        return [
            'is_open' => true,
            'message' => 'Store is open',
            'closing_time' => Carbon::parse($closingTime)->format('H:i'),
            'next_opening' => $this->getNextOpeningTime(),
            'next_opening_friendly' => $this->getNextOpeningTimeFriendly()
        ];
    }

    /**
     * Get the next time the store will be open
     */
    public function getNextOpeningTime(): string
    {
        $now = Carbon::now();
        $currentDayOfWeek = $now->format('l');
        $currentTime = $now->format('H:i:s');

        // Get all store hours configs ordered by the day of week
        $configs = StoreHoursConfig::all()
            ->sortBy(function ($config) use ($currentDayOfWeek) {
                $dayNumber = Carbon::parse("next {$config->day_of_week}")->dayOfWeek;
                $currentDayNumber = Carbon::parse("next {$currentDayOfWeek}")->dayOfWeek;
                
                if ($dayNumber < $currentDayNumber) {
                    $dayNumber += 7;
                }
                return $dayNumber;
            });

        foreach ($configs as $config) {
            if (!$config->is_open) continue;

            $nextOpeningDate = Carbon::parse("next {$config->day_of_week}");
            
            // If it's the current day and the opening time hasn't passed yet
            if ($config->day_of_week === $currentDayOfWeek && $currentTime < $config->opening_time) {
                return "Today at " . Carbon::parse($config->opening_time)->format('H:i');
            }

            // For alternate week Saturdays
            if ($config->alternate_weeks_only && $config->day_of_week === 'Saturday') {
                $weekNumber = $nextOpeningDate->weekOfYear;
                if ($weekNumber % 2 !== 0) { // Skip odd weeks
                    $nextOpeningDate->addWeek();
                }
            }

            return $nextOpeningDate->format('l') . " at " . 
                   Carbon::parse($config->opening_time)->format('H:i');
        }

        return 'No upcoming opening times found';
    }

    /**
     * Get a human-friendly next opening time
     */
    private function getNextOpeningTimeFriendly(): string
    {
        $now = Carbon::now();
        $currentDayOfWeek = $now->format('l');
        $currentTime = $now->format('H:i:s');

        // Get all store hours configs ordered by the day of week
        $configs = StoreHoursConfig::all()
            ->sortBy(function ($config) use ($currentDayOfWeek) {
                $dayNumber = Carbon::parse("next {$config->day_of_week}")->dayOfWeek;
                $currentDayNumber = Carbon::parse("next {$currentDayOfWeek}")->dayOfWeek;
                
                if ($dayNumber < $currentDayNumber) {
                    $dayNumber += 7;
                }
                return $dayNumber;
            });

        foreach ($configs as $config) {
            if (!$config->is_open) continue;

            $nextOpeningDate = Carbon::parse("next {$config->day_of_week}");
            
            // If it's the current day and the opening time hasn't passed yet
            if ($config->day_of_week === $currentDayOfWeek && $currentTime < $config->opening_time) {
                return "Later today at " . Carbon::parse($config->opening_time)->format('h:i A');
            }

            // For alternate week Saturdays
            if ($config->alternate_weeks_only && $config->day_of_week === 'Saturday') {
                $weekNumber = $nextOpeningDate->weekOfYear;
                if ($weekNumber % 2 !== 0) { // Skip odd weeks
                    $nextOpeningDate->addWeek();
                }
            }

            $diffInDays = $nextOpeningDate->diffForHumans($now, [
                'syntax' => CarbonInterface::DIFF_RELATIVE_TO_NOW,
                'options' => CarbonInterface::JUST_NOW | CarbonInterface::ONE_DAY_WORDS,
            ]);

            return "{$diffInDays} at " . Carbon::parse($config->opening_time)->format('h:i A');
        }

        return 'No upcoming opening times found';
    }

    /**
     * Check if the store is open on a specific date
     */
    public function checkDateStatus(string $date): array
    {
        $targetDate = Carbon::parse($date);
        $dayOfWeek = $targetDate->format('l');
        $now = Carbon::now();
        
        $config = StoreHoursConfig::where('day_of_week', $dayOfWeek)->first();
        
        if (!$config || !$config->is_open) {
            $nextOpeningDate = $this->findNextOpeningDate($targetDate);
            $diffForHumans = $nextOpeningDate ? $nextOpeningDate->diffForHumans($targetDate, [
                'syntax' => CarbonInterface::DIFF_RELATIVE_TO_NOW,
                'options' => CarbonInterface::JUST_NOW | CarbonInterface::ONE_DAY_WORDS,
            ]) : null;

            return [
                'is_open' => false,
                'message' => "Store is closed on {$dayOfWeek}s",
                'next_opening' => $nextOpeningDate ? $nextOpeningDate->format('l') . " at " . Carbon::parse($config->opening_time)->format('h:i A') : null,
                'next_opening_friendly' => $diffForHumans ? "{$diffForHumans} at " . Carbon::parse($config->opening_time)->format('h:i A') : null
            ];
        }

        // Check for alternate week Saturdays
        if ($config->alternate_weeks_only && $dayOfWeek === 'Saturday') {
            $weekNumber = $targetDate->weekOfYear;
            if ($weekNumber % 2 !== 0) {
                return [
                    'is_open' => false,
                    'message' => "Store is closed on this Saturday (Alternate week)",
                    'next_opening' => $this->getNextOpeningTime(),
                    'next_opening_friendly' => $this->getNextOpeningTimeFriendly()
                ];
            }
        }

        $openingTime = Carbon::parse($config->opening_time)->format('H:i');
        $closingTime = Carbon::parse($config->closing_time)->format('H:i');
        
        $message = "Store is open from {$openingTime} to {$closingTime}";
        
        if ($config->lunch_break_start && $config->lunch_break_end) {
            $lunchStart = Carbon::parse($config->lunch_break_start)->format('H:i');
            $lunchEnd = Carbon::parse($config->lunch_break_end)->format('H:i');
            $message .= " with lunch break from {$lunchStart} to {$lunchEnd}";
        }

        return [
            'is_open' => true,
            'message' => $message,
            'next_opening' => $this->getNextOpeningTime(),
            'next_opening_friendly' => $this->getNextOpeningTimeFriendly()
        ];
    }

    /**
     * Find the next opening date from a given date
     */
    private function findNextOpeningDate(Carbon $fromDate): ?Carbon
    {
        $configs = StoreHoursConfig::all()
            ->sortBy(function ($config) use ($fromDate) {
                $dayNumber = Carbon::parse("next {$config->day_of_week}")->dayOfWeek;
                $currentDayNumber = $fromDate->dayOfWeek;
                
                if ($dayNumber < $currentDayNumber) {
                    $dayNumber += 7;
                }
                return $dayNumber;
            });

        foreach ($configs as $config) {
            if (!$config->is_open) continue;

            $nextOpeningDate = Carbon::parse("next {$config->day_of_week}");
            
            if ($config->alternate_weeks_only && $config->day_of_week === 'Saturday') {
                $weekNumber = $nextOpeningDate->weekOfYear;
                if ($weekNumber % 2 !== 0) { // Skip odd weeks
                    $nextOpeningDate->addWeek();
                }
            }

            return $nextOpeningDate;
        }

        return null;
    }

    /**
     * Get the weekly schedule
     */
    public function getWeeklySchedule(): Collection
    {
        return StoreHoursConfig::orderByRaw("FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->get()
            ->map(function ($config) {
                $schedule = [
                    'day' => $config->day_of_week,
                    'is_open' => $config->is_open,
                    'opening_time' => $config->is_open ? Carbon::parse($config->opening_time)->format('H:i') : null,
                    'closing_time' => $config->is_open ? Carbon::parse($config->closing_time)->format('H:i') : null,
                    'alternate_weeks_only' => $config->alternate_weeks_only
                ];

                if ($config->lunch_break_start && $config->lunch_break_end) {
                    $schedule['lunch_break'] = [
                        'start' => Carbon::parse($config->lunch_break_start)->format('H:i'),
                        'end' => Carbon::parse($config->lunch_break_end)->format('H:i')
                    ];
                }

                return $schedule;
            });
    }
} 