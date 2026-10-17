<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->decimal('coefficient', 4, 2)->default(1.00);
        });

        if (Schema::hasColumn('subjects', 'coefficient')) {
            foreach (DB::table('teaching_assignments')->get(['id', 'subject_id']) as $assignment) {
                $coefficient = DB::table('subjects')
                    ->where('id', $assignment->subject_id)
                    ->value('coefficient');
                DB::table('teaching_assignments')
                    ->where('id', $assignment->id)
                    ->update(['coefficient' => $coefficient ?? 1]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->dropColumn('coefficient');
        });
    }
};
