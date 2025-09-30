<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MedicalOnsite\MedicalOnsiteReport;
use App\Models\BUJP\BujpReport;

class ReportSummaryController extends Controller
{
    public function pending()
    {
        try {
            $medicalPending = MedicalOnsiteReport::where('status', 'submitted')->count();
            $bujpPending    = BujpReport::where('status', 'pending')->count();

            // bisa disesuaikan kalau mau grouping per type
            $medicalUrgent = MedicalOnsiteReport::where('status', 'submitted')
                ->whereDate('month', '<', now()->subDays(7))
                ->count();

            $bujpUrgent = BujpReport::where('status', 'pending')
                ->whereDate('month', '<', now()->subDays(7))
                ->count();

            $pendingReports = [
                [
                    'type'   => 'Medical Onsite',
                    'count'  => $medicalPending,
                    'urgent' => $medicalUrgent,
                ],
                [
                    'type'   => 'BUJP',
                    'count'  => $bujpPending,
                    'urgent' => $bujpUrgent,
                ],
            ];

            return response()->json($pendingReports);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
