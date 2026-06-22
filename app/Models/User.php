<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable; // WAJIB ADA

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'currency',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * PBI 02: Activity Session CRUD
     * Relasi ke Activity (Satu User bisa punya banyak Sesi).
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Relasi ke Trip (Satu User bisa punya banyak Trip Folder).
     */
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }
}
