<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    // Mengizinkan field ini untuk diisi melalui mass assignment
    protected $fillable = [
        'activity_id', 
        'name'
    ];

    // Relasi balik ke Activity
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}