<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'nationality')) {
                $table->string('nationality')->nullable()->after('birth_place');
            }

            if (! Schema::hasColumn('students', 'inscription_date')) {
                $table->date('inscription_date')->nullable()->after('nationality');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'nationality')) {
                $table->dropColumn('nationality');
            }

            if (Schema::hasColumn('students', 'inscription_date')) {
                $table->dropColumn('inscription_date');
            }
        });
    }
};
