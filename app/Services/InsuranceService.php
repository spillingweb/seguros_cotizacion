<?php

namespace App\Services;

use App\Models\Insurance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class InsuranceService
{
    private const SORTABLE_COLUMNS = [
        'first_name', 'identification_number', 'destination_country',
        'departure_date', 'return_date', 'total_amount', 'status', 'created_at',
    ];

    public function paginate(Request $request): LengthAwarePaginator
    {
        $query = $this->buildFilteredQuery($request);
        $this->applySorting($query, $request);

        return $query->paginate(10);
    }

    public function stats(): array
    {
        $totals = Insurance::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'quoted' THEN 1 ELSE 0 END) as total_quoted")
            ->selectRaw("SUM(CASE WHEN status = 'contracted' THEN 1 ELSE 0 END) as total_contracted")
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total_amount')
            ->first();

        return [
            'total_quoted'     => (int) ($totals?->total_quoted ?? 0),
            'total_contracted' => (int) ($totals?->total_contracted ?? 0),
            'total_amount'     => (float) ($totals?->total_amount ?? 0),
        ];
    }

    private function buildFilteredQuery(Request $request): Builder
    {
        $query = Insurance::query();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('identification_number', 'like', "%{$search}%")
                    ->orWhere('destination_country', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->trim()->value()) {
            $query->where('status', $status);
        }

        return $query;
    }

    private function applySorting(Builder $query, Request $request): void
    {
        $column    = $request->string('sort')->trim()->value();
        $direction = $request->string('direction')->trim()->lower()->value();

        if ($column && in_array($column, self::SORTABLE_COLUMNS, true)) {
            $query->orderBy($column, $direction === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }
    }
}
