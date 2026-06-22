<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_trip(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('trips.store'), [
            'name' => 'Liburan Jepang 2026',
            'description' => 'Trip keliling Tokyo dan Osaka',
        ]);

        $response->assertRedirect(route('trips.index'));
        $this->assertDatabaseHas('trips', [
            'name' => 'Liburan Jepang 2026',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_associate_activity_with_trip(): void
    {
        $user = User::factory()->create();
        $trip = $user->trips()->create([
            'name' => 'Liburan Jepang 2026',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('activities.store'), [
            'title' => 'Makan Sushi',
            'location' => 'Tokyo',
            'event_date' => '2026-06-22',
            'trip_id' => $trip->id,
            'currency' => 'IDR',
            'friends' => ['Budi'],
            'items' => [
                ['name' => 'Sushi Set', 'price' => 150000, 'friend' => 'Budi'],
            ],
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('activities', [
            'title' => 'Makan Sushi',
            'trip_id' => $trip->id,
        ]);
    }

    public function test_consolidated_settlement_calculation(): void
    {
        $user = User::factory()->create();
        $trip = $user->trips()->create([
            'name' => 'Liburan Jepang 2026',
            'status' => 'active',
        ]);

        // Sesi 1
        $activity1 = $user->activities()->create([
            'title' => 'Sesi 1',
            'location' => 'Tokyo',
            'event_date' => '2026-06-22',
            'trip_id' => $trip->id,
            'total_amount' => 100000,
            'split_type' => 'equal',
        ]);
        $activity1->members()->create(['name' => 'BUDI']);

        // Sesi 2
        $activity2 = $user->activities()->create([
            'title' => 'Sesi 2',
            'location' => 'Osaka',
            'event_date' => '2026-06-23',
            'trip_id' => $trip->id,
            'total_amount' => 50000,
            'split_type' => 'equal',
        ]);
        $activity2->members()->create(['name' => 'BUDI']);

        $response = $this->actingAs($user)->get(route('trips.show', $trip));
        $response->assertStatus(200);
        $response->assertSee('BUDI');
        $response->assertSee('150.000');
    }

    public function test_toggle_member_payment_in_trip(): void
    {
        $user = User::factory()->create();
        $trip = $user->trips()->create([
            'name' => 'Liburan Jepang 2026',
            'status' => 'active',
        ]);

        $activity = $user->activities()->create([
            'title' => 'Sesi 1',
            'location' => 'Tokyo',
            'event_date' => '2026-06-22',
            'trip_id' => $trip->id,
            'total_amount' => 100000,
            'split_type' => 'equal',
        ]);
        $member = $activity->members()->create([
            'name' => 'BUDI',
            'payment_status' => 'unpaid',
        ]);

        // Toggle to paid
        $response = $this->actingAs($user)->post(route('trips.toggle-member-payment', $trip), [
            'member_name' => 'BUDI',
        ]);

        $response->assertJson([
            'success' => true,
            'is_fully_paid' => true,
        ]);

        $this->assertEquals('paid', $member->refresh()->payment_status);

        // Toggle back to unpaid
        $response = $this->actingAs($user)->post(route('trips.toggle-member-payment', $trip), [
            'member_name' => 'BUDI',
        ]);

        $response->assertJson([
            'success' => true,
            'is_fully_paid' => false,
        ]);

        $this->assertEquals('unpaid', $member->refresh()->payment_status);
    }

    public function test_guest_can_access_shared_trip_page(): void
    {
        $user = User::factory()->create();
        $trip = $user->trips()->create([
            'name' => 'Liburan Jepang 2026',
            'status' => 'active',
        ]);

        $response = $this->get(route('trips.shared', $trip->uuid));
        $response->assertStatus(200);
        $response->assertSee('Liburan Jepang 2026');
    }
}
