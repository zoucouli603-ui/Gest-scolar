<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->json('cycles')->nullable();
        });

        Schema::table('school_applications', function (Blueprint $table) {
            $table->json('cycles')->nullable();
        });

        $defaultCycles = json_encode(['Maternelle', 'Primaire', 'Secondaire', 'Lycée']);
        DB::table('schools')->whereNull('cycles')->update(['cycles' => $defaultCycles]);
        DB::table('school_applications')->whereNull('cycles')->update(['cycles' => $defaultCycles]);
        DB::table('class_rooms')->where('cycle', 'Jardins')->update(['cycle' => 'Maternelle']);
    }

    public function down(): void
    {
        DB::table('class_rooms')->where('cycle', 'Maternelle')->update(['cycle' => 'Jardins']);

        Schema::table('school_applications', function (Blueprint $table) {
            $table->dropColumn('cycles');
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('cycles');
        });
    }
};
