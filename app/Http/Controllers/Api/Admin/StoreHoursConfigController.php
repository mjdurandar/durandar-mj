<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHoursConfigRequest;
use App\Models\StoreHoursConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreHoursConfigController extends Controller
{
    public function index(): JsonResponse
    {
        $configs = StoreHoursConfig::orderByRaw('FIELD(day_of_week, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday")')
            ->get();

        return response()->json($configs);
    }

    public function store(StoreHoursConfigRequest $request): JsonResponse
    {
        $config = StoreHoursConfig::create($request->validated());
        return response()->json($config, 201);
    }

    public function show(StoreHoursConfig $config): JsonResponse
    {
        return response()->json($config);
    }

    public function update(StoreHoursConfigRequest $request, StoreHoursConfig $config): JsonResponse
    {
        $config->update($request->validated());
        return response()->json($config);
    }

    public function destroy(StoreHoursConfig $config): JsonResponse
    {
        $config->delete();
        return response()->json(null, 204);
    }

    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'configs' => ['required', 'array'],
            'configs.*.id' => ['required', 'exists:store_hours_config,id'],
            'configs.*.opening_time' => ['required', 'date_format:H:i'],
            'configs.*.closing_time' => ['required', 'date_format:H:i', 'after:configs.*.opening_time'],
            'configs.*.lunch_break_start' => ['nullable', 'date_format:H:i'],
            'configs.*.lunch_break_end' => ['nullable', 'date_format:H:i'],
            'configs.*.is_open' => ['required', 'boolean'],
            'configs.*.alternate_weeks_only' => ['required', 'boolean'],
        ]);

        foreach ($request->configs as $configData) {
            $config = StoreHoursConfig::find($configData['id']);
            $config->update($configData);
        }

        return response()->json(['message' => 'Store hours updated successfully']);
    }
}
