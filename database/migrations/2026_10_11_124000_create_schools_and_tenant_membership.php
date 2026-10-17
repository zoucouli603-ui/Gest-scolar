<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('country');
            $table->string('province')->nullable();
            $table->string('currency', 3);
            $table->string('institution_type');
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('role')->default('pending')->after('password');
        });

        Schema::table('school_years', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::create('school_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'email']);
        });

        $firstUser = DB::table('users')->orderBy('id')->first();
        if ($firstUser && DB::table('school_years')->exists()) {
            $schoolId = DB::table('schools')->insertGetId([
                'name' => 'Établissement existant',
                'country' => 'Non renseigné',
                'currency' => 'XOF',
                'institution_type' => 'private',
                'owner_user_id' => $firstUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('school_years')->whereNull('school_id')->update(['school_id' => $schoolId]);
            DB::table('users')->whereNull('school_id')->update([
                'school_id' => $schoolId,
                'role' => 'staff',
            ]);
            DB::table('users')->where('id', $firstUser->id)->update(['role' => 'school_admin']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_invitations');

        Schema::table('school_years', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_id');
            $table->dropColumn('role');
        });

        Schema::dropIfExists('schools');
    }
};
