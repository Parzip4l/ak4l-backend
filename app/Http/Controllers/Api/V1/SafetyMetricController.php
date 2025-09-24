<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\SafetyMetricRepository;
use App\Models\SafetyMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SafetyMetricController extends Controller
{
    protected $repo;
    

    public function __construct(SafetyMetricRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        return response()->json($this->repo->all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'month' => 'required|string',
            'fatality' => 'nullable|integer',
            'lost_time_injuries' => 'nullable|integer',
            'illness' => 'nullable|integer',
            'medical_treatment_cases' => 'nullable|integer',
            'first_aid_cases' => 'nullable|integer',
            'property_damage' => 'nullable|integer',
            'near_miss' => 'nullable|integer',
            'unsafe_action' => 'nullable|integer',
            'unsafe_condition' => 'nullable|integer',
            'work_hours' => 'required|integer|min:0',
            'lost_days' => 'nullable|integer',
            'safety_inspection' => 'boolean',
            'emergency_drill' => 'boolean',
            'incident_investigation' => 'boolean',
            'internal_audit' => 'boolean',
            'p2k3_meeting' => 'boolean',
            'safety_awareness' => 'boolean',
        ]);

        // Add a check for existing data based on month and year
        $month = $data['month'];
        $year = now()->year;

        // Check if data already exists for the specified month and year
        $existingRecord = $this->repo->findByMonthAndYear($month, $year);

        if ($existingRecord) {
            // If data exists, return a specific response to the client
            // The client-side logic will handle this response and show the "update" prompt
            return response()->json([
                'message' => 'Data untuk bulan ini sudah ada. Apakah Anda ingin mengupdate?',
                'existing_id' => $existingRecord->id,
                'status' => 'data_exists'
            ], 409); // Using HTTP 409 Conflict to indicate a conflict
        }

        // Mengambil nilai untuk perhitungan, menggunakan 0 jika tidak ada
        $fatality = $data['fatality'] ?? 0;
        $lostTimeInjuries = $data['lost_time_injuries'] ?? 0;
        $lostDays = $data['lost_days'] ?? 0;
        $workHours = $data['work_hours'];

        // Menghitung FAR, SR, dan FR
        if ($workHours > 0) {
            $data['far'] = ($fatality * 1000000) / $workHours;
            $data['sr'] = ($lostDays * 1000000) / $workHours;
            $data['fr'] = ($lostTimeInjuries * 1000000) / $workHours;
        } else {
            // Mencegah error pembagian dengan nol
            $data['far'] = 0;
            $data['sr'] = 0;
            $data['fr'] = 0;
        }

        $data['created_by'] = Auth::id();

        // Create a new record if no existing data is found
        return response()->json($this->repo->create($data), 201);
    }

    public function show($id)
    {
        return response()->json($this->repo->find($id));
    }

    public function update(Request $request, $id)
    {
        $metric = $this->repo->find($id);
        $metric = $this->repo->update($metric, $request->all());
        return response()->json($metric);
    }

    public function destroy($id)
    {
        $metric = $this->repo->find($id);
        $this->repo->delete($metric);
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function monthlySummary(Request $request)
    {
        $year = $request->year ?? date('Y');

        // Ambil semua metric untuk tahun tertentu
        $metrics = SafetyMetric::where('month', 'like', "$year-%")->get();

        $months = [
            'Jan'=>1,'Feb'=>2,'Mar'=>3,'Apr'=>4,'May'=>5,'Jun'=>6,
            'Jul'=>7,'Aug'=>8,'Sep'=>9,'Oct'=>10,'Nov'=>11,'Dec'=>12
        ];

        $result = [];

        foreach ($months as $name => $monthNumber) {
            $monthStr = sprintf("%04d-%02d", $year, $monthNumber); // "2025-09"

            $monthData = $metrics->where('month', $monthStr);

            $totalIncidents = $monthData->sum(function($item){
                return $item->fatality 
                    + $item->lost_time_injuries 
                    + $item->illness 
                    + $item->medical_treatment_cases
                    + $item->first_aid_cases
                    + $item->property_damage;
            });

            $totalNearMiss = $monthData->sum('near_miss');

            $result[] = [
                'name' => $name,
                'incidents' => $totalIncidents,
                'nearMiss' => $totalNearMiss,
            ];
        }

        return response()->json($result);
    }

    public function latest(Request $request)
    {
        try {
            $latestMetric = SafetyMetric::orderBy('month', 'desc')->first();

            if (!$latestMetric) {
                return response()->json([
                    'message' => 'No data available'
                ], 404);
            }

            return response()->json($latestMetric);
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Failed to fetch latest safety metric: '.$e->getMessage());

            return response()->json([
                'message' => 'Failed to fetch latest safety metric',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function latestByMonth(Request $request)
    {
        try {
            // Dapatkan tahun dan bulan dari request, default ke tahun dan bulan saat ini
            $year = $request->input('year', date('Y'));
            $month = $request->input('month', date('m'));
    
            // Format string bulan untuk pencarian di database (contoh: "2024-09")
            $monthString = sprintf("%04d-%02d", $year, $month);
    
            // Cari metrik terbaru untuk bulan yang spesifik
            $latestMetric = SafetyMetric::where('month', $monthString)
                                        ->orderBy('id', 'desc') // Asumsi id terurut, atau tambahkan kolom 'created_at'
                                        ->first();
    
            if (!$latestMetric) {
                return response()->json([
                    'message' => 'No data available for the specified month'
                ], 404);
            }
    
            return response()->json($latestMetric);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch latest safety metric by month: '.$e->getMessage());
    
            return response()->json([
                'message' => 'Failed to fetch latest safety metric by month',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
