<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCurrentSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    use BelongsToCurrentSchool;

    protected $fillable = [
        'student_id',
        'class_room_id',
        'school_year_id',
        'amount',
        'fee_type',
        'fee_month',
        'is_subsidy',
        'payment_date',
        'payment_method',
        'reference',
        'receipt_number',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee_month' => 'date',
            'is_subsidy' => 'boolean',
            'payment_date' => 'date',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class);
    }
}
