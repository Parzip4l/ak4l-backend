<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use App\Models\Security\Personnel;
use Illuminate\Http\Request;

class PersonnelController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Personnel::with('skills.skill','jobPosition');

            if ($request->filled('bujp_name')) {
                $query->where('bujp_name', 'like', '%' . $request->bujp_name . '%');
            }

            if ($request->filled('job_position')) {
                $query->where('job_position', 'like', '%' . $request->job_position . '%');
            }

            return response()->json($query->paginate(20));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name'            => 'required|string|max:255',
                'job_position_id' => 'required|exists:job_positions,id',
                'bujp'            => 'nullable|string|max:255',
                'kta_number'      => 'nullable|string|max:100',
                'code'            => 'required|string|max:50|unique:personnels,code',
                'photo'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('personnel_photos', 'public');
            }

            $personnel = Personnel::create($data);

            return response()->json($personnel, 201);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function show($id)
    {
        try {
            $personnel = Personnel::with(['skills.skill', 'skills.logs.approver'])->findOrFail($id);
            return response()->json($personnel);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Personnel not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $personnel = Personnel::findOrFail($id);

            $data = $request->validate([
                'name'            => 'sometimes|string|max:255',
                'job_position_id' => 'sometimes|exists:job_positions,id',
                'bujp'            => 'nullable|string|max:255',
                'kta_number'      => 'nullable|string|max:100',
                'code'            => "sometimes|string|max:50|unique:personnels,code,{$id}",
                'photo'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            // Handle new photo upload (replace old if exists)
            if ($request->hasFile('photo')) {
                if ($personnel->photo && \Storage::disk('public')->exists($personnel->photo)) {
                    \Storage::disk('public')->delete($personnel->photo);
                }
                $data['photo'] = $request->file('photo')->store('personnel_photos', 'public');
            }

            $personnel->update($data);

            return response()->json($personnel);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $personnel = Personnel::findOrFail($id);
            $personnel->delete();

            return response()->json(['message' => 'Personnel deleted successfully']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function analytics(Request $request)
    {
        try {
            $query = \App\Models\Security\Personnel::query();

            if ($request->filled('bujp')) {
                $query->where('bujp', 'like', '%' . $request->bujp . '%');
            }

            if ($request->filled('job_position_id')) {
                $query->where('job_position_id', $request->job_position_id);
            }

            $totalPersonnel = $query->count();

            // Hitung kategori skill (sertifikat) berdasarkan filter personnel
            $skillsByCategory = \App\Models\Security\Skill::withCount([
                'personnelSkills as total_certified' => function ($q) use ($request) {
                    $q->whereNotNull('certificate_file');

                    // filter personnel sesuai bujp / job_position
                    $q->whereHas('personnel', function ($p) use ($request) {
                        if ($request->filled('bujp')) {
                            $p->where('bujp', 'like', '%' . $request->bujp . '%');
                        }
                        if ($request->filled('job_position_id')) {
                            $p->where('job_position_id', $request->job_position_id);
                        }
                    });
                }
            ])->get()->map(function ($skill) use ($totalPersonnel) {
                return [
                    'skill_category' => $skill->category,
                    'skill_name'     => $skill->name,
                    'certified'      => $skill->total_certified,
                    'not_certified'  => $totalPersonnel - $skill->total_certified,
                ];
            });

            // Sertifikat expired dalam 1 bulan ke depan
            $expiringSoon = \App\Models\Security\PersonnelSkill::with('personnel', 'skill')
                ->whereNotNull('valid_until')
                ->whereBetween('valid_until', [now(), now()->addMonth()])
                ->whereHas('personnel', function ($p) use ($request) {
                    if ($request->filled('bujp')) {
                        $p->where('bujp', 'like', '%' . $request->bujp . '%');
                    }
                    if ($request->filled('job_position_id')) {
                        $p->where('job_position_id', $request->job_position_id);
                    }
                })
                ->get()
                ->map(function ($ps) {
                    return [
                        'personnel' => $ps->personnel->name ?? 'Unknown',
                        'skill'     => $ps->skill->name ?? 'Unknown',
                        'expiry'    => $ps->valid_until->format('Y-m-d'),
                    ];
                });

            return response()->json([
                'total_personnel' => $totalPersonnel,
                'skills_summary'  => $skillsByCategory,
                'expiring_soon'   => $expiringSoon,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
