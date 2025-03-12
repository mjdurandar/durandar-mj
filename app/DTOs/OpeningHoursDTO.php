<?php

namespace App\DTOs;

use Carbon\Carbon;

class OpeningHoursDTO
{
    public function __construct(
        public Carbon $openTime,
        public Carbon $closeTime,
        public Carbon $lunchStartTime,
        public Carbon $lunchEndTime,
        public array $regularBusinessDays, // [1, 3, 5] for Mon, Wed, Fri
        public bool $isAlternateSaturday = false
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            openTime: Carbon::parse($data['open_time']),
            closeTime: Carbon::parse($data['close_time']),
            lunchStartTime: Carbon::parse($data['lunch_start_time']),
            lunchEndTime: Carbon::parse($data['lunch_end_time']),
            regularBusinessDays: $data['regular_business_days'],
            isAlternateSaturday: $data['is_alternate_saturday'] ?? false
        );
    }
} 