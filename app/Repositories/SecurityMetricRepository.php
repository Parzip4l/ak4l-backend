<?php 

// app/Repositories/SecurityMetricRepository.php
namespace App\Repositories;

use App\Models\SecurityMetric;

class SecurityMetricRepository
{
    public function all(?int $categoryId = null, ?string $start = null, ?string $end = null)
    {
        $query = SecurityMetric::with(['category', 'reporter', 'approver'])->latest();

        if ($categoryId) {
            $query->where('incident_category_id', $categoryId);
        }

        if ($start && $end) {
            $query->whereBetween('date', [$start, $end]);
        }

        return $query->get();
    }

    public function find(int $id): SecurityMetric
    {
        return SecurityMetric::with(['category', 'reporter', 'approver'])->findOrFail($id);
    }

    public function create(array $data): SecurityMetric
    {
        return SecurityMetric::create($data);
    }

    public function update(SecurityMetric $metric, array $data): SecurityMetric
    {
        $metric->update($data);
        return $metric;
    }

    public function delete(SecurityMetric $metric): bool
    {
        return $metric->delete();
    }
}
