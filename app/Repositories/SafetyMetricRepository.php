<?php

namespace App\Repositories;

use App\Models\SafetyMetric;
use Illuminate\Support\Carbon;

class SafetyMetricRepository
{
    public function all()
    {
        return SafetyMetric::with('creator')->latest()->get();
    }

    public function find($id)
    {
        return SafetyMetric::with('creator')->findOrFail($id);
    }

    /**
     * Finds a safety metric record by month and year.
     *
     * @param string $month The name of the month (e.g., 'January').
     * @param int $year The four-digit year (e.g., 2024).
     * @return SafetyMetric|null
     */
    public function findByMonthAndYear(string $month, int $year)
    {
        return SafetyMetric::where('month', $month)
                           ->whereYear('created_at', $year)
                           ->first();
    }

    public function create(array $data)
    {
        return SafetyMetric::create($data);
    }

    public function update(SafetyMetric $metric, array $data)
    {
        $metric->update($data);
        return $metric;
    }

    public function delete(SafetyMetric $metric)
    {
        return $metric->delete();
    }
}