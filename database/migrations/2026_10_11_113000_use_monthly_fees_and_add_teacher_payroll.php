<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // The idempotent follow-up migration 120000 performs this conversion.
    }

    public function down(): void
    {
        // The follow-up migration owns the schema changes and rollback.
    }
};