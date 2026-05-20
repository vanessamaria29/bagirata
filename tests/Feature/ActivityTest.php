<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_activity_with_tax_and_service_charge(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('activities.store'), [
            'title'          => 'Makan Siang',
            'location'       => 'Ancol',
            'event_date'     => '2026-05-20',
            'tax'            => 11000,
            'service_charge' => 5000,
            'friends'        => ['Budi', 'Ani'],
            'items'          => [
                ['name' => 'Nasi Goreng', 'price' => 50000, 'friend' => 'Budi'],
                ['name' => 'Es Teh',      'price' => 10000, 'friend' => 'Ani'],
            ],
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('activities', [
            'title'          => 'Makan Siang',
            'tax'            => 11000,
            'service_charge' => 5000,
            'total_amount'   => 76000,
        ]);
    }

    public function test_activity_total_amount_includes_tax_and_service_charge(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('activities.store'), [
            'title'          => 'Nobar',
            'location'       => 'XXI',
            'event_date'     => '2026-05-20',
            'tax'            => 5500,
            'service_charge' => 2500,
        ]);

        $this->assertDatabaseHas('activities', [
            'title'          => 'Nobar',
            'total_amount'   => 8000,
            'tax'            => 5500,
            'service_charge' => 2500,
        ]);
    }

    public function test_activity_show_page_displays_tax_and_service_charge(): void
    {
        $user = User::factory()->create();

        $activity = $user->activities()->create([
            'title'          => 'Test',
            'location'       => 'Tempat',
            'event_date'     => '2026-05-20',
            'status'         => 'active',
            'total_amount'   => 66000,
            'tax'            => 6000,
            'service_charge' => 6000,
        ]);

        $response = $this->actingAs($user)->get(route('activities.show', $activity));

        $response->assertOk();
        $response->assertSee('Test');
    }

    public function test_user_can_update_tax_and_service_charge(): void
    {
        $user = User::factory()->create();

        $activity = $user->activities()->create([
            'title'          => 'Awal',
            'location'       => 'Kota',
            'event_date'     => '2026-05-20',
            'status'         => 'active',
            'total_amount'   => 10000,
            'tax'            => 0,
            'service_charge' => 0,
        ]);

        $response = $this->actingAs($user)->put(route('activities.update', $activity), [
            'title'          => 'Update',
            'location'       => 'Luar Kota',
            'event_date'     => '2026-05-21',
            'tax'            => 11000,
            'service_charge' => 5000,
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('activities', [
            'id'             => $activity->id,
            'title'          => 'Update',
            'tax'            => 11000,
            'service_charge' => 5000,
        ]);
    }

    public function test_index_page_shows_activities(): void
    {
        $user = User::factory()->create();

        $user->activities()->create([
            'title'          => 'My Bill',
            'location'       => 'Mall',
            'event_date'     => '2026-05-20',
            'status'         => 'active',
            'total_amount'   => 50000,
            'tax'            => 5000,
            'service_charge' => 3000,
        ]);

        $response = $this->actingAs($user)->get(route('activities.index'));

        $response->assertOk();
        $response->assertSee('My Bill');
    }

    public function test_member_breakdown_distributes_tax_proportionally(): void
    {
        $user = User::factory()->create();
        $activity = $user->activities()->create([
            'title'          => 'Makan',
            'location'       => 'Cafe',
            'event_date'     => '2026-05-20',
            'status'         => 'active',
            'total_amount'   => 174000,
            'tax'            => 11000,
            'service_charge' => 5000,
        ]);

        $activity->members()->createMany([
            ['name' => 'Budi'],
            ['name' => 'Ani'],
            ['name' => 'Citra'],
        ]);

        $activity->items()->createMany([
            ['name' => 'Steak',     'price' => 80000, 'friend_name' => 'Budi'],
            ['name' => 'Pasta',     'price' => 50000, 'friend_name' => 'Ani'],
            ['name' => 'Jus',       'price' => 15000, 'friend_name' => 'Budi'],
            ['name' => 'Salad',     'price' => 13000, 'friend_name' => 'Citra'],
        ]);

        $breakdown = $activity->member_breakdown;

        $this->assertCount(3, $breakdown);

        $budi = collect($breakdown)->firstWhere('name', 'Budi');
        $ani = collect($breakdown)->firstWhere('name', 'Ani');
        $citra = collect($breakdown)->firstWhere('name', 'Citra');

        // Budi: 80000+15000 = 95000 -> ratio 95000/158000 = 0.6013
        // tax = round(0.6013 * 11000) = 6614
        // sc  = round(0.6013 * 5000)  = 3007
        // total = 95000 + 6614 + 3007 = 104621
        $this->assertEquals(95000, $budi['subtotal']);
        $this->assertEquals(95000 + $budi['tax'] + $budi['sc'], $budi['total']);

        // Verify sum of shares equals total tax and SC
        $totalTaxShare = collect($breakdown)->sum('tax');
        $totalScShare = collect($breakdown)->sum('sc');
        $this->assertEquals($activity->tax, $totalTaxShare);
        $this->assertEquals($activity->service_charge, $totalScShare);
    }

    public function test_member_breakdown_includes_member_without_items(): void
    {
        $user = User::factory()->create();
        $activity = $user->activities()->create([
            'title'          => 'Nobar',
            'location'       => 'XXI',
            'event_date'     => '2026-05-20',
            'status'         => 'active',
            'total_amount'   => 55000,
            'tax'            => 5000,
            'service_charge' => 0,
        ]);

        $activity->members()->createMany([
            ['name' => 'Budi'],
            ['name' => 'Doni'],
        ]);

        $activity->items()->createMany([
            ['name' => 'Tiket', 'price' => 50000, 'friend_name' => 'Budi'],
        ]);

        $breakdown = $activity->member_breakdown;

        $this->assertCount(2, $breakdown);

        $doni = collect($breakdown)->firstWhere('name', 'Doni');
        $this->assertEquals(0, $doni['subtotal']);
        $this->assertEquals(0, $doni['tax']);
        $this->assertEquals(0, $doni['total']);
    }

    public function test_member_breakdown_item_without_friend(): void
    {
        $user = User::factory()->create();
        $activity = $user->activities()->create([
            'title'          => 'Makan',
            'location'       => 'Cafe',
            'event_date'     => '2026-05-20',
            'status'         => 'active',
            'total_amount'   => 15000,
            'tax'            => 0,
            'service_charge' => 0,
        ]);

        $activity->items()->createMany([
            ['name' => 'Kopi', 'price' => 15000, 'friend_name' => null],
        ]);

        $breakdown = $activity->member_breakdown;

        $this->assertCount(1, $breakdown);
        $this->assertEquals('Unassigned', $breakdown[0]['name']);
        $this->assertEquals(15000, $breakdown[0]['subtotal']);
    }

    public function test_member_breakdown_with_no_items(): void
    {
        $user = User::factory()->create();
        $activity = $user->activities()->create([
            'title'          => 'Kosong',
            'location'       => 'Rumah',
            'event_date'     => '2026-05-20',
            'status'         => 'active',
            'total_amount'   => 0,
            'tax'            => 0,
            'service_charge' => 0,
        ]);

        $this->assertCount(0, $activity->member_breakdown);
    }

    public function test_member_breakdown_zero_tax_and_sc(): void
    {
        $user = User::factory()->create();
        $activity = $user->activities()->create([
            'title'          => 'Minum',
            'location'       => 'Kafe',
            'event_date'     => '2026-05-20',
            'status'         => 'active',
            'total_amount'   => 30000,
            'tax'            => 0,
            'service_charge' => 0,
        ]);

        $activity->members()->create(['name' => 'Budi']);
        $activity->items()->create(['name' => 'Kopi', 'price' => 30000, 'friend_name' => 'Budi']);

        $breakdown = $activity->member_breakdown;

        $this->assertCount(1, $breakdown);
        $this->assertEquals(0, $breakdown[0]['tax']);
        $this->assertEquals(0, $breakdown[0]['sc']);
        $this->assertEquals(30000, $breakdown[0]['total']);
    }
}
