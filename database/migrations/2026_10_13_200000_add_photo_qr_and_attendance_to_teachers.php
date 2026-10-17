<?php

use App\Models\Teacher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('photo_path')->nullable();
            $table->string('qr_token', 64)->nullable()->unique();
        });

        DB::table('teachers')->whereNull('qr_token')->pluck('id')->each(
            fn ($id) => DB::table('teachers')->where('id', $id)->update(['qr_token' => Str::random(32)])
        );

        Schema::create('teacher_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->timestamps();
            $table->unique(['teacher_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_attendances');
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropUnique(['qr_token']);
            $table->dropColumn(['photo_path', 'qr_token']);
        });
    }
};
