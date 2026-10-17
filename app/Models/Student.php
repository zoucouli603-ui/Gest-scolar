<?php

namespace App\Models;
use App\Models\Concerns\BelongsToCurrentSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
 use HasFactory;
 use BelongsToCurrentSchool;

 protected $fillable = [
    'registration_number',
    'first_name',
    'last_name',
    'gender',
    'birth_date',
    'birth_place',
    'nationality',
    'inscription_date',
    'class_name',
    'class_room_id',
    'parent_name',
    'parent_phone',
   'school_name',
    'school_year_id',
    'status',
    'photo_path',
    'birth_certificate_path',
 ];

 public function schoolYear()
 {
    return $this->belongsTo(schoolYear::class);
 }

 public function classRoom()
 {
    return $this->belongsTo(ClassRoom::class, 'class_room_id');
 }

 public function genderLabel(): string
 {
    $gender = mb_strtolower(trim((string) $this->gender));
    $gender = str_replace(['é', 'è', 'ê', 'ë'], 'e', $gender);

    return match ($gender) {
        'f', 'feminin', 'female' => 'Féminin',
        'm', 'masculin', 'male' => 'Masculin',
        default => '—',
    };
 }

 public function payments()
 {
    return $this->hasMany(Payment::class);
 }
}
