<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['schools', 'school_applications'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->string('academy')->nullable();
                $table->string('cap')->nullable();
                if ($tableName === 'schools') {
                    $table->json('card_settings')->nullable();
                }
            });
        }

        Schema::table('students', function (Blueprint $table) {
            $table->string('birth_certificate_path')->nullable();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('is_subsidy')->default(false);
        });

        Schema::table('school_invitations', function (Blueprint $table) {
            $table->string('role')->default('staff');
        });
    }

    public function down(): void
    {
        Schema::table('school_invitations', fn (Blueprint $table) => $table->dropColumn('role'));
        Schema::table('payments', fn (Blueprint $table) => $table->dropColumn('is_subsidy'));
        Schema::table('students', fn (Blueprint $table) => $table->dropColumn('birth_certificate_path'));
        Schema::table('schools', fn (Blueprint $table) => $table->dropColumn(['academy', 'cap', 'card_settings']));
        Schema::table('school_applications', fn (Blueprint $table) => $table->dropColumn(['academy', 'cap']));
    }
};
