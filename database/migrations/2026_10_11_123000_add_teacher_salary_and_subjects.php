<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('teachers', 'monthly_salary')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->decimal('monthly_salary', 12, 2)->default(0)->after('specialty');
            });
        }

        if (!Schema::hasTable('teacher_subject')) {
            Schema::create('teacher_subject', function (Blueprint $table) {
                $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['teacher_id', 'subject_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_subject');
    }
};
