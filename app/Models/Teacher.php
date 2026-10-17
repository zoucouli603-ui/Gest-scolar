<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCurrentSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;
    use BelongsToCurrentSchool;

    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'phone',
        'email',
        'specialty',
        'monthly_salary',
        'status',
        'school_year_id',
        'photo_path',
        'qr_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (Teacher $teacher): void {
            $teacher->qr_token ??= \Illuminate\Support\Str::random(32);
        });
    }

    public function attendances()
    {
        return $this->hasMany(TeacherAttendance::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? url('storage/'.ltrim($this->photo_path, '/')) : null;
    }

    protected function casts(): array
    {
        return [
            'monthly_salary' => 'decimal:2',
        ];
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function teachingAssignments()
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teaching_assignments')
            ->withPivot(['class_room_id', 'school_year_id'])
            ->withTimestamps();
    }

    public function declaredSubjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject')
            ->withTimestamps();
    }

    public function classes()
    {
        return $this->belongsToMany(ClassRoom::class, 'teaching_assignments', 'teacher_id', 'class_room_id')
            ->withPivot(['subject_id', 'school_year_id'])
            ->withTimestamps();
    }

    public function assignedClasses()
    {
        return $this->belongsToMany(ClassRoom::class, 'teacher_class_assignments')
            ->withPivot('school_year_id')
            ->withTimestamps();
    }
}
