<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('class_rooms')->where('cycle', 'Lycées')->update(['cycle' => 'Lycée']);
    }

    public function down(): void
    {
        DB::table('class_rooms')->where('cycle', 'Lycée')->update(['cycle' => 'Lycées']);
    }
};
