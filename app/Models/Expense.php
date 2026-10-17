<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCurrentSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    use BelongsToCurrentSchool;

    protected $fillable = [
        'school_year_id',
        'teacher_id',
        'expense_date',
        'category',
        'amount',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
