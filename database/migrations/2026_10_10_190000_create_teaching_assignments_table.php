<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_room_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['school_year_id', 'teacher_id', 'subject_id', 'class_room_id'],
                'teaching_assignments_unique_assignment'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
    }
};
