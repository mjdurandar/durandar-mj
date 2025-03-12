<?php

namespace Tests\Feature;

use App\Models\StoreHoursConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreHoursConfigControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = User::factory()->create();

        // Create initial store hours
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($days as $day) {
            StoreHoursConfig::create([
                'day_of_week' => $day,
                'opening_time' => '08:00:00',
                'closing_time' => '16:00:00',
                'is_open' => true,
            ]);
        }
    }

    public function test_unauthorized_users_cannot_access_config()
    {
        $response = $this->getJson('/api/admin/store-hours');
        $response->assertUnauthorized();

        $response = $this->putJson('/api/admin/store-hours/1', [
            'is_open' => false
        ]);
        $response->assertUnauthorized();
    }

    public function test_admin_can_view_store_hours_config()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/store-hours');

        $response->assertOk()
            ->assertJsonCount(7)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'day_of_week',
                    'opening_time',
                    'closing_time',
                    'lunch_break_start',
                    'lunch_break_end',
                    'is_open',
                    'alternate_weeks_only'
                ]
            ]);
    }

    public function test_admin_can_update_store_hours()
    {
        $config = StoreHoursConfig::where('day_of_week', 'Monday')->first();

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/store-hours/{$config->id}", [
                'day_of_week' => 'Monday',
                'is_open' => false,
                'opening_time' => '09:00',
                'closing_time' => '17:00',
                'lunch_break_start' => '12:00',
                'lunch_break_end' => '13:00',
                'alternate_weeks_only' => false
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('store_hours_config', [
            'id' => $config->id,
            'is_open' => false,
            'opening_time' => '09:00:00',
            'closing_time' => '17:00:00',
            'lunch_break_start' => '12:00:00',
            'lunch_break_end' => '13:00:00',
            'alternate_weeks_only' => false
        ]);
    }

    public function test_admin_cannot_update_with_invalid_times()
    {
        $config = StoreHoursConfig::where('day_of_week', 'Monday')->first();

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/store-hours/{$config->id}", [
                'day_of_week' => 'Monday',
                'is_open' => true,
                'opening_time' => 'invalid-time',
                'closing_time' => '25:00', // Invalid hour
                'lunch_break_start' => '13:00',
                'lunch_break_end' => '12:00' // End before start
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['opening_time', 'closing_time', 'lunch_break_end']);
    }

    public function test_admin_cannot_update_nonexistent_config()
    {
        $response = $this->actingAs($this->admin)
            ->putJson('/api/admin/store-hours/999', [
                'day_of_week' => 'Monday',
                'is_open' => false
            ]);

        $response->assertNotFound();
    }

    public function test_admin_can_update_alternate_weeks_for_saturday()
    {
        $saturday = StoreHoursConfig::where('day_of_week', 'Saturday')->first();

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/store-hours/{$saturday->id}", [
                'day_of_week' => 'Saturday',
                'is_open' => true,
                'opening_time' => '08:00',
                'closing_time' => '16:00',
                'alternate_weeks_only' => true
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('store_hours_config', [
            'id' => $saturday->id,
            'alternate_weeks_only' => true
        ]);
    }

    public function test_admin_cannot_set_alternate_weeks_for_non_saturday()
    {
        $monday = StoreHoursConfig::where('day_of_week', 'Monday')->first();

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/store-hours/{$monday->id}", [
                'day_of_week' => 'Monday',
                'is_open' => true,
                'opening_time' => '08:00',
                'closing_time' => '16:00',
                'alternate_weeks_only' => true // Should fail for Monday
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['alternate_weeks_only']);
    }
} 