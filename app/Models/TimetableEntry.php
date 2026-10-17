<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCurrentSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimetableEntry extends Model
{
    use HasFactory;
    use BelongsToCurrentSchool;

    protected $fillable = [
        'school_year_id',
        'teaching_assignment_id',
        'day_of_week',
        'starts_at',
        'ends_at',
        'room',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
        ];
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function teachingAssignment()
    {
        return $this->belongsTo(TeachingAssignment::class);
    }
}
