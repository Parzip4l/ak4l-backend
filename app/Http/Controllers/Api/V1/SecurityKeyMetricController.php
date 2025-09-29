<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Security\SecurityKeyMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecurityKeyMetricController extends Controller
{
    // Tampilkan semua data
    public function index()
    {
        try {
            $metrics = SecurityKeyMetric::orderBy('month', 'desc')->get();
            return response()->json(['data' => $metrics]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Simpan data baru
    public function store(Request $request)
    {
        try {
            $request->validate([
                'month' => 'required|string',
                'kasus_kriminal' => 'nullable|integer',
                'kasus_ancaman_bom' => 'nullable|integer',
                'kasus_huru_hara' => 'nullable|integer',
                'kasus_vandalisme' => 'nullable|integer',
                'kasus_lainnya' => 'nullable|integer',
                'inspeksi_pengamanan' => 'nullable|integer',
                'investigasi_insiden_pengamanan' => 'nullable|integer',
                'audit_internal_smp' => 'nullable|integer',
                'simulasi_tanggap_darurat_pengamanan' => 'nullable|integer',
                'rapat_koordinasi_3_pilar' => 'nullable|integer',
            ]);

            $month = date('Y-m', strtotime($request->month));
            $existing = SecurityKeyMetric::where('month', 'like', "$month-%")->first();

            if ($existing) {
                    return response()->json([
                    'message' => 'Data untuk bulan dan tahun ini sudah ada. Apakah ingin mengupdate?',
                    'data' => $existing,
                    'exists' => true
                ]);
            }

            $metric = SecurityKeyMetric::create($request->all());

            return response()->json([
                'message' => 'Data berhasil disimpan',
                'data' => $metric
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Update data
    public function update(Request $request, SecurityKeyMetric $securityKeyMetric)
    {
        try {
            $request->validate([
                'month' => 'required|date',
                'kasus_kriminal' => 'nullable|integer',
                'kasus_ancaman_bom' => 'nullable|integer',
                'kasus_huru_hara' => 'nullable|integer',
                'kasus_vandalisme' => 'nullable|integer',
                'kasus_lainnya' => 'nullable|integer',
                'inspeksi_pengamanan' => 'nullable|integer',
                'investigasi_insiden_pengamanan' => 'nullable|integer',
                'audit_internal_smp' => 'nullable|integer',
                'simulasi_tanggap_darurat_pengamanan' => 'nullable|integer',
                'rapat_koordinasi_3_pilar' => 'nullable|integer',
            ]);

            $securityKeyMetric->update($request->all());

            return response()->json([
                'message' => 'Data berhasil diupdate',
                'data' => $securityKeyMetric
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Hapus data
    public function destroy(SecurityKeyMetric $securityKeyMetric)
    {
        try {
            $securityKeyMetric->delete();
            return response()->json(['message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Analytics: total kasus per bulan
    public function analyticsMonthly(Request $request)
    {
        try {
            $query = SecurityKeyMetric::query();

            // Filter by month (format YYYY-MM)
            if ($request->has('month')) {
                $month = $request->month; // contoh: 2025-09
                $query->whereRaw("DATE_FORMAT(month, '%Y-%m') = ?", [$month]);
            }

            // Filter by year (format YYYY)
            if ($request->has('year')) {
                $year = $request->year;
                $query->whereYear('month', $year);
            }

            $metrics = $query->orderBy('month', 'desc')->get();

            $categories = [
                'kasus_kriminal',
                'kasus_ancaman_bom',
                'kasus_huru_hara',
                'kasus_vandalisme',
                'kasus_lainnya',
                'inspeksi_pengamanan',
                'investigasi_insiden_pengamanan',
                'audit_internal_smp',
                'simulasi_tanggap_darurat_pengamanan',
                'rapat_koordinasi_3_pilar'
            ];

            $incidentCategories = [
                'kasus_kriminal',
                'kasus_ancaman_bom',
                'kasus_huru_hara',
                'kasus_vandalisme',
                'kasus_lainnya'
            ];

            $result = [];

            foreach ($metrics as $metric) {
                $previous = SecurityKeyMetric::where('month', '<', $metric->month)
                    ->orderBy('month', 'desc')
                    ->first();

                $row = $metric->toArray();
                $row['trend'] = [];

                // Hitung total insiden utama
                $row['total_insiden'] = array_sum(array_map(fn($cat) => $metric->$cat, $incidentCategories));

                if ($previous) {
                    foreach ($categories as $category) {
                        $row['trend'][$category] = $metric->$category > $previous->$category ? 'naik' :
                                                ($metric->$category < $previous->$category ? 'turun' : 'sama');
                    }
                } else {
                    foreach ($categories as $category) {
                        $row['trend'][$category] = null; // tidak ada bulan sebelumnya
                    }
                }

                $result[] = $row;
            }

            return response()->json(['data' => $result]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
