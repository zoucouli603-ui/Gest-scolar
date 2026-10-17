<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolInvitation extends Model
{
    protected $fillable = [
        'school_id',
        'email',
        'role',
        'token',
        'expires_at',
        'accepted_at',
        'invited_by',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
