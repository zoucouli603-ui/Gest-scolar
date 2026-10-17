<?php

namespace App\Support;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class SearchFilter
{
    /**
     * Chaque mot saisi doit correspondre à au moins une colonne (ordre des mots indifférent,
     * ex. « Coulibaly Zoumana » ou « Zoumana Coulibaly »). Les jokers LIKE saisis sont échappés.
     */
    public static function apply(Builder $query, ?string $search, array $columns, ?Closure $extra = null): Builder
    {
        $terms = preg_split('/\s+/u', trim((string) $search), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach (array_slice($terms, 0, 6) as $term) {
            $like = '%'.addcslashes($term, '\\%_').'%';

            $query->where(function ($group) use ($columns, $like, $extra) {
                foreach ($columns as $column) {
                    $group->orWhere($column, 'like', $like);
                }

                if ($extra) {
                    $extra($group, $like);
                }
            });
        }

        return $query;
    }
}
