<?php

namespace Database\Seeders;

use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TripAmericaSeeder extends Seeder
{
    public function run()
    {
        // 1. Ambil atau buat User utama sebagai Host (Pemilik Trip)
        $host = User::firstOrCreate(
            ['email' => 'vanessa.422024030@civitas.ukrida.ac.id'],
            [
                'name' => 'Vanessa Maria',
                'password' => bcrypt('password'),
            ]
        );

        $usdRate = 0.000061;

        // ========================================================
        // TAHAP 1: PRE-REGISTER TRIP & PARTICIPANTS
        // ========================================================
        $trip = $host->trips()->create([
            'name' => 'Liburan Musim Panas Amerika',
            'description' => 'Wadah patungan BEM untuk Trip New York & LA 2026',
            'status' => 'active',
        ]);

        $participants = ['VANESSA', 'WILLIAMS', 'KEVIN', 'TANESHIA'];
        foreach ($participants as $name) {
            $trip->participants()->create([
                'name' => $name,
            ]);
        }

        // Fungsi Helper untuk membuat sesi patungan
        $createSession = function ($title, $location, $date, $taxUsd, $serviceUsd, $itemsInput) use ($host, $trip, $participants, $usdRate) {
            $taxIdr = $taxUsd / $usdRate;
            $serviceIdr = $serviceUsd / $usdRate;

            $activity = $host->activities()->create([
                'trip_id' => $trip->id,
                'title' => $title,
                'location' => $location,
                'event_date' => $date,
                'status' => 'active',
                'split_type' => 'proportional',
                'original_currency' => 'USD',
                'exchange_rate' => $usdRate,
                'tax' => $taxIdr,
                'service_charge' => $serviceIdr,
                'total_amount' => 0,
                'original_amount' => 0,
            ]);

            // Masukkan Anggota (Otomatis lunaskan Host)
            $hostFirstName = explode(' ', strtoupper($host->name))[0];
            foreach ($participants as $name) {
                $status = (strtoupper($name) === $hostFirstName) ? 'paid' : 'unpaid';
                $activity->members()->create(['name' => $name, 'payment_status' => $status]);
            }

            $totalItemsForeign = 0;
            $totalItemsIdr = 0;

            foreach ($itemsInput as $item) {
                $priceIdr = $item['price'] / $usdRate;

                $activity->items()->create([
                    'name' => $item['name'],
                    'price' => $priceIdr,
                    'friend_name' => $item['friend'],
                ]);

                $totalItemsForeign += $item['price'];
                $totalItemsIdr += $priceIdr;
            }

            $activity->update([
                'total_amount' => $totalItemsIdr + $taxIdr + $serviceIdr,
                'original_amount' => $totalItemsForeign + $taxUsd + $serviceUsd,
            ]);
        };

        // ========================================================
        // SESI 1: DINNER DI STARK STEAKHOUSE
        // ========================================================
        $createSession('Dinner Stark Steakhouse', 'Times Square, NY', Carbon::create(2026, 6, 15), 12.00, 8.00, [
            ['name' => 'T-BONE STEAK', 'price' => 45.00, 'friend' => 'WILLIAMS'],
            ['name' => 'SIRLOIN STEAK', 'price' => 40.00, 'friend' => 'KEVIN'],
            ['name' => 'SALMON GRILL', 'price' => 35.00, 'friend' => 'VANESSA'],
            ['name' => 'CAESAR SALAD', 'price' => 15.00, 'friend' => 'TANESHIA'],
            ['name' => 'MASHED POTATO', 'price' => 8.00, 'friend' => 'VANESSA'],
            ['name' => 'ICED TEA', 'price' => 3.00, 'friend' => 'WILLIAMS'],
            ['name' => 'ICED TEA', 'price' => 3.00, 'friend' => 'KEVIN'],
            ['name' => 'ICED LEMON TEA', 'price' => 4.00, 'friend' => 'TANESHIA'],
        ]);

        // ========================================================
        // SESI 2: NGOPI DI STARBUCKS ROASTERY
        // ========================================================
        $createSession('Ngopi Cantik Starbucks Roastery', 'Seattle', Carbon::create(2026, 6, 18), 3.50, 0, [
            ['name' => 'RESERVE LATTE', 'price' => 7.50, 'friend' => 'VANESSA'],
            ['name' => 'CARAMEL MACCHIATO', 'price' => 6.50, 'friend' => 'WILLIAMS'],
            ['name' => 'MATCHA FRAPP', 'price' => 8.00, 'friend' => 'KEVIN'],
            ['name' => 'AMERICANO', 'price' => 5.00, 'friend' => 'TANESHIA'],
            ['name' => 'CHEESECAKE (SHARE)', 'price' => 12.00, 'friend' => 'TANESHIA'],
        ]);

        // ========================================================
        // SESI 3: UNIVERSAL STUDIOS HOLLYWOOD
        // ========================================================
        $createSession('Universal Studios Hollywood', 'Los Angeles, CA', Carbon::create(2026, 6, 20), 25.00, 0, [
            ['name' => 'TICKET ADULT', 'price' => 109.00, 'friend' => 'VANESSA'],
            ['name' => 'TICKET ADULT', 'price' => 109.00, 'friend' => 'WILLIAMS'],
            ['name' => 'TICKET ADULT', 'price' => 109.00, 'friend' => 'KEVIN'],
            ['name' => 'TICKET ADULT', 'price' => 109.00, 'friend' => 'TANESHIA'],
            ['name' => 'BUTTERBEER', 'price' => 9.50, 'friend' => 'VANESSA'],
            ['name' => 'BUTTERBEER', 'price' => 9.50, 'friend' => 'WILLIAMS'],
            ['name' => 'GIANT PRETZEL', 'price' => 14.00, 'friend' => 'KEVIN'],
            ['name' => 'CHURROS', 'price' => 8.50, 'friend' => 'TANESHIA'],
        ]);

        // ========================================================
        // SESI 4: OLEH-OLEH DI WALMART
        // ========================================================
        $createSession('Belanja Oleh-Oleh Walmart', 'Los Angeles, CA', Carbon::create(2026, 6, 21), 8.50, 0, [
            ['name' => 'HERSHEY CHOCOLATE BOX', 'price' => 24.00, 'friend' => 'VANESSA'],
            ['name' => 'LA KEYCHAINS PACK', 'price' => 15.00, 'friend' => 'VANESSA'],
            ['name' => 'NIKE SOCKS', 'price' => 18.00, 'friend' => 'KEVIN'],
            ['name' => 'MAGNETS BUNDLE', 'price' => 20.00, 'friend' => 'TANESHIA'],
            ['name' => 'LAYS CHIPS', 'price' => 6.50, 'friend' => 'WILLIAMS'],
            ['name' => 'DR PEPPER PACK', 'price' => 8.00, 'friend' => 'WILLIAMS'],
        ]);

        $this->command->info('Trip Amerika dan 4 sesinya berhasil di-seed ulang untuk '.$host->email);
    }
}
