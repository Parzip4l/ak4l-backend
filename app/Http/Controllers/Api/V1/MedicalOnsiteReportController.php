<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MedicalOnsite\MedicalOnsiteReport;
use App\Models\MedicalOnsite\MedicalOnsiteApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MedicalOnsiteReportController extends Controller
{
    public function index(Request $request)
    {
        try {
            $month = $request->get('month');
            $query = MedicalOnsiteReport::with('approvals', 'submitter');

            if ($month) {
                $query->where('month', $month);
            }

            return response()->json($query->get());
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:laporan_kegiatan,absensi_kehadiran,penggunaan_obat,limbah',
                'month' => 'required|date_format:Y-m-d',
                'file' => 'nullable|file|mimes:pdf,doc,docx,xlsx,png,jpg|max:2048',
                'notes' => 'nullable|string'
            ]);

            $path = null;
                if ($request->hasFile('file')) {
                    $path = $request->file('file')->store('medical_reports', 'public');
                }

            $report = MedicalOnsiteReport::create([
                'type' => $validated['type'],
                'month' => $validated['month'],
                'submitted_by' => Auth::id(),
                'file_path' => $path,
                'status' => 'submitted',
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json($report, 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function approve(Request $request, MedicalOnsiteReport $report)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:approved,rejected',
                'notes' => 'nullable|string'
            ]);

            $approval = MedicalOnsiteApproval::create([
                'report_id' => $report->id,
                'approved_by' => Auth::id(),
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $report->update(['status' => $validated['status']]);

            return response()->json([
                'message' => "Report {$validated['status']} successfully",
                'approval' => $approval
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, MedicalOnsiteReport $report)
    {
        try {
            if (in_array($report->status, ['approved', 'rejected'])) {
                return response()->json(['error' => 'Report sudah tidak bisa diubah'], 400);
            }

            $validated = $request->validate([
                'type' => 'sometimes|in:laporan_kegiatan,absensi_kehadiran,penggunaan_obat,limbah',
                'month' => 'sometimes|date_format:Y-m-d',
                'file' => 'nullable|file|mimes:pdf,doc,docx,xlsx,png,jpg|max:2048',
                'notes' => 'nullable|string'
            ]);

            if ($request->hasFile('file')) {
                if ($report->file_path) {
                    Storage::delete($report->file_path);
                }
                $validated['file_path'] = $request->file('file')->store('medical_reports');
            }

            $report->update($validated);

            return response()->json($report);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(MedicalOnsiteReport $report)
    {
        try {
            if ($report->status === 'approved') {
                return response()->json(['error' => 'Report yang sudah approved tidak bisa dihapus'], 400);
            }

            if ($report->file_path) {
                Storage::delete($report->file_path);
            }

            $report->delete();

            return response()->json(['message' => 'Report deleted successfully']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Filter
    public function filterData(Request $request)
    {
        try {
            $validated = $request->validate([
                'year'   => 'required|integer|min:2000|max:' . date('Y'),
                'month'  => 'nullable|integer|min:1|max:12',
                'status' => 'nullable|in:pending,approved,rejected,submitted',
            ]);

            $query = MedicalOnsiteReport::with(['uploader', 'approvals'])
                ->whereYear('month', $validated['year']);

            if (!empty($validated['month'])) {
                $query->whereMonth('month', $validated['month']);
            }

            if (!empty($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            $reports = $query->orderBy('month', 'desc')->get();

            return response()->json($reports);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function recap(Request $request)
    {
        try {
            $validated = $request->validate([
                'year'  => 'required|integer|min:2000|max:' . date('Y'),
                'month' => 'nullable|integer|min:1|max:12',
            ]);

            $query = MedicalOnsiteReport::query()->whereYear('month', $validated['year']);

            if (!empty($validated['month'])) {
                $query->whereMonth('month', $validated['month']);
            }

            $total = $query->count();
            $approved = (clone $query)->where('status', 'approved')->count();
            $pending = (clone $query)->where('status', 'pending')->count();
            $rejected = (clone $query)->where('status', 'rejected')->count();

            return response()->json([
                'year'     => $validated['year'],
                'month'    => $validated['month'] ?? null,
                'total'    => $total,
                'approved' => $approved,
                'pending'  => $pending,
                'rejected' => $rejected,
                'growth'   => $this->calculateGrowth($validated['year'], $validated['month'] ?? null),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function calculateGrowth($year, $month = null)
    {
        $currentQuery = MedicalOnsiteReport::whereYear('month', $year);
        $prevQuery = MedicalOnsiteReport::query();

        if ($month) {
            $currentQuery->whereMonth('month', $month);

            // cari bulan sebelumnya
            $prevMonth = $month - 1;
            $prevYear  = $year;
            if ($prevMonth <= 0) {
                $prevMonth = 12;
                $prevYear  = $year - 1;
            }
            $prevQuery->whereYear('month', $prevYear)->whereMonth('month', $prevMonth);
        } else {
            // bandingkan dengan tahun sebelumnya
            $prevQuery->whereYear('month', $year - 1);
        }

        $currentTotal = $currentQuery->count();
        $prevTotal = $prevQuery->count();

        if ($prevTotal == 0) {
            return $currentTotal > 0 ? 100 : 0;
        }

        return round((($currentTotal - $prevTotal) / $prevTotal) * 100, 2);
    }

    // app/Http/Controllers/Api/V1/MedicalOnsiteReportController.php

    public function monthlyTrend(Request $request)
    {
        try {
            $validated = $request->validate([
                'year' => 'required|integer|min:2000|max:' . date('Y'),
            ]);

            $year = $validated['year'];

            $data = MedicalOnsiteReport::selectRaw("
                    MONTH(month) as month,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                ")
                ->whereYear('month', $year)
                ->groupByRaw("MONTH(month)")
                ->orderByRaw("MONTH(month)")
                ->get();

            // pastikan semua bulan terisi, kalau tidak ada data set 0
            $months = collect(range(1, 12))->map(function ($m) use ($data) {
                $found = $data->firstWhere('month', $m);
                return [
                    'month'    => $m,
                    'total'    => $found->total ?? 0,
                    'approved' => $found->approved ?? 0,
                    'pending'  => $found->pending ?? 0,
                    'rejected' => $found->rejected ?? 0,
                ];
            });

            return response()->json([
                'year' => $year,
                'data' => $months
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function approvalLogs(MedicalOnsiteReport $report)
    {
        try {
            $logs = $report->approvals()->with('approver')->get();
            return response()->json($logs);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(MedicalOnsiteReport $report)
    {
        try {
            // ambil detail report beserta logs
            $report = $report->load([
                'submitter:id,name,email',
                'approvals.approver:id,name,email'
            ]);

            return response()->json($report);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
