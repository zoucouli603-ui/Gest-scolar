<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('teacher_class_assignments')) {
            Schema::create('teacher_class_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
                $table->foreignId('class_room_id')->constrained()->cascadeOnDelete();
                $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
        }

        Schema::table('teacher_class_assignments', function (Blueprint $table) {
            $table->unique(
                ['teacher_id', 'class_room_id', 'school_year_id'],
                'teacher_class_year_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_class_assignments');
    }
};
