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
            if (! Schema::hasColumn('schools', 'access_status')) {
                $table->string('access_status')->default('active');
            }
            if (! Schema::hasColumn('schools', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable();
            }
            if (! Schema::hasColumn('schools', 'suspension_reason')) {
                $table->text('suspension_reason')->nullable();
            }
            if (! Schema::hasColumn('schools', 'subscription_plan')) {
                $table->string('subscription_plan')->default('free');
            }
            if (! Schema::hasColumn('schools', 'subscription_status')) {
                $table->string('subscription_status')->default('active');
            }
            if (! Schema::hasColumn('schools', 'subscription_starts_at')) {
                $table->date('subscription_starts_at')->nullable();
            }
            if (! Schema::hasColumn('schools', 'subscription_ends_at')) {
                $table->date('subscription_ends_at')->nullable();
            }
            if (! Schema::hasColumn('schools', 'subscription_note')) {
                $table->text('subscription_note')->nullable();
            }
        });

        Schema::table('school_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('school_applications', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
        });

        $applicationIndexes = Schema::getIndexes('school_applications');
        $applicationForeignKeys = Schema::getForeignKeys('school_applications');
        $userForeignKey = collect($applicationForeignKeys)->first(
            fn (array $foreignKey) => ($foreignKey['columns'] ?? []) === ['user_id']
        );
        $requiresForeignKeyRecreation = DB::connection()->getDriverName() === 'mysql' && $userForeignKey;
        if ($requiresForeignKeyRecreation) {
            Schema::table('school_applications', fn (Blueprint $table) => $table->dropForeign($userForeignKey['name']));
        }

        foreach ($applicationIndexes as $index) {
            if (($index['unique'] ?? false) && ($index['columns'] ?? []) === ['user_id']) {
                Schema::table('school_applications', fn (Blueprint $table) => $table->dropUnique($index['name']));
            }
        }

        $hasApplicationLookupIndex = collect($applicationIndexes)->contains(
            fn (array $index) => array_slice($index['columns'] ?? [], 0, 2) === ['user_id', 'status']
        );
        if (! $hasApplicationLookupIndex) {
            Schema::table('school_applications', fn (Blueprint $table) => $table->index(['user_id', 'status']));
        }

        if ($requiresForeignKeyRecreation) {
            Schema::table('school_applications', fn (Blueprint $table) => $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete());
        }

        if (! Schema::hasTable('owner_admin_actions')) {
            Schema::create('owner_admin_actions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('school_application_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action');
                $table->string('summary');
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['created_at', 'action']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_admin_actions');
    }
};
