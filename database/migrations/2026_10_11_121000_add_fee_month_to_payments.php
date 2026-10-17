<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->date('fee_month')->nullable()->after('fee_type');
        });

        DB::table('payments')
            ->where('fee_type', 'scolarite')
            ->whereNull('fee_month')
            ->orderBy('id')
            ->chunkById(500, function ($payments): void {
                foreach ($payments as $payment) {
                    DB::table('payments')->where('id', $payment->id)->update([
                        'fee_month' => CarbonImmutable::parse($payment->payment_date)->startOfMonth()->toDateString(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('fee_month');
        });
    }
};
