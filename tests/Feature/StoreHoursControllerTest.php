<?php

namespace Tests\Feature;

use App\Models\StoreHoursConfig;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreHoursControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create store hours for the entire week
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($days as $day) {
            StoreHoursConfig::create([
                'day_of_week' => $day,
                'opening_time' => '08:00:00',
                'closing_time' => '16:00:00',
                'lunch_break_start' => $day === 'Monday' ? '12:00:00' : null,
                'lunch_break_end' => $day === 'Monday' ? '12:45:00' : null,
                'is_open' => $day !== 'Sunday',
                'alternate_weeks_only' => $day === 'Saturday',
            ]);
        }
    }

    public function test_get_current_status()
    {
        // Set current time to Monday at 10 AM
        Carbon::setTestNow(Carbon::create(2024, 3, 18, 10, 0, 0)); // Monday, March 18, 2024 at 10:00 AM

        $response = $this->getJson('/api/store-hours/status');

        $response->assertOk()
            ->assertJson([
                'is_open' => true,
                'message' => 'Store is open',
                'closing_time' => '16:00'
            ]);
    }

    public function test_check_date_status()
    {
        $response = $this->getJson('/api/store-hours/check-date/2024-03-18'); // A Monday

        $response->assertOk()
            ->assertJson([
                'is_open' => true,
                'message' => 'Store is open from 08:00 to 16:00 with lunch break from 12:00 to 12:45'
            ]);
    }

    public function test_get_weekly_schedule()
    {
        $response = $this->getJson('/api/store-hours/schedule');

        $response->assertOk()
            ->assertJsonCount(7)
            ->assertJsonStructure([
                '*' => [
                    'day',
                    'is_open',
                    'opening_time',
                    'closing_time',
                    'alternate_weeks_only'
                ]
            ]);

        // Check specific day (Monday)
        $response->assertJson([
            [
                'day' => 'Monday',
                'is_open' => true,
                'opening_time' => '08:00',
                'closing_time' => '16:00',
                'lunch_break' => [
                    'start' => '12:00',
                    'end' => '12:45'
                ]
            ]
        ]);
    }

    public function test_check_invalid_date_format()
    {
        $response = $this->getJson('/api/store-hours/check-date/invalid-date');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
} 