<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = ['title', 'location', 'event_date', 'status', 'total_amount', 'tax', 'service_charge'];

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

    public function getMemberBreakdownAttribute(): array
    {
        $items = $this->items;
        $members = $this->members;
        $subtotal = $items->sum('price');

        $itemsByMember = $items->groupBy(function ($item) {
            return $item->friend_name ?: '__unassigned__';
        });

        $names = $members->pluck('name')
            ->merge($itemsByMember->keys())
            ->unique()
            ->filter(function ($name) {
                return !is_null($name) && $name !== '';
            })
            ->values();

        $memberData = [];
        foreach ($names as $name) {
            $displayName = $name === '__unassigned__' ? 'Unassigned' : $name;
            $memberItems = $itemsByMember->get($name, collect());
            $memberData[] = [
                'name'     => $displayName,
                'items'    => $memberItems,
                'subtotal' => $memberItems->sum('price'),
            ];
        }

        // Distribute tax & SC proportionally with largest-remainder to absorb rounding
        $breakdown = $this->distributeEvenly($memberData, 'tax', $this->tax, $subtotal);
        $breakdown = $this->distributeEvenly($breakdown, 'sc', $this->service_charge, $subtotal);

        foreach ($breakdown as $i => $m) {
            $breakdown[$i]['total'] = $m['subtotal'] + $m['tax'] + $m['sc'];
        }

        return $breakdown;
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
                if ($i >= $remainder) break;
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