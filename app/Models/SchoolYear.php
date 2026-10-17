<?php

namespace App\Models;

use App\Tenancy\CurrentSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SchoolYear extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_current', 'school_id'];

    protected static function booted(): void
    {
        static::addGlobalScope('current_school', function (Builder $builder): void {
            $schoolId = app(CurrentSchool::class)->id();
            if ($schoolId !== null) {
                $builder->where($builder->getModel()->qualifyColumn('school_id'), $schoolId);
                } elseif (auth()->check()) {
                    $builder->where('school_id', auth()->user()->school_id);
                }
            });
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function classes()
    {
        return $this->hasMany(ClassRoom::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function timetableEntries()
    {
        return $this->hasMany(TimetableEntry::class);
    }
}
