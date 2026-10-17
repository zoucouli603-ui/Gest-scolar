<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('students', 'photo_path')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('photo_path')->nullable()->after('status');
            });
        }

        DB::table('students')
            ->whereIn('status', ['suspendu', 'suspendus', 'suspended'])
            ->update(['status' => 'inactif']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('students', 'photo_path')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('photo_path');
            });
        }
    }
};