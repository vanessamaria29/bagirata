<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Activity extends Model
{
    protected $fillable = ['uuid', 'title', 'location', 'event_date', 'status', 'total_amount', 'tax', 'service_charge', 'split_type', 'trip_id', 'original_currency', 'exchange_rate', 'original_amount'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($activity) {
            if (empty($activity->uuid)) {
                $activity->uuid = (string) Str::uuid();
            }
        });
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getMemberBreakdownAttribute()
    {
        $breakdown = [];
        $members = $this->members;
        $items = $this->items;
        $totalMembers = $members->count() ?: 1; // Cegah error dibagi 0

        // JIKA MODE BAGI RATA (EQUAL)
        if ($this->split_type === 'equal') {
            $grandTotal = $this->total_amount;
            $perPerson = $grandTotal / $totalMembers;

            // Bagi rata juga subtotal dan pajaknya biar UI nggak kelihatan 0
            $sharedSubtotal = $items->sum('price') / $totalMembers;
            $sharedTax = ($this->tax ?? 0) / $totalMembers;
            $sharedSc = ($this->service_charge ?? 0) / $totalMembers;

            foreach ($members as $member) {
                $breakdown[] = [
                    'id' => $member->id,
                    'name' => $member->name,
                    'payment_status' => $member->payment_status ?? 'unpaid',
                    'items' => collect([]), // Item tetap kosong karena patungan bareng
                    'subtotal' => $sharedSubtotal,
                    'tax' => $sharedTax,
                    'sc' => $sharedSc,
                    'total' => $perPerson,
                ];
            }

            return collect($breakdown);
        }

        // JIKA MODE SESUAI PESANAN (PROPORSIONAL LAMA)
        $totalMenuSubtotal = $items->sum('price');
        $totalTax = $this->tax ?? 0;
        $totalSc = $this->service_charge ?? 0;

        $memberBreakdowns = [];
        foreach ($members as $member) {
            $memberBreakdowns[strtoupper($member->name)] = [
                'name' => $member->name,
                'items' => collect([]),
                'subtotal' => 0,
                'tax' => 0,
                'sc' => 0,
                'total' => 0,
            ];
        }

        $unassignedBreakdown = [
            'id' => null,
            'name' => 'Unassigned',
            'payment_status' => 'unpaid',
            'items' => collect([]),
            'subtotal' => 0,
            'tax' => 0,
            'sc' => 0,
            'total' => 0,
        ];
        $hasUnassigned = false;

        foreach ($items as $item) {
            $friends = [];
            if (! empty($item->friend_name)) {
                $friends = array_filter(array_map('trim', explode(',', $item->friend_name)));
            }

            if (empty($friends)) {
                $hasUnassigned = true;
                $splitItem = clone $item;
                $unassignedBreakdown['items']->push($splitItem);
                $unassignedBreakdown['subtotal'] += $item->price;
            } else {
                $count = count($friends);
                $sharePrice = $item->price / $count;
                foreach ($friends as $friendName) {
                    $upperFriend = strtoupper($friendName);
                    if (isset($memberBreakdowns[$upperFriend])) {
                        $splitItem = clone $item;
                        $splitItem->price = $sharePrice;
                        if ($count > 1) {
                            $splitItem->name = $item->name.' (SPLIT 1/'.$count.')';
                        }
                        $memberBreakdowns[$upperFriend]['items']->push($splitItem);
                        $memberBreakdowns[$upperFriend]['subtotal'] += $sharePrice;
                    } else {
                        $hasUnassigned = true;
                        $splitItem = clone $item;
                        $splitItem->price = $sharePrice;
                        if ($count > 1) {
                            $splitItem->name = $item->name.' (SPLIT 1/'.$count.') ['.$friendName.']';
                        } else {
                            $splitItem->name = $item->name.' ['.$friendName.']';
                        }
                        $unassignedBreakdown['items']->push($splitItem);
                        $unassignedBreakdown['subtotal'] += $sharePrice;
                    }
                }
            }
        }

        foreach ($memberBreakdowns as $name => $breakdownData) {
            $member = $members->first(function ($m) use ($name) {
                return strtoupper($m->name) === strtoupper($name);
            });
            $breakdownData['id'] = $member ? $member->id : null;
            $breakdownData['payment_status'] = $member ? ($member->payment_status ?? 'unpaid') : 'unpaid';

            $proportion = $totalMenuSubtotal > 0 ? ($breakdownData['subtotal'] / $totalMenuSubtotal) : 0;
            $breakdownData['tax'] = $proportion * $totalTax;
            $breakdownData['sc'] = $proportion * $totalSc;
            $breakdownData['total'] = $breakdownData['subtotal'] + $breakdownData['tax'] + $breakdownData['sc'];
            $breakdown[] = $breakdownData;
        }

        if ($hasUnassigned) {
            $proportion = $totalMenuSubtotal > 0 ? ($unassignedBreakdown['subtotal'] / $totalMenuSubtotal) : 0;
            $unassignedBreakdown['tax'] = $proportion * $totalTax;
            $unassignedBreakdown['sc'] = $proportion * $totalSc;
            $unassignedBreakdown['total'] = $unassignedBreakdown['subtotal'] + $unassignedBreakdown['tax'] + $unassignedBreakdown['sc'];
            $breakdown[] = $unassignedBreakdown;
        }

        return collect($breakdown);
    }

    private function distributeEvenly(array $members, string $key, int $total, int $subtotal): array
    {
        if ($total === 0 || $subtotal === 0) {
            foreach ($members as $i => $m) {
                $members[$i][$key] = 0;
            }

            return $members;
        }

        // Round down each share
        $shares = [];
        $allocated = 0;
        foreach ($members as $m) {
            $share = (int) floor($m['subtotal'] * $total / $subtotal);
            $shares[] = $share;
            $allocated += $share;
        }

        // Distribute remainder to largest fractional remainders
        $remainder = $total - $allocated;
        if ($remainder > 0) {
            $fractions = [];
            foreach ($members as $i => $m) {
                $exact = $m['subtotal'] * $total / $subtotal;
                $fractions[$i] = $exact - floor($exact);
            }
            arsort($fractions);
            $i = 0;
            foreach ($fractions as $idx => $frac) {
                if ($i >= $remainder) {
                    break;
                }
                $shares[$idx]++;
                $i++;
            }
        }

        foreach ($members as $i => $m) {
            $members[$i][$key] = $shares[$i];
        }

        return $members;
    }
}
