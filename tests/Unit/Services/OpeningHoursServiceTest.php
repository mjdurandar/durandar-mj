<?php

namespace Tests\Unit\Services;

use App\DTOs\OpeningHoursDTO;
use App\Services\OpeningHoursService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class OpeningHoursServiceTest extends TestCase
{
    private OpeningHoursService $service;
    private OpeningHoursDTO $config;

    protected function setUp(): void
    {
        parent::setUp();

        // Set a fixed test date/time to ensure consistent results
        Carbon::setTestNow(Carbon::parse('2024-03-13 10:00:00')); // A Wednesday

        $this->config = new OpeningHoursDTO(
            openTime: Carbon::parse('08:00'),
            closeTime: Carbon::parse('16:00'),
            lunchStartTime: Carbon::parse('12:00'),
            lunchEndTime: Carbon::parse('12:45'),
            regularBusinessDays: [
                Carbon::MONDAY,    // 1
                Carbon::WEDNESDAY, // 3
                Carbon::FRIDAY,    // 5
            ],
            isAlternateSaturday: true
        );

        $this->service = new OpeningHoursService($this->config);
    }

    #[Test]
    public function it_correctly_identifies_business_days()
    {
        // Wednesday (regular business day)
        $this->assertTrue($this->service->isBusinessDay(Carbon::parse('2024-03-13')));
        
        // Tuesday (non-business day)
        $this->assertFalse($this->service->isBusinessDay(Carbon::parse('2024-03-12')));
        
        // Alternate Saturday (week 11 - odd week)
        $this->assertFalse($this->service->isBusinessDay(Carbon::parse('2024-03-16')));
        
        // Alternate Saturday (week 12 - even week)
        $this->assertTrue($this->service->isBusinessDay(Carbon::parse('2024-03-23')));
    }

    #[Test]
    public function it_correctly_identifies_store_open_status()
    {
        // Open during regular hours
        $this->assertTrue($this->service->isOpen(Carbon::parse('2024-03-13 10:00')));
        
        // Closed during lunch break
        $this->assertFalse($this->service->isOpen(Carbon::parse('2024-03-13 12:30')));
        
        // Closed before opening time
        $this->assertFalse($this->service->isOpen(Carbon::parse('2024-03-13 07:00')));
        
        // Closed after closing time
        $this->assertFalse($this->service->isOpen(Carbon::parse('2024-03-13 17:00')));
        
        // Closed on non-business day
        $this->assertFalse($this->service->isOpen(Carbon::parse('2024-03-12 10:00')));
    }

    #[Test]
    public function it_correctly_identifies_lunch_break()
    {
        // During lunch break
        $this->assertTrue($this->service->isLunchBreak(Carbon::parse('2024-03-13 12:30')));
        
        // Before lunch break
        $this->assertFalse($this->service->isLunchBreak(Carbon::parse('2024-03-13 11:00')));
        
        // After lunch break
        $this->assertFalse($this->service->isLunchBreak(Carbon::parse('2024-03-13 13:00')));
        
        // During lunch time but on non-business day
        $this->assertFalse($this->service->isLunchBreak(Carbon::parse('2024-03-12 12:30')));
    }

    #[Test]
    public function it_correctly_gets_next_opening_time()
    {
        // From lunch break
        $lunchTime = Carbon::parse('2024-03-13 12:30');
        $expectedAfterLunch = Carbon::parse('2024-03-13 12:45');
        $this->assertEquals(
            $expectedAfterLunch->format('Y-m-d H:i'),
            $this->service->getNextOpeningTime($lunchTime)->format('Y-m-d H:i')
        );

        // From after closing time
        $afterClose = Carbon::parse('2024-03-13 17:00');
        $expectedNextDay = Carbon::parse('2024-03-15 08:00'); // Next business day (Friday)
        $this->assertEquals(
            $expectedNextDay->format('Y-m-d H:i'),
            $this->service->getNextOpeningTime($afterClose)->format('Y-m-d H:i')
        );

        // From non-business day
        $nonBusinessDay = Carbon::parse('2024-03-14 10:00'); // Thursday
        $expectedNextBusinessDay = Carbon::parse('2024-03-15 08:00'); // Friday
        $this->assertEquals(
            $expectedNextBusinessDay->format('Y-m-d H:i'),
            $this->service->getNextOpeningTime($nonBusinessDay)->format('Y-m-d H:i')
        );
    }

    #[Test]
    public function it_correctly_gets_opening_hours_for_day()
    {
        // Business day
        $businessDay = Carbon::parse('2024-03-13'); // Wednesday
        $expectedHours = [
            'open' => [
                'morning' => [
                    'start' => '08:00',
                    'end' => '12:00',
                ],
                'afternoon' => [
                    'start' => '12:45',
                    'end' => '16:00',
                ],
            ],
        ];
        $this->assertEquals($expectedHours, $this->service->getOpeningHoursForDay($businessDay));

        // Non-business day
        $nonBusinessDay = Carbon::parse('2024-03-14'); // Thursday
        $this->assertEquals([], $this->service->getOpeningHoursForDay($nonBusinessDay));
    }
} 