<?php

namespace App\Services;

use App\Models\MedicalReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MedicalReportService
{
    public function list()
    {
        return MedicalReport::with(['uploader', 'approver'])->latest()->get();
    }

    public function store(array $data)
    {
        // simpan file ke storage/public/medical_reports
        $path = $data['file']->store('medical_reports', 'public');

        return MedicalReport::create([
            'type'        => $data['type'],
            'date'        => $data['date'],
            'file_path'   => $path,
            'uploaded_by' => Auth::id(),
            'status'      => 'pending',
            'notes'       => $data['notes'] ?? null,
        ]);
    }

    public function approve(MedicalReport $report, ?string $notes = null)
    {
        return $report->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'notes'       => $notes,
        ]);
    }

    public function reject(MedicalReport $report, ?string $notes = null)
    {
        return $report->update([
            'status'      => 'rejected',
            'approved_by' => Auth::id(),
            'notes'       => $notes,
        ]);
    }

    public function update(MedicalReport $report, array $data)
    {
        if (isset($data['file'])) {
            $path = $data['file']->store('medical_reports', 'public');
            $data['file_path'] = $path;
            unset($data['file']);
        }

        $report->update($data);

        return $report->fresh(['uploader', 'approver']);
    }

    public function filterByMonthYear($month, $year)
    {
        return MedicalReport::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->with(['uploader', 'approver'])
            ->get();
    }

    public function filterByRange($start, $end)
    {
        return MedicalReport::whereBetween('date', [$start, $end])
            ->with(['uploader', 'approver'])
            ->get();
    }
}
