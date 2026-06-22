<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index()
    {
        $trips = auth()->user()->trips()->latest()->get();

        return view('trips.index', compact('trips'));
    }

    public function create()
    {
        return view('trips.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'participants' => 'nullable|array',
            'participants.*' => 'string|max:255',
        ]);

        $trip = auth()->user()->trips()->create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => 'active',
        ]);

        if ($request->has('participants')) {
            foreach ($request->participants as $participantName) {
                if (! empty(trim($participantName))) {
                    $trip->participants()->create([
                        'name' => trim($participantName),
                    ]);
                }
            }
        }

        return redirect()->route('trips.index')->with('success', 'Trip Berhasil Dibuat!');
    }

    public function show(Trip $trip)
    {
        if ($trip->user_id !== auth()->id()) {
            abort(403);
        }

        $activities = $trip->activities()->latest()->get();
        $totalSpent = $activities->sum('total_amount');
        $activeSessions = $activities->where('status', 'active')->count();

        // Consolidated Settlement Calculations
        $consolidated = [];
        foreach ($activities as $activity) {
            $breakdowns = $activity->member_breakdown;
            foreach ($breakdowns as $b) {
                if ($b['name'] === 'Unassigned') {
                    continue;
                }
                $normalizedName = strtoupper($b['name']);
                if (! isset($consolidated[$normalizedName])) {
                    $consolidated[$normalizedName] = [
                        'name' => $b['name'],
                        'total' => 0.0,
                        'paid' => 0.0,
                        'unpaid' => 0.0,
                        'is_fully_paid' => true,
                    ];
                }
                $consolidated[$normalizedName]['total'] += $b['total'];
                if ($b['payment_status'] === 'paid') {
                    $consolidated[$normalizedName]['paid'] += $b['total'];
                } else {
                    $consolidated[$normalizedName]['unpaid'] += $b['total'];
                    $consolidated[$normalizedName]['is_fully_paid'] = false;
                }
            }
        }

        return view('trips.show', compact('trip', 'activities', 'totalSpent', 'activeSessions', 'consolidated'));
    }

    public function destroy(Trip $trip)
    {
        if ($trip->user_id !== auth()->id()) {
            abort(403);
        }

        $trip->delete();

        return redirect()->route('trips.index')->with('success', 'Trip Berhasil Dihapus!');
    }

    public function sharedShow($uuid)
    {
        $trip = Trip::where('uuid', $uuid)->firstOrFail();
        $activities = $trip->activities()->latest()->get();
        $totalSpent = $activities->sum('total_amount');
        $activeSessions = $activities->where('status', 'active')->count();

        $consolidated = [];
        foreach ($activities as $activity) {
            $breakdowns = $activity->member_breakdown;
            foreach ($breakdowns as $b) {
                if ($b['name'] === 'Unassigned') {
                    continue;
                }
                $normalizedName = strtoupper($b['name']);
                if (! isset($consolidated[$normalizedName])) {
                    $consolidated[$normalizedName] = [
                        'name' => $b['name'],
                        'total' => 0.0,
                        'paid' => 0.0,
                        'unpaid' => 0.0,
                        'is_fully_paid' => true,
                    ];
                }
                $consolidated[$normalizedName]['total'] += $b['total'];
                if ($b['payment_status'] === 'paid') {
                    $consolidated[$normalizedName]['paid'] += $b['total'];
                } else {
                    $consolidated[$normalizedName]['unpaid'] += $b['total'];
                    $consolidated[$normalizedName]['is_fully_paid'] = false;
                }
            }
        }

        return view('trips.shared', compact('trip', 'activities', 'totalSpent', 'activeSessions', 'consolidated'));
    }

    public function toggleMemberPayment(Request $request, Trip $trip)
    {
        if ($trip->user_id !== auth()->id()) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $memberName = $request->input('member_name');
        if (empty($memberName)) {
            return response()->json(['error' => 'Nama anggota diperlukan.'], 422);
        }

        $activities = $trip->activities;

        $anyUnpaid = false;
        foreach ($activities as $activity) {
            $breakdowns = $activity->member_breakdown;
            foreach ($breakdowns as $b) {
                if (strtoupper($b['name']) === strtoupper($memberName) && $b['payment_status'] !== 'paid') {
                    $anyUnpaid = true;
                    break 2;
                }
            }
        }

        $targetStatus = $anyUnpaid ? 'paid' : 'unpaid';

        foreach ($activities as $activity) {
            $updated = false;
            foreach ($activity->members as $m) {
                if (strtoupper($m->name) === strtoupper($memberName)) {
                    $m->payment_status = $targetStatus;
                    $m->save();
                    $updated = true;
                }
            }
            if ($updated) {
                $totalMembersCount = $activity->members()->count();
                $paidMembersCount = $activity->members()->where('payment_status', 'paid')->count();
                if ($totalMembersCount > 0 && $totalMembersCount === $paidMembersCount) {
                    $activity->status = 'settled';
                } else {
                    $activity->status = 'active';
                }
                $activity->save();
            }
        }

        $newUnpaidTotal = 0;
        $newTotal = 0;
        foreach ($trip->activities()->get() as $activity) {
            foreach ($activity->member_breakdown as $b) {
                if (strtoupper($b['name']) === strtoupper($memberName)) {
                    $newTotal += $b['total'];
                    if ($b['payment_status'] !== 'paid') {
                        $newUnpaidTotal += $b['total'];
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'member_name' => $memberName,
            'target_status' => $targetStatus,
            'unpaid_total' => $newUnpaidTotal,
            'total' => $newTotal,
            'is_fully_paid' => $targetStatus === 'paid',
        ]);
    }
}
