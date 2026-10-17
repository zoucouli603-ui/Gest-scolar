<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCurrentSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;
    use BelongsToCurrentSchool;

    protected $fillable = [
        'school_year_id',
        'student_id',
        'subject_id',
        'class_room_id',
        'teacher_id',
        'evaluation_type',
        'trimester',
        'score',
        'max_score',
        'appreciation',
        'note_date',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'note_date' => 'date',
        'trimester' => 'integer',
    ];

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_room_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function getAverageAttribute(): ?float
    {
        if ($this->score === null || $this->max_score <= 0) {
            return null;
        }

        return round(($this->score / $this->max_score) * 100, 2);
    }
}
