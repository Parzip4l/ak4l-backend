<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rikes\RikesNapza;
use Illuminate\Support\Facades\Log;

class RikesNapzaController extends Controller
{
    public function index()
    {
        try {
            $data = RikesNapza::latest()->paginate(10);
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error index RikesNapza: '.$e->getMessage());
            return response()->json(['error' => 'Gagal mengambil data'], 500);
        }
    }

    /**
     * Simpan data baru
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validateRequest($request);

            // pastikan format periode konsisten YYYY-MM
            $periode = date('Y-m', strtotime($validated['periode']));

            // cek apakah data dengan periode sama sudah ada
            $existing = RikesNapza::where('periode', $periode)->first();

            if ($existing) {

                // lakukan update jika user setuju
                $existing->update($validated);

                return response()->json([
                    'status'  => 'updated',
                    'message' => 'Data berhasil diperbarui',
                    'data'    => $existing,
                ], 200);
            }

            // buat data baru kalau belum ada
            $napza = RikesNapza::create($validated);

            return response()->json([
                'status'  => 'created',
                'message' => 'Data berhasil disimpan',
                'data'    => $napza,
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error store RikesNapza: '.$e->getMessage());
            return response()->json(['error' => 'Gagal menyimpan data'], 500);
        }
    }


    /**
     * Tampilkan single data
     */
    public function show(RikesNapza $rikesNapza)
    {
        try {
            return response()->json($rikesNapza);
        } catch (\Exception $e) {
            Log::error('Error show RikesNapza: '.$e->getMessage());
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }
    }

    /**
     * Update data
     */
    public function update(Request $request, RikesNapza $rikesNapza)
    {
        try {
            $validated = $this->validateRequest($request, true);

            $rikesNapza->update($validated);
            return response()->json($rikesNapza);
        } catch (\Exception $e) {
            Log::error('Error update RikesNapza: '.$e->getMessage());
            return response()->json(['error' => 'Gagal update data'], 500);
        }
    }

