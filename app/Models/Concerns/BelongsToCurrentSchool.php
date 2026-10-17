<?php

namespace App\Models\Concerns;

use App\Tenancy\CurrentSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait BelongsToCurrentSchool
{
    protected static function bootBelongsToCurrentSchool(): void
    {
        static::addGlobalScope('current_school', function (Builder $builder): void {
            $schoolId = app(CurrentSchool::class)->id();
            if ($schoolId === null && auth()->check()) {
                $schoolId = auth()->user()->school_id;
            }
            if ($schoolId === null) {
                return;
            }

            $schoolYearIds = DB::table('school_years')
                ->select('id')
                ->where('school_id', $schoolId);

            $builder->whereIn(
                $builder->getModel()->qualifyColumn('school_year_id'),
                $schoolYearIds
            );
        });
    }
}
