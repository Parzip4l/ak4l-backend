<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rikes\RikesPradinas;
use Illuminate\Support\Facades\Log;

class RikesPradinasController extends Controller
{
    public function index()
    {
        try {
            $data = RikesPradinas::latest()->paginate(10);
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error index RikesPradinas: '.$e->getMessage());
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
            $existing = RikesPradinas::where('periode', $periode)->first();

            if ($existing) {

                // lakukan update
                $existing->update($validated);

                return response()->json([
                    'status'  => 'updated',
                    'message' => 'Data berhasil diperbarui',
                    'data'    => $existing,
                ], 200);
            }

            // buat baru jika belum ada
            $pradinas = RikesPradinas::create($validated);

            return response()->json([
                'status'  => 'created',
                'message' => 'Data berhasil disimpan',
                'data'    => $pradinas,
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error store RikesPradinas: '.$e->getMessage());
            return response()->json(['error' => 'Gagal menyimpan data'], 500);
        }
    }


    /**
     * Tampilkan single data
     */
    public function show(RikesPradinas $rikesPradinas)
    {
        try {
            return response()->json($rikesPradinas);
        } catch (\Exception $e) {
            Log::error('Error show RikesPradinas: '.$e->getMessage());
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }
    }

    /**
     * Update data
     */
    public function update(Request $request, RikesPradinas $rikesPradinas)
    {
        try {
            $validated = $this->validateRequest($request, true);

            $rikesPradinas->update($validated);
            return response()->json($rikesPradinas);
        } catch (\Exception $e) {
            Log::error('Error update RikesPradinas: '.$e->getMessage());
            return response()->json(['error' => 'Gagal update data'], 500);
        }
    }

    /**
     * Hapus data
     */
    public function destroy(RikesPradinas $rikesPradinas)
    {
        try {
            $rikesPradinas->delete();
            return response()->json(['message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            Log::error('Error destroy RikesPradinas: '.$e->getMessage());
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

            // Format periode sekarang (YYYY-MM)
            $periode = sprintf('%04d-%02d', $validated['year'], $validated['month']);

            // Ambil data bulan yang dipilih
            $current = RikesPradinas::where('periode', $periode)->first();

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
            $previous = RikesPradinas::where('periode', $prevPeriode)->first();

            // summary bulan sekarang
            $summary = [
                'asp'        => $current->asp,
                'occ'        => $current->occ,
                'sarana'     => $current->sarana,
                'prasarana'  => $current->prasarana,
                'target'     => $current->target,
                'keterangan' => $current->keterangan,
            ];

            // bandingkan dengan bulan sebelumnya
            $trend = [];
            if ($previous) {
                $trend = [
                    'asp'        => $current->asp > $previous->asp ? 'naik' : ($current->asp < $previous->asp ? 'turun' : 'tetap'),
                    'occ'        => $current->occ > $previous->occ ? 'naik' : ($current->occ < $previous->occ ? 'turun' : 'tetap'),
                    'sarana'     => $current->sarana > $previous->sarana ? 'naik' : ($current->sarana < $previous->sarana ? 'turun' : 'tetap'),
                    'prasarana'  => $current->prasarana > $previous->prasarana ? 'naik' : ($current->prasarana < $previous->prasarana ? 'turun' : 'tetap'),
                    'target'     => $current->target > $previous->target ? 'naik' : ($current->target < $previous->target ? 'turun' : 'tetap'),
                ];
            } else {
                $trend = [
                    'asp'        => 'tidak ada data sebelumnya',
                    'occ'        => 'tidak ada data sebelumnya',
                    'sarana'     => 'tidak ada data sebelumnya',
                    'prasarana'  => 'tidak ada data sebelumnya',
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
            \Log::error('Error filterByMonth RikesPradinas: '.$e->getMessage());
            return response()->json(['error' => 'Gagal filter data'], 500);
        }
    }

    public function filterByYear(Request $request)
    {
        try {
            $validated = $request->validate([
                'year'  => 'required|integer|min:2000',
            ]);

            $year = $validated['year'];

            // Ambil semua data dalam 1 tahun
            $records = RikesPradinas::where('periode', 'like', $year.'-%')
                ->orderBy('periode', 'asc')
                ->get();

            if ($records->isEmpty()) {
                return response()->json([
                    'filter'  => $validated,
                    'summary' => null,
                    'records' => [],
                    'message' => 'Data tidak ditemukan untuk tahun ini'
                ], 404);
            }

            // summary total tahunan
            $summary = [
                'asp'        => $records->sum('asp'),
                'occ'        => $records->sum('occ'),
                'sarana'     => $records->sum('sarana'),
                'prasarana'  => $records->sum('prasarana'),
                'target'     => $records->sum('target'),
            ];

            // buat trend per bulan
            $trend = [];
            $previous = null;
            foreach ($records as $record) {
                $monthName = \Carbon\Carbon::createFromFormat('Y-m', $record->periode)->format('F');

                if ($previous) {
                    $trend[$monthName] = [
                        'asp'        => $this->compareTrend($record->asp, $previous->asp),
                        'occ'        => $this->compareTrend($record->occ, $previous->occ),
                        'sarana'     => $this->compareTrend($record->sarana, $previous->sarana),
                        'prasarana'  => $this->compareTrend($record->prasarana, $previous->prasarana),
                        'target'     => $this->compareTrend($record->target, $previous->target),
                    ];
                } else {
                    $trend[$monthName] = [
                        'asp'        => 'tidak ada data sebelumnya',
                        'occ'        => 'tidak ada data sebelumnya',
                        'sarana'     => 'tidak ada data sebelumnya',
                        'prasarana'  => 'tidak ada data sebelumnya',
                        'target'     => 'tidak ada data sebelumnya',
                    ];
                }

                $previous = $record;
            }

            return response()->json([
                'filter'  => $validated,
                'summary' => $summary,
                'records' => $records,
                'trend'   => $trend,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error filterByYear RikesPradinas: '.$e->getMessage());
            return response()->json(['error' => 'Gagal filter data tahunan'], 500);
        }
    }


    /**
     * DRY - validasi request
     */
    private function validateRequest(Request $request, $isUpdate = false)
    {
        $rules = [
            'periode'    => $isUpdate ? 'sometimes|date' : 'required|date',
            'asp'        => 'nullable|integer|min:0|max:100',
            'occ'        => 'nullable|integer|min:0|max:100',
            'sarana'     => 'nullable|integer|min:0|max:100',
            'prasarana'  => 'nullable|integer|min:0|max:100',
            'target'     => 'nullable|numeric|min:0|max:100',
            'keterangan' => 'nullable|string',
        ];

        return $request->validate($rules);
    }

    private function compareTrend($current, $previous)
    {
        if ($current > $previous) {
            return 'naik';
        } elseif ($current < $previous) {
            return 'turun';
        }
        return 'tetap';
    }

}