    /**
     * Hapus data
     */
    public function destroy(RikesNapza $rikesNapza)
    {
        try {
            $rikesNapza->delete();
            return response()->json(['message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            Log::error('Error destroy RikesNapza: '.$e->getMessage());
            return response()->json(['error' => 'Gagal menghapus data'], 500);
        }
    }

    /**
     * Filter analytics berdasarkan bulan & tahun
     */
    public function filterByMonth(Request $request)
    {
        try {
            $validated = $request->validate([
                'month' => 'required|integer|min:1|max:12',
                'year'  => 'required|integer|min:2000',
            ]);

            // Format periode sekarang
            $periode = sprintf('%04d-%02d', $validated['year'], $validated['month']);

            // Ambil data bulan yang dipilih
            $current = RikesNapza::where('periode', $periode)->first();

            if (!$current) {
                return response()->json([
                    'filter'  => $validated,
                    'summary' => null,
                    'records' => [],
                    'message' => 'Data tidak ditemukan untuk periode ini'
                ], 404);
            }

            // Hitung bulan sebelumnya
            $prevMonth = $validated['month'] - 1;
            $prevYear  = $validated['year'];

            if ($prevMonth === 0) {
                $prevMonth = 12;
                $prevYear -= 1;
            }

            $prevPeriode = sprintf('%04d-%02d', $prevYear, $prevMonth);
            $previous = RikesNapza::where('periode', $prevPeriode)->first();

            // summary bulan sekarang
            $summary = [
                'total_passed'     => $current->passed,
                'total_not_passed' => $current->not_passed,
                'kehadiran'        => $current->kehadiran,
                'target'           => $current->target,
                'keterangan'       => $current->keterangan,
            ];

            // bandingkan dengan bulan sebelumnya
            $trend = [];
            if ($previous) {
                $trend = [
                    'passed'     => $current->passed > $previous->passed ? 'naik' : ($current->passed < $previous->passed ? 'turun' : 'tetap'),
                    'not_passed' => $current->not_passed > $previous->not_passed ? 'naik' : ($current->not_passed < $previous->not_passed ? 'turun' : 'tetap'),
                    'kehadiran'  => $current->kehadiran > $previous->kehadiran ? 'naik' : ($current->kehadiran < $previous->kehadiran ? 'turun' : 'tetap'),
                    'target'     => $current->target > $previous->target ? 'naik' : ($current->target < $previous->target ? 'turun' : 'tetap'),
                ];
            } else {
                $trend = [
                    'passed'     => 'tidak ada data sebelumnya',
                    'not_passed' => 'tidak ada data sebelumnya',
                    'kehadiran'  => 'tidak ada data sebelumnya',
                    'target'     => 'tidak ada data sebelumnya',
                ];
            }

            return response()->json([
                'filter'    => $validated,
                'summary'   => $summary,
                'records'   => $current,
                'trend'     => $trend,
                'prev_data' => $previous,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error filterByMonth RikesNapza: '.$e->getMessage());
            return response()->json(['error' => 'Gagal filter data'], 500);
        }
    }

    public function filterByYear(Request $request)
    {
        try {
            $validated = $request->validate([
                'year' => 'required|integer|min:2000',
            ]);

            $year = $validated['year'];

            // Ambil semua data tahun yang dipilih (periode format: YYYY-MM)
            $current = RikesNapza::where('periode', 'like', $year.'-%')->get();

            if ($current->isEmpty()) {
                return response()->json([
                    'filter'  => $validated,
                    'summary' => null,
                    'records' => [],
                    'message' => 'Data tidak ditemukan untuk tahun ini'
                ], 404);
            }

            // Ambil data tahun sebelumnya
            $previous = RikesNapza::where('periode', 'like', ($year - 1).'-%')->get();

            // summary tahun sekarang
            $summary = [
                'total_passed'     => $current->sum('passed'),
                'total_not_passed' => $current->sum('not_passed'),
                'avg_kehadiran'    => round($current->avg('kehadiran'), 2),
                'target'           => round($current->avg('target'), 2),
                'periode'          => $year,
            ];

            // summary tahun sebelumnya (jika ada)
            $prevSummary = null;
            if ($previous->isNotEmpty()) {
                $prevSummary = [
                    'total_passed'     => $previous->sum('passed'),
                    'total_not_passed' => $previous->sum('not_passed'),
                    'avg_kehadiran'    => round($previous->avg('kehadiran'), 2),
                    'target'           => round($previous->avg('target'), 2),
                    'periode'          => $year - 1,
                ];
            }

            // bandingkan dengan tahun sebelumnya
            $trend = [];
            if ($prevSummary) {
                $trend = [
                    'passed'     => $summary['total_passed'] > $prevSummary['total_passed'] ? 'naik' : ($summary['total_passed'] < $prevSummary['total_passed'] ? 'turun' : 'tetap'),
                    'not_passed' => $summary['total_not_passed'] > $prevSummary['total_not_passed'] ? 'naik' : ($summary['total_not_passed'] < $prevSummary['total_not_passed'] ? 'turun' : 'tetap'),
                    'kehadiran'  => $summary['avg_kehadiran'] > $prevSummary['avg_kehadiran'] ? 'naik' : ($summary['avg_kehadiran'] < $prevSummary['avg_kehadiran'] ? 'turun' : 'tetap'),
                    'target'     => $summary['target'] > $prevSummary['target'] ? 'naik' : ($summary['target'] < $prevSummary['target'] ? 'turun' : 'tetap'),
                ];
            } else {
                $trend = [
                    'passed'     => 'tidak ada data tahun sebelumnya',
                    'not_passed' => 'tidak ada data tahun sebelumnya',
                    'kehadiran'  => 'tidak ada data tahun sebelumnya',
                    'target'     => 'tidak ada data tahun sebelumnya',
                ];
            }

            return response()->json([
                'filter'     => $validated,
                'summary'    => $summary,
                'records'    => $current,
                'trend'      => $trend,
                'prev_data'  => $prevSummary,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error filterByYear RikesNapza: '.$e->getMessage());
            return response()->json(['error' => 'Gagal filter data'], 500);
        }
    }





    /**
     * DRY - validasi request
     */
    private function validateRequest(Request $request, $isUpdate = false)
    {
        $rules = [
            'periode'    => $isUpdate ? 'sometimes|date' : 'required|date',
            'passed'     => 'nullable|integer|min:0',
            'not_passed' => 'nullable|integer|min:0',
            'kehadiran'  => 'nullable|numeric|min:0|max:100',
            'target'     => 'nullable|numeric|min:0|max:100',
            'keterangan' => 'nullable|string',
        ];

        return $request->validate($rules);
    }
}
