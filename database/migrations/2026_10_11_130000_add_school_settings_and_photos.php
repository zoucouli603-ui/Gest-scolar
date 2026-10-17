<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('establishment_type')->default('polyvalent');
            $table->string('website')->nullable();
            $table->string('city')->nullable();
            $table->string('commune')->nullable();
            $table->string('district')->nullable();
            $table->string('subdivision')->nullable();
            $table->string('inspection')->nullable();
            $table->boolean('maintenance_mode')->default(false);
        });

        Schema::create('school_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('title', 100)->nullable();
            $table->string('path');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_photos');

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'establishment_type',
                'website',
                'city',
                'commune',
                'district',
                'subdivision',
                'inspection',
                'maintenance_mode',
            ]);
        });
    }
};
