<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_registration_number_unique');
            $table->unique(['school_year_id', 'registration_number'], 'students_year_registration_unique');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique('subjects_code_unique');
            $table->unique(['school_year_id', 'code'], 'subjects_year_code_unique');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropUnique('teachers_email_unique');
            $table->unique(['school_year_id', 'email'], 'teachers_year_email_unique');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropUnique('teachers_year_email_unique');
            $table->unique('email', 'teachers_email_unique');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique('subjects_year_code_unique');
            $table->unique('code', 'subjects_code_unique');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_year_registration_unique');
            $table->unique('registration_number', 'students_registration_number_unique');
        });
    }
};
