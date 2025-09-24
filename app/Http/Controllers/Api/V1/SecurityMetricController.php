<?php 

// app/Http/Controllers/Api/V1/SecurityMetricController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSecurityMetricRequest;
use App\Http\Requests\UpdateSecurityMetricRequest;
use App\Models\SecurityMetric;
use App\Services\SecurityMetricService;
use Illuminate\Http\Request;

class SecurityMetricController extends Controller
{
    protected SecurityMetricService $service;

    public function __construct(SecurityMetricService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $reports = $this->service->list(
            $request->query('category_id'),
            $request->query('start'),
            $request->query('end')
        );

        return response()->json($reports);
    }

    public function store(StoreSecurityMetricRequest $request)
    {
        $metric = $this->service->store($request->validated());
        return response()->json($metric, 201);
    }

    public function show(SecurityMetric $securityMetric)
    {
        return response()->json($securityMetric->load(['category', 'reporter', 'approver']));
    }

    public function update(UpdateSecurityMetricRequest $request, SecurityMetric $securityMetric)
    {
        $metric = $this->service->update($securityMetric, $request->validated());
        return response()->json($metric);
    }

    public function approve(SecurityMetric $securityMetric)
    {
        $this->service->approve($securityMetric);
        return response()->json(['message' => 'Metric approved']);
    }

    public function reject(SecurityMetric $securityMetric)
    {
        $this->service->reject($securityMetric);
        return response()->json(['message' => 'Metric rejected']);
    }

    public function destroy(SecurityMetric $securityMetric)
    {
        $this->service->delete($securityMetric);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
