<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolApplication extends Model
{
    protected $fillable = [
        'user_id',
        'role',
        'name',
        'logo_path',
        'address',
        'phone',
        'email',
        'country',
        'province',
        'academy',
        'cap',
        'currency',
        'institution_type',
        'school_year',
        'cycles',
        'rejection_reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'approved_school_id',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'cycles' => 'array',
        ];
    }

    public function applicant()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedSchool()
    {
        return $this->belongsTo(School::class, 'approved_school_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
