<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    // Mengizinkan field ini untuk diisi
    protected $fillable = [
        'activity_id',
        'name',
        'price',
        'friend_name',
    ];

    // Relasi balik ke Activity
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
