<?php 


namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BUJP\BujpReport;
use App\Models\BUJP\BujpApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BUJPReportController extends Controller
{
    /**
     * List laporan dengan filter (month, year, status)
     */
    public function index(Request $request)
    {
        try {
            $query = BujpReport::with(['submitter', 'approvals.approver']);

            // Filter by month & year
            if ($request->filled('month') && $request->filled('year')) {
                $query->whereMonth('month', $request->month)
                      ->whereYear('month', $request->year);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $reports = $query->latest()->get();

            return response()->json($reports);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store laporan baru
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'type' => 'required|in:kegiatan,absensi,obat,limbah,lain',
                'month' => 'required|date',
                'file' => 'nullable|file|mimes:pdf,doc,docx,xlsx',
                'notes' => 'nullable|string',
            ]);

            if ($request->hasFile('file')) {
                $data['file_path'] = $request->file('file')->store('bujp/reports');
            }

            $data['submitted_by'] = Auth::id();
            $data['status'] = 'pending';

            $report = BujpReport::create($data);

            return response()->json($report, 201);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Approve laporan
     */
    public function approve(BujpReport $bujpReport, Request $request)
    {
        return $this->processApproval($bujpReport, 'approved', $request->notes);
    }

    /**
     * Reject laporan
     */
    public function reject(BujpReport $bujpReport, Request $request)
    {
        return $this->processApproval($bujpReport, 'rejected', $request->notes);
    }

    /**
     * Log approval
     */
    public function approvalLogs(BujpReport $bujpReport)
    {
        try {
            $logs = $bujpReport->approvals()->with('approver')->get();
            return response()->json($logs);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Download file (hanya approved)
     */
    public function download(BujpReport $bujpReport)
    {
        try {

            if (!$bujpReport->file_path || !Storage::exists($bujpReport->file_path)) {
                return response()->json(['error' => 'File tidak ditemukan'], 404);
            }

            return Storage::download($bujpReport->file_path);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Internal approval processing
     */
    private function processApproval(BujpReport $report, string $action, ?string $notes = null)
    {
        try {
            DB::transaction(function () use ($report, $action, $notes) {
                $report->update(['status' => $action, 'notes' => $notes]);

                BujpApproval::create([
                    'report_id' => $report->id,
                    'approved_by' => Auth::id(),
                    'action' => $action,
                    'notes' => $notes,
                ]);
            });

            return response()->json(['message' => "Report {$action}"]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function filter(Request $request)
    {
        try {
            $validated = $request->validate([
                'year'   => 'required|integer|min:2000|max:' . date('Y'),
                'month'  => 'nullable|integer|min:1|max:12',
                'status' => 'nullable|in:pending,approved,rejected,submitted',
            ]);

            $query = BujpReport::with(['uploader', 'approvals'])
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

    public function show(BujpReport $bujpReport)
    {
        try {
            // ambil detail report beserta logs
            $report = $bujpReport->load([
                'submitter:id,name,email',
                'approvals.approver:id,name,email'
            ]);

            return response()->json($report);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
