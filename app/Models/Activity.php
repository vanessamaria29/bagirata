<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = ['title', 'location', 'event_date', 'status', 'total_amount'];

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
}