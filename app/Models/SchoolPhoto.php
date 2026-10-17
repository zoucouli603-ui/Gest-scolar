<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolPhoto extends Model
{
    protected $fillable = [
        'school_id',
        'title',
        'path',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
