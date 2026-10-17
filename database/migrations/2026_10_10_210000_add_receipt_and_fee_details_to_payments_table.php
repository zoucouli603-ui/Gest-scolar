<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('class_room_id')->nullable()->after('student_id')->constrained('class_rooms')->nullOnDelete();
            $table->string('fee_type', 30)->default('scolarite')->after('amount');
            $table->string('receipt_number')->nullable()->unique()->after('reference');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['class_room_id']);
            $table->dropUnique(['receipt_number']);
            $table->dropColumn(['class_room_id', 'fee_type', 'receipt_number']);
        });
    }
};
