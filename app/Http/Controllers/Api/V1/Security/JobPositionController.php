<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use App\Models\Security\Skill;
use App\Models\Security\JobPosition;
use Illuminate\Http\Request;

class JobPositionController extends Controller
{
    public function index()
    {
        try {
            $job = JobPosition::all();
            return response()->json($job);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name'   => 'required|string|max:255',
            ]);

            $job = JobPosition::create($data);

            return response()->json($job, 201);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $job = JobPosition::findOrFail($id);
            return response()->json($job);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Skill not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $job = JobPosition::findOrFail($id);

            $data = $request->validate([
                'name'      => 'sometimes|required|string|max:255',
            ]);

            $job->update($data);

            return response()->json($job);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $job = JobPosition::findOrFail($id);
            $job->delete();

            return response()->json(['message' => 'Job Position deleted successfully']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

