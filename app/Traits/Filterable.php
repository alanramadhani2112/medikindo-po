<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait Filterable
{
    /**
     * Scope a query to apply filters from the request.
     *
     * @param Builder $query
     * @param Request $request
     * @param array $filterMappings [ 'param_name' => 'column_name or callable' ]
     * @return Builder
     */
    public function scopeFilter(Builder $query, Request $request, array $filterMappings = []): Builder
    {
        // Global search (default column is 'name' unless specified in mappings)
        if ($request->filled('search')) {
            $search = $request->get('search');
            $searchColumn = $filterMappings['search_column'] ?? 'name';
            
            if (is_callable($searchColumn)) {
                $searchColumn($query, $search);
            } else {
                $query->where($searchColumn, 'like', "%{$search}%");
            }
        }

        // Date Range
        if ($request->filled('date_from')) {
            $column = $filterMappings['date_column'] ?? 'created_at';
            $query->whereDate($column, '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $column = $filterMappings['date_column'] ?? 'created_at';
            $query->whereDate($column, '<=', $request->get('date_to'));
        }

        // Apply specific column filters
        foreach ($filterMappings as $param => $target) {
            // Skip reserved mapping keys
            if (in_array($param, ['search_column', 'date_column'])) continue;

            if ($request->filled($param)) {
                $value = $request->get($param);

                if (is_callable($target)) {
                    $target($query, $value);
                } else {
                    $query->where($target, $value);
                }
            }
        }

        return $query;
    }
}
