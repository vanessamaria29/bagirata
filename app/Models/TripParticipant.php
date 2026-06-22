<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripParticipant extends Model
{
    protected $fillable = ['trip_id', 'name'];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
