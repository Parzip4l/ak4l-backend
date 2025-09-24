<?php 

// app/Services/SecurityMetricService.php
namespace App\Services;

use App\Models\SecurityMetric;
use App\Repositories\SecurityMetricRepository;
use Illuminate\Support\Facades\Auth;

class SecurityMetricService
{
    protected SecurityMetricRepository $repo;

    public function __construct(SecurityMetricRepository $repo)
    {
        $this->repo = $repo;
    }

    public function list(?int $categoryId = null, ?string $start = null, ?string $end = null)
    {
        return $this->repo->all($categoryId, $start, $end);
    }

    public function get(int $id)
    {
        return $this->repo->find($id);
    }

    public function store(array $data)
    {
        $data['reported_by'] = Auth::id();
        $data['status'] = 'pending';
        return $this->repo->create($data);
    }

    public function update(SecurityMetric $metric, array $data)
    {
        return $this->repo->update($metric, $data);
    }

    public function approve(SecurityMetric $metric, ?string $notes = null)
    {
        return $this->repo->update($metric, [
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);
    }

    public function reject(SecurityMetric $metric, ?string $notes = null)
    {
        return $this->repo->update($metric, [
            'status' => 'rejected',
            'approved_by' => Auth::id(),
        ]);
    }

    public function delete(SecurityMetric $metric)
    {
        return $this->repo->delete($metric);
    }
}
