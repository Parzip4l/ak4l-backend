<?php 
// app/Http/Controllers/Api/V1/RikesAttendanceController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RikesAttendance;
use Illuminate\Http\Request;

class RikesAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = RikesAttendance::with('user');

        if ($request->has('month')) {
            $query->whereMonth('date', date('m', strtotime($request->month)))
                  ->whereYear('date', date('Y', strtotime($request->month)));
        }

        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'           => 'nullable|exists:users,id',
            'division'          => 'required|string|max:100',
            'department'        => 'required|string|max:100',
            'date'              => 'required|date',
            'attendance_status' => 'required|in:Y,N,OD',
            'result_status'     => 'nullable|in:FTW,FTTWN,TU',
        ]);

        // Rule tambahan: result_status hanya boleh ada kalau attendance_status = Y
        if ($validated['attendance_status'] !== 'Y') {
            $validated['result_status'] = null;
        } elseif (empty($validated['result_status'])) {
            return response()->json([
                'error' => 'Result status wajib diisi jika hadir (Y).'
            ], 422);
        }

        $attendance = RikesAttendance::create($validated);

        return response()->json($attendance, 201);
    }

    public function update(Request $request, RikesAttendance $attendance)
    {
        $validated = $request->validate([
            'division'   => 'sometimes|string|max:100',
            'department' => 'sometimes|string|max:100',
            'date'       => 'sometimes|date',
            'attendance_status' => 'required|in:Y,N,OD',
            'result_status'     => 'nullable|in:FTW,FTTWN,TU',
        ]);

        $attendance->update($validated);

        return response()->json($attendance);
    }

    public function destroy(RikesAttendance $attendance)
    {
        $attendance->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function recap(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $startDate = $validated['month'] . '-01';
        $endDate   = date("Y-m-t", strtotime($startDate));

        // Ambil data bulan itu
        $attendances = RikesAttendance::whereBetween('date', [$startDate, $endDate])->get();

        if ($attendances->isEmpty()) {
            return response()->json(['message' => 'No data found for this month'], 404);
        }

        // Grouping by division + department
        $grouped = $attendances->groupBy(function ($item) {
            return $item->division . '||' . $item->department;
        });

        $recap = $grouped->map(function ($records, $key) {
            [$division, $department] = explode('||', $key);

            $total       = $records->count();
            $hadir       = $records->where('attendance_status', 'Y')->count();
            $tidakHadir  = $records->where('attendance_status', 'N')->count();
            $offDuty     = $records->where('attendance_status', 'OD')->count();

            return [
                'division'               => $division,
                'department'             => $department,
                'total'                  => $total,
                'hadir'                  => $hadir,
                'tidak_hadir'            => $tidakHadir,
                'off_duty'               => $offDuty,
                'persentase_kehadiran'   => $total > 0 ? round(($hadir / $total) * 100, 2) : 0,
            ];
        })->values();

        return response()->json([
            'month' => $validated['month'],
            'rekap' => $recap,
        ]);
    }

}
