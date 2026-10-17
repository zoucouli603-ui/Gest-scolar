<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCurrentSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    use HasFactory;
    use BelongsToCurrentSchool;

    public const CYCLES = School::CYCLES;

    public function getGradeScaleAttribute(): ?int
    {
        return match ($this->cycle) {
            '1er Cycle', 'Primaire' => 10,
            '2è Cycle', 'Secondaire', 'Lycée' => 20,
            default => null,
        };
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name;
    }

    protected $table = 'class_rooms';

    protected $fillable = [
        'name',
        'cycle',
        'monthly_fee',
        'school_year_id',
    ];

    protected function casts(): array
    {
        return [
            'monthly_fee' => 'decimal:2',
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

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teaching_assignments', 'class_room_id', 'teacher_id')
            ->withPivot(['subject_id', 'school_year_id'])
            ->withTimestamps();
    }

    public function assignedTeachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_class_assignments')
            ->withPivot('school_year_id')
            ->withTimestamps();
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teaching_assignments', 'class_room_id', 'subject_id')
            ->withPivot(['teacher_id', 'school_year_id'])
            ->withTimestamps();
    }

    // Relation pour compter ou récupérer les élèves de cette classe
    public function students()
    {
        return Student::query()
            ->where(function ($query) {
                $query->where('class_room_id', $this->id)
                    ->orWhere(function ($unlinkedStudents) {
                        $unlinkedStudents->whereNull('class_room_id')
                            ->whereRaw('LOWER(TRIM(class_name)) = ?', [mb_strtolower(trim($this->name))]);
                    });
            })
            ->where(function ($query) {
                $query->where('school_year_id', $this->school_year_id)
                    ->orWhereNull('school_year_id');
            });
    }
}