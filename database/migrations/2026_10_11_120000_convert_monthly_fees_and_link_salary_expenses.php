<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('class_rooms', 'annual_fee') && !Schema::hasColumn('class_rooms', 'monthly_fee')) {
            Schema::table('class_rooms', function (Blueprint $table) {
                $table->renameColumn('annual_fee', 'monthly_fee');
            });
        } elseif (!Schema::hasColumn('class_rooms', 'monthly_fee')) {
            throw new RuntimeException('The class_rooms table must contain annual_fee or monthly_fee before this migration can run.');
        }

        if (Schema::hasColumn('class_rooms', 'level')) {
            Schema::table('class_rooms', function (Blueprint $table) {
                $table->dropColumn('level');
            });
        }

        if (!Schema::hasColumn('expenses', 'teacher_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->foreignId('teacher_id')->nullable()->after('school_year_id')->constrained()->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('expenses', 'teacher_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropConstrainedForeignId('teacher_id');
            });
        }

        if (Schema::hasColumn('class_rooms', 'monthly_fee')) {
            Schema::table('class_rooms', function (Blueprint $table) {
                $table->renameColumn('monthly_fee', 'annual_fee');
            });
        }

        if (!Schema::hasColumn('class_rooms', 'level')) {
            Schema::table('class_rooms', function (Blueprint $table) {
                $table->string('level')->nullable();
            });
        }
    }
};
