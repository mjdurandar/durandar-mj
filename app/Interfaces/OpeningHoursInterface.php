<?php

namespace App\Interfaces;

use Carbon\Carbon;

interface OpeningHoursInterface
{
    /**
     * Check if the store is open at a given datetime
     */
    public function isOpen(Carbon $dateTime): bool;

    /**
     * Get the next opening time from a given datetime
     */
    public function getNextOpeningTime(Carbon $dateTime): Carbon;

    /**
     * Check if it's a valid business day
     */
    public function isBusinessDay(Carbon $date): bool;

    /**
     * Get opening hours for a specific day
     */
    public function getOpeningHoursForDay(Carbon $date): array;

    /**
     * Check if it's currently lunch break
     */
    public function isLunchBreak(Carbon $dateTime): bool;
} 