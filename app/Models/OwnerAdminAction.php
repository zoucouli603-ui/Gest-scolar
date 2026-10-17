<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerAdminAction extends Model
{
    protected $fillable = [
        'actor_user_id',
        'school_id',
        'school_application_id',
        'action',
        'summary',
        'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function application()
    {
        return $this->belongsTo(SchoolApplication::class, 'school_application_id');
    }
}
