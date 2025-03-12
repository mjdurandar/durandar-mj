<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\OpeningHoursInterface;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpeningHoursController extends Controller
{
    public function __construct(
        private OpeningHoursInterface $openingHours
    ) {}

    public function status(?string $date = null): JsonResponse
    {
        $dateTime = $date ? Carbon::parse($date) : Carbon::now();
        
        $isOpen = $this->openingHours->isOpen($dateTime);
        $isLunchBreak = $this->openingHours->isLunchBreak($dateTime);
        
        $status = $isLunchBreak ? 'lunch_break' : ($isOpen ? 'open' : 'closed');
        $nextOpeningTime = null;

        if (!$isOpen || $isLunchBreak) {
            $nextOpeningTime = $this->openingHours->getNextOpeningTime($dateTime);
        }

        return response()->json([
            'status' => $status,
            'timestamp' => $dateTime->toIso8601String(),
            'next_opening_time' => $nextOpeningTime?->toIso8601String(),
            'next_opening_time_human' => $nextOpeningTime ? $nextOpeningTime->diffForHumans() : null,
        ]);
    }

    public function openingHours(?string $date = null): JsonResponse
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        
        return response()->json([
            'date' => $date->toDateString(),
            'is_business_day' => $this->openingHours->isBusinessDay($date),
            'hours' => $this->openingHours->getOpeningHoursForDay($date),
        ]);
    }

    public function checkDate(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = Carbon::parse($request->date);
        
        return response()->json([
            'date' => $date->toDateString(),
            'is_business_day' => $this->openingHours->isBusinessDay($date),
            'hours' => $this->openingHours->getOpeningHoursForDay($date),
            'next_opening_time' => $this->openingHours->getNextOpeningTime($date)->toIso8601String(),
            'next_opening_time_human' => $this->openingHours->getNextOpeningTime($date)->diffForHumans(),
        ]);
    }
} 