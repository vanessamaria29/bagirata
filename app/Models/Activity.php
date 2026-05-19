<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory;

    /**
     * PBI 02: Activity Session CRUD
     * Kolom yang bisa diisi manual lewat form.
     */
    protected $fillable = [
        'user_id',      // ID si Host [cite: 60, 175]
        'title',        // Judul Sesi (PBI 02) [cite: 156, 176]
        'location',     // Lokasi (PBI 02) 
        'total_amount', // PBI 09: Summary Dashboard 
        'status',       // PBI 12: Settlement Tracker 
    ];

    /**
     * Relasi ke User (Satu sesi punya satu Host).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}