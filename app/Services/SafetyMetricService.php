<?php 

// app/Services/SafetyMetricService.php
namespace App\Services;

use App\Repositories\SafetyMetricRepository;
use App\Models\SafetyMetric;

class SafetyMetricService
{
    protected $repo;

    public function __construct(SafetyMetricRepository $repo)
    {
        $this->repo = $repo;
    }

    public function list()
    {
        return $this->repo->all();
    }

    public function get($id)
    {
        return $this->repo->find($id);
    }

    public function create(array $data)
    {
        return $this->repo->create($data);
    }

    public function update(SafetyMetric $metric, array $data)
    {
        return $this->repo->update($metric, $data);
    }

    public function delete(SafetyMetric $metric)
    {
        return $this->repo->delete($metric);
    }
}
