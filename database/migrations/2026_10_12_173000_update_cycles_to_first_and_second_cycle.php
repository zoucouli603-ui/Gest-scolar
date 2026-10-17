<?php

use App\Models\School;
use App\Models\SchoolApplication;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('class_rooms')->where('cycle', 'Primaire')->update(['cycle' => '1er Cycle']);
        DB::table('class_rooms')->where('cycle', 'Secondaire')->update(['cycle' => '2è Cycle']);

        $schools = DB::table('schools')->get(['id', 'cycles']);
        foreach ($schools as $school) {
            if ($school->cycles) {
                $decoded = json_decode($school->cycles, true);
                if (is_array($decoded)) {
                    $updated = array_map(function ($c) {
                        return match ($c) {
                            'Primaire' => '1er Cycle',
                            'Secondaire' => '2è Cycle',
                            default => $c,
                        };
                    }, $decoded);
                    DB::table('schools')->where('id', $school->id)->update([
                        'cycles' => json_encode(array_values(array_unique($updated))),
                    ]);
                }
            }
        }

        $applications = DB::table('school_applications')->get(['id', 'cycles']);
        foreach ($applications as $app) {
            if ($app->cycles) {
                $decoded = json_decode($app->cycles, true);
                if (is_array($decoded)) {
                    $updated = array_map(function ($c) {
                        return match ($c) {
                            'Primaire' => '1er Cycle',
                            'Secondaire' => '2è Cycle',
                            default => $c,
                        };
                    }, $decoded);
                    DB::table('school_applications')->where('id', $app->id)->update([
                        'cycles' => json_encode(array_values(array_unique($updated))),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('class_rooms')->where('cycle', '1er Cycle')->update(['cycle' => 'Primaire']);
        DB::table('class_rooms')->where('cycle', '2è Cycle')->update(['cycle' => 'Secondaire']);

        $schools = DB::table('schools')->get(['id', 'cycles']);
        foreach ($schools as $school) {
            if ($school->cycles) {
                $decoded = json_decode($school->cycles, true);
                if (is_array($decoded)) {
                    $updated = array_map(function ($c) {
                        return match ($c) {
                            '1er Cycle' => 'Primaire',
                            '2è Cycle' => 'Secondaire',
                            default => $c,
                        };
                    }, $decoded);
                    DB::table('schools')->where('id', $school->id)->update([
                        'cycles' => json_encode(array_values(array_unique($updated))),
                    ]);
                }
            }
        }

        $applications = DB::table('school_applications')->get(['id', 'cycles']);
        foreach ($applications as $app) {
            if ($app->cycles) {
                $decoded = json_decode($app->cycles, true);
                if (is_array($decoded)) {
                    $updated = array_map(function ($c) {
                        return match ($c) {
                            '1er Cycle' => 'Primaire',
                            '2è Cycle' => 'Secondaire',
                            default => $c,
                        };
                    }, $decoded);
                    DB::table('school_applications')->where('id', $app->id)->update([
                        'cycles' => json_encode(array_values(array_unique($updated))),
                    ]);
                }
            }
        }
    }
};

