<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Trip extends Model
{
    protected $fillable = ['uuid', 'name', 'description', 'user_id', 'status'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($trip) {
            if (empty($trip->uuid)) {
                $trip->uuid = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
