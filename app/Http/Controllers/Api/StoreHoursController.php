<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StoreHoursService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreHoursController extends Controller
{
    private StoreHoursService $service;

    public function __construct(StoreHoursService $service)
    {
        $this->service = $service;
    }

    public function getCurrentStatus(): JsonResponse
    {
        return response()->json($this->service->getCurrentStatus());
    }

    public function checkDateStatus(string $date): JsonResponse
    {
        try {
            return response()->json($this->service->checkDateStatus($date));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Invalid date format',
                'errors' => ['date' => ['The date format is invalid']]
            ], 422);
        }
    }

    public function getWeeklySchedule(): JsonResponse
    {
        return response()->json($this->service->getWeeklySchedule());
    }
} 