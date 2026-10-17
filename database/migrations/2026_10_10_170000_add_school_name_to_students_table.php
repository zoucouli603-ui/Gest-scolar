<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('students', 'school_name')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('school_name')->nullable()->after('inscription_date');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('students', 'school_name')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('school_name');
            });
        }
    }
};