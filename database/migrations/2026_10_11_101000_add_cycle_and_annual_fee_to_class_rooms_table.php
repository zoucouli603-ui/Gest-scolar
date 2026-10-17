<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_rooms', function (Blueprint $table) {
            $table->string('cycle', 30)->default('Primaire');
            $table->decimal('annual_fee', 12, 2)->default(0);
        });

        foreach (DB::table('class_rooms')->select('id', 'level')->get() as $classRoom) {
            $level = mb_strtolower((string) $classRoom->level);
            $cycle = match (true) {
                str_contains($level, 'jardin'), str_contains($level, 'matern') => 'Jardins',
                str_contains($level, 'lyc') => 'Lycées',
                str_contains($level, 'second'), str_contains($level, 'coll') => 'Secondaire',
                default => 'Primaire',
            };

            DB::table('class_rooms')->where('id', $classRoom->id)->update(['cycle' => $cycle]);
        }
    }

    public function down(): void
    {
        Schema::table('class_rooms', function (Blueprint $table) {
            $table->dropColumn(['cycle', 'annual_fee']);
        });
    }
};
