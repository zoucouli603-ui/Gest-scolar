<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCurrentSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;
    use BelongsToCurrentSchool;

    protected $fillable = [
        'name',
        'code',
        'coefficient',
        'description',
        'school_year_id',
    ];

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function teachingAssignments()
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teaching_assignments')
            ->withPivot(['class_room_id', 'school_year_id'])
            ->withTimestamps();
    }

    public function classes()
    {
        return $this->belongsToMany(ClassRoom::class, 'teaching_assignments', 'subject_id', 'class_room_id')
            ->withPivot(['teacher_id', 'school_year_id'])
            ->withTimestamps();
    }
}
