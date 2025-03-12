<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OpeningHoursService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreHoursController extends Controller
{
    protected $openingHoursService;

    public function __construct(OpeningHoursService $openingHoursService)
    {
        $this->openingHoursService = $openingHoursService;
    }

    public function status(Request $request): JsonResponse
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::now();
        $isOpen = $this->openingHoursService->isStoreOpen($date);
        $isLunchBreak = $this->openingHoursService->isLunchBreak($date);
        $nextOpeningTime = $this->openingHoursService->getNextOpeningTime($date);

        return response()->json([
            'isOpen' => $isOpen && !$isLunchBreak,
            'isLunchBreak' => $isLunchBreak,
            'nextOpeningTime' => $nextOpeningTime ? $nextOpeningTime->format('H:i') : null,
            'date' => $date->format('Y-m-d'),
        ]);
    }

    public function today(Request $request): JsonResponse
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::now();
        $hours = $this->openingHoursService->getOpeningHoursForDay($date);

        return response()->json($this->formatHours($hours));
    }

    public function week(Request $request): JsonResponse
    {
        $startDate = $request->date ? Carbon::parse($request->date) : Carbon::now();
        $weekSchedule = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            $hours = $this->openingHoursService->getOpeningHoursForDay($date);

            $weekSchedule[] = [
                'date' => $date->format('Y-m-d'),
                'name' => $date->format('l'),
                'isToday' => $date->isToday(),
                'hours' => $this->formatHours($hours),
            ];
        }

        return response()->json($weekSchedule);
    }

    protected function formatHours(array $hours): array
    {
        $formatted = [];

        foreach ($hours as $period) {
            $formatted[] = [
                'open' => $period['open'],
                'close' => $period['close'],
                'isLunchBreak' => $period['isLunchBreak'] ?? false,
            ];
        }

        return $formatted;
    }
} 