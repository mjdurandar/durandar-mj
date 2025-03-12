<?php

namespace Tests\Unit;

use App\Models\StoreHoursConfig;
use App\Services\StoreHoursService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreHoursServiceTest extends TestCase
{
    use RefreshDatabase;

    private StoreHoursService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StoreHoursService();
    }

    public function test_get_current_status_when_store_is_open()
    {
        // Create a store hours config for Monday
        StoreHoursConfig::create([
            'day_of_week' => 'Monday',
            'opening_time' => '08:00:00',
            'closing_time' => '16:00:00',
            'is_open' => true,
        ]);

        // Set current time to Monday at 10 AM
        Carbon::setTestNow(Carbon::create(2024, 3, 18, 10, 0, 0)); // Monday, March 18, 2024 at 10:00 AM

        $status = $this->service->getCurrentStatus();

        $this->assertTrue($status['is_open']);
        $this->assertEquals('Store is open', $status['message']);
        $this->assertEquals('16:00', $status['closing_time']);
    }

    public function test_get_current_status_during_lunch_break()
    {
        // Create a store hours config for Monday with lunch break
        StoreHoursConfig::create([
            'day_of_week' => 'Monday',
            'opening_time' => '08:00:00',
            'closing_time' => '16:00:00',
            'lunch_break_start' => '12:00:00',
            'lunch_break_end' => '12:45:00',
            'is_open' => true,
        ]);

        // Set current time to Monday during lunch break
        Carbon::setTestNow(Carbon::create(2024, 3, 18, 12, 30, 0)); // Monday, March 18, 2024 at 12:30 PM

        $status = $this->service->getCurrentStatus();

        $this->assertFalse($status['is_open']);
        $this->assertEquals('Store is on lunch break', $status['message']);
        $this->assertEquals('12:45', $status['next_opening']);
    }

    public function test_get_current_status_on_alternate_week_saturday()
    {
        // Create a store hours config for Saturday
        StoreHoursConfig::create([
            'day_of_week' => 'Saturday',
            'opening_time' => '08:00:00',
            'closing_time' => '16:00:00',
            'is_open' => true,
            'alternate_weeks_only' => true,
        ]);

        // Set current time to a Saturday on an odd week
        Carbon::setTestNow(Carbon::parse('2025-03-15 10:00:00')); // Odd week Saturday

        $status = $this->service->getCurrentStatus();

        $this->assertFalse($status['is_open']);
        $this->assertEquals('Store is closed on Alternate week', $status['message']);
    }

    public function test_check_date_status_for_regular_open_day()
    {
        StoreHoursConfig::create([
            'day_of_week' => 'Monday',
            'opening_time' => '08:00:00',
            'closing_time' => '16:00:00',
            'lunch_break_start' => '12:00:00',
            'lunch_break_end' => '12:45:00',
            'is_open' => true,
        ]);

        $status = $this->service->checkDateStatus('2025-03-17'); // A Monday

        $this->assertTrue($status['is_open']);
        $this->assertEquals(
            'Store is open from 08:00 to 16:00 with lunch break from 12:00 to 12:45',
            $status['message']
        );
    }

    public function test_check_date_status_for_closed_day()
    {
        StoreHoursConfig::create([
            'day_of_week' => 'Sunday',
            'opening_time' => '08:00:00',
            'closing_time' => '16:00:00',
            'is_open' => false,
        ]);

        $status = $this->service->checkDateStatus('2025-03-16'); // A Sunday

        $this->assertFalse($status['is_open']);
        $this->assertEquals('Store is closed on Sundays', $status['message']);
    }

    public function test_get_weekly_schedule()
    {
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

        $schedule = $this->service->getWeeklySchedule();

        $this->assertCount(7, $schedule);
        $this->assertEquals('Monday', $schedule[0]['day']);
        $this->assertTrue($schedule[0]['is_open']);
        $this->assertEquals('08:00', $schedule[0]['opening_time']);
        $this->assertEquals('16:00', $schedule[0]['closing_time']);
        $this->assertTrue(isset($schedule[0]['lunch_break']));
        $this->assertEquals('12:00', $schedule[0]['lunch_break']['start']);
        $this->assertEquals('12:45', $schedule[0]['lunch_break']['end']);

        // Check Saturday (alternate weeks)
        $this->assertTrue($schedule[5]['alternate_weeks_only']);

        // Check Sunday (closed)
        $this->assertFalse($schedule[6]['is_open']);
        $this->assertNull($schedule[6]['opening_time']);
        $this->assertNull($schedule[6]['closing_time']);
    }

    public function test_get_next_opening_time_same_day()
    {
        $dayOfWeek = Carbon::now()->format('l');
        StoreHoursConfig::create([
            'day_of_week' => $dayOfWeek,
            'opening_time' => '08:00:00',
            'closing_time' => '16:00:00',
            'is_open' => true,
        ]);

        // Set current time before opening
        Carbon::setTestNow(Carbon::parse($dayOfWeek . ' 07:00:00'));

        $nextOpening = $this->service->getNextOpeningTime();
        $this->assertEquals('Today at 08:00', $nextOpening);
    }

    public function test_get_next_opening_time_next_day()
    {
        $today = Carbon::now();
        $tomorrow = $today->copy()->addDay();
        
        // Create config for today (closed) and tomorrow (open)
        StoreHoursConfig::create([
            'day_of_week' => $today->format('l'),
            'opening_time' => '08:00:00',
            'closing_time' => '16:00:00',
            'is_open' => false,
        ]);

        StoreHoursConfig::create([
            'day_of_week' => $tomorrow->format('l'),
            'opening_time' => '08:00:00',
            'closing_time' => '16:00:00',
            'is_open' => true,
        ]);

        // Set current time during today
        Carbon::setTestNow($today->setTime(10, 0));

        $nextOpening = $this->service->getNextOpeningTime();
        $this->assertEquals($tomorrow->format('l') . ' at 08:00', $nextOpening);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Clear mock time
        parent::tearDown();
    }
} 