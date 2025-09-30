<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use App\Models\Security\PersonnelSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PersonnelSkillController extends Controller
{
    // List with filters
    public function index(Request $request)
    {
        try {
            $query = PersonnelSkill::with(['personnel', 'skill', 'logs.approver']);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('month') && $request->filled('year')) {
                $query->whereYear('created_at', $request->year)
                      ->whereMonth('created_at', $request->month);
            }

            return response()->json($query->paginate(20));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Assign skill to personnel
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validate([
                'personnel_id'     => 'required|exists:personnels,id',
                'skill_id'         => 'required|exists:skills,id',
                'certificate' => 'required',
                'member_card' => 'required',
                'valid_until' => 'required',
                'certificate_file' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
                'member_card_file'  => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            ]);

            if ($request->hasFile('certificate_file')) {
                $data['certificate_file'] = $request->file('certificate_file')->store('certificates', 'public');
            }
            if ($request->hasFile('member_card_file')) {
                $data['member_card_file'] = $request->file('member_card_file')->store('member_card_files', 'public');
            }

            // Default status pending
            $data['status'] = 'valid';

            $ps = PersonnelSkill::create($data);

            DB::commit();
            return response()->json($ps, 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Show detail with logs
    public function show($id)
    {
        try {
            $ps = PersonnelSkill::with(['personnel', 'skill', 'logs.approver'])->findOrFail($id);
            return response()->json($ps);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Data not found'], 404);
        }
    }

    // Approve / Reject
    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'status' => 'required|in:approved,rejected',
                'notes'  => 'nullable|string|max:500'
            ]);

            $ps = PersonnelSkill::findOrFail($id);
            $ps->update(['status' => $validated['status']]);

            $ps->logs()->create([
                'approved_by' => auth()->id(),
                'status'      => $validated['status'],
                'notes'       => $validated['notes'] ?? null,
            ]);

            DB::commit();
            return response()->json(['message' => "Skill {$validated['status']}"]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Analytics
    public function analytics()
    {
        try {
            $total    = PersonnelSkill::count();
            $approved = PersonnelSkill::where('status', 'approved')->count();
            $pending  = PersonnelSkill::where('status', 'pending')->count();

            return response()->json([
                'total'           => $total,
                'approved'        => $approved,
                'pending'         => $pending,
                'completion_rate' => $total ? round(($approved / $total) * 100, 2) : 0
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Download approved file (certificate or membership card)
    public function downloadFile($id, $type)
    {
        try {
            $ps = PersonnelSkill::where('status', 'approved')->findOrFail($id);

            if (!in_array($type, ['certificate', 'membership'], true)) {
                return response()->json(['error' => 'Invalid file type'], 400);
            }

            $filePath = $type === 'certificate'
                ? $ps->certificate_file
                : $ps->membership_card;

            if (!$filePath || !Storage::exists($filePath)) {
                return response()->json(['error' => 'File not found'], 404);
            }

            return Storage::download($filePath);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
