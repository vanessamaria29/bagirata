<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = ['title', 'location', 'event_date', 'status', 'total_amount', 'tax', 'service_charge', 'split_type'];

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
                    'name'     => $member->name,
                    'items'    => collect([]), // Item tetap kosong karena patungan bareng
                    'subtotal' => $sharedSubtotal,
                    'tax'      => $sharedTax,
                    'sc'       => $sharedSc,
                    'total'    => $perPerson
                ];
            }
            return collect($breakdown);
        }

        // JIKA MODE SESUAI PESANAN (PROPORSIONAL LAMA)
        $totalMenuSubtotal = $items->sum('price');
        $totalTax = $this->tax ?? 0;
        $totalSc = $this->service_charge ?? 0;

        foreach ($members as $member) {
            $memberItems = $items->where('friend_name', $member->name);
            $memberSubtotal = $memberItems->sum('price');

            $proportion = $totalMenuSubtotal > 0 ? ($memberSubtotal / $totalMenuSubtotal) : 0;

            $memberTax = $proportion * $totalTax;
            $memberSc = $proportion * $totalSc;
            $memberTotal = $memberSubtotal + $memberTax + $memberSc;

            $breakdown[] = [
                'name'     => $member->name,
                'items'    => $memberItems,
                'subtotal' => $memberSubtotal,
                'tax'      => $memberTax,
                'sc'       => $memberSc,
                'total'    => $memberTotal
            ];
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