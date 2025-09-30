<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use App\Models\Security\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function index()
    {
        try {
            $skills = Skill::all();
            return response()->json($skills);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name'      => 'required|string|max:255',
                'category'  => 'required|string|max:100',
                'criteria'  => 'required|string|max:100',
                'reference' => 'nullable|string|max:255',
            ]);

            $skill = Skill::create($data);

            return response()->json($skill, 201);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $skill = Skill::findOrFail($id);
            return response()->json($skill);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Skill not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $skill = Skill::findOrFail($id);

            $data = $request->validate([
                'name'      => 'sometimes|required|string|max:255',
                'category'  => 'sometimes|required|string|max:100',
                'criteria'  => 'sometimes|required|string|max:100',
                'reference' => 'nullable|string|max:255',
            ]);

            $skill->update($data);

            return response()->json($skill);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $skill = Skill::findOrFail($id);
            $skill->delete();

            return response()->json(['message' => 'Skill deleted successfully']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
