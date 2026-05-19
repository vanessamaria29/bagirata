<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    // Pastikan fillable sudah diatur sesuai kebutuhanmu
    protected $fillable = ['title', 'location', 'event_date', 'status', 'total_amount'];

    // --- TAMBAHKAN RELASI INI ---
    public function members()
    {
        // Asumsi: Kamu punya tabel 'members' yang menyimpan 'activity_id'
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
}