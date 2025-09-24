<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVisitorRequest;
use App\Models\VisitorRequest;
use App\Services\VisitorRequestService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VisitorRequestController extends Controller
{
    protected VisitorRequestService $service;

    public function __construct(VisitorRequestService $service)
    {
        $this->service = $service;
        // tambahkan middleware permission sesuai kebutuhan, misal:
        // $this->middleware('permission:visitor_requests.read')->only(['index','analytics']);
        // $this->middleware('permission:visitor_requests.create')->only(['store']);
        // $this->middleware('permission:visitor_requests.approve')->only(['approve','reject']);
    }

    public function index(Request $request)
    {
        $query = VisitorRequest::with('host');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('host_id')) {
            $query->where('host_id', $request->host_id);
        }

        if ($request->filled('company')) {
            $query->where('visitor_company', 'like', '%'.$request->company.'%');
        }

        if ($request->filled('start') && $request->filled('end')) {
            $query->whereBetween('visit_date', [$request->start, $request->end]);
        }

        return response()->json($query->latest()->paginate(10));
    }


    public function store(StoreVisitorRequest $request)
    {
        $data = $request->validated();
        $visitor = $this->service->store($data);
        return response()->json($visitor, 201);
    }

    public function approve(VisitorRequest $visitorRequest)
    {
        try {
            $this->service->approve($visitorRequest, auth()->user());
            return response()->json(['message' => 'Visitor request approved']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function reject(VisitorRequest $visitorRequest, Request $request)
    {
        try {
            $this->service->reject($visitorRequest, auth()->user(), $request->input('notes'));
            return response()->json(['message' => 'Visitor request rejected']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function complete(VisitorRequest $visitorRequest)
    {
        $visitorRequest->update([
            'status' => 'completed',
        ]);

        return response()->json(['message' => 'Visitor marked as completed']);
    }

    public function analytics(Request $request)
    {
        $month = $request->query('month'); // optional
        $data = $this->service->analytics($month);
        return response()->json($data);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $month = $request->query('month');
        $list  = $this->service->list($month);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="visitor_requests.csv"',
        ];

        $callback = function () use ($list) {
            $handle = fopen('php://output', 'w');
            // Header CSV
            fputcsv($handle, ['ID', 'Visitor Name', 'Host', 'Date', 'Status']);

            foreach ($list as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->visitor_name ?? '-',
                    optional($row->host)->name,
                    $row->visit_date,
                    $row->status,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function monthlySummary(Request $request)
    {
        $year = $request->query('year', date('Y'));

        $summary = VisitorRequest::selectRaw('MONTH(visit_date) as month, COUNT(*) as total')
            ->whereYear('visit_date', $year)
            ->groupByRaw('MONTH(visit_date)')
            ->orderByRaw('MONTH(visit_date)')
            ->get()
            ->map(function ($row) {
                return [
                    'month' => $row->month,
                    'total' => $row->total,
                ];
            });

        return response()->json([
            'year' => $year,
            'data' => $summary,
        ]);
    }

    public function topHosts(Request $request)
    {
        $month = $request->query('month', date('Y-m'));

        $summary = VisitorRequest::selectRaw('host_id, COUNT(*) as total')
            ->whereMonth('visit_date', substr($month, 5, 2))
            ->whereYear('visit_date', substr($month, 0, 4))
            ->groupBy('host_id')
            ->with('host')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return response()->json([
            'month' => $month,
            'top_hosts' => $summary,
        ]);
    }

    public function activeToday()
    {
        $today = now()->toDateString();

        $active = VisitorRequest::whereDate('visit_date', $today)
            ->whereIn('status', ['approved','onsite'])
            ->with('host')
            ->get();

        return response()->json([
            'date' => $today,
            'active_visitors' => $active,
        ]);
    }


}
