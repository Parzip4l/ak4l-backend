<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalReportRequest;
use App\Http\Requests\UpdateMedicalReportRequest;
use App\Models\MedicalReport;
use App\Services\MedicalReportService;
use Illuminate\Http\Request;

class MedicalReportController extends Controller
{
    protected MedicalReportService $service;

    public function __construct(MedicalReportService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json($this->service->list());
    }

    public function store(StoreMedicalReportRequest $request)
    {
        $report = $this->service->store($request->validated() + ['file' => $request->file('file')]);
        return response()->json($report, 201);
    }

    public function show(MedicalReport $medicalReport)
    {
        return response()->json($medicalReport->load(['uploader', 'approver']));
    }

    public function update(UpdateMedicalReportRequest $request, MedicalReport $medicalReport)
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file');
        }

        $updated = $this->service->update($medicalReport, $data);

        return response()->json($updated);
    }

    public function approve(MedicalReport $medicalReport, Request $request)
    {
        $this->service->approve($medicalReport, $request->input('notes'));
        return response()->json(['message' => 'Report approved']);
    }

    public function reject(MedicalReport $medicalReport, Request $request)
    {
        $this->service->reject($medicalReport, $request->input('notes'));
        return response()->json(['message' => 'Report rejected']);
    }

    public function filter(Request $request)
    {
        $month = $request->query('month');
        $year = $request->query('year');

        $reports = $this->service->filterByMonthYear($month, $year);

        return response()->json($reports);
    }

    public function range(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after_or_equal:start',
        ]);

        $reports = $this->service->filterByRange($request->start, $request->end);

        return response()->json($reports);
    }
}
