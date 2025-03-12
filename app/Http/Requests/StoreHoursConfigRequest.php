<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHoursConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // TODO: Add proper authorization
    }

    public function rules(): array
    {
        return [
            'day_of_week' => ['required', 'string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'opening_time' => ['required', 'date_format:H:i'],
            'closing_time' => ['required', 'date_format:H:i', 'after:opening_time'],
            'lunch_break_start' => ['nullable', 'date_format:H:i', 'after:opening_time', 'before:closing_time'],
            'lunch_break_end' => ['nullable', 'date_format:H:i', 'after:lunch_break_start', 'before:closing_time'],
            'is_open' => ['required', 'boolean'],
            'alternate_weeks_only' => ['required', 'boolean'],
        ];
    }
} 