<?php 

// app/Http/Controllers/Api/V1/IncidentCategoryController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\IncidentCategory;
use Illuminate\Http\Request;

class IncidentCategoryController extends Controller
{
    public function index()
    {
        return response()->json(IncidentCategory::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:incident_categories',
            'description' => 'nullable|string',
        ]);

        $category = IncidentCategory::create($validated);

        return response()->json($category, 201);
    }

    public function update(Request $request, IncidentCategory $incidentCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:incident_categories,name,'.$incidentCategory->id,
            'description' => 'nullable|string',
        ]);

        $incidentCategory->update($validated);

        return response()->json($incidentCategory);
    }

    public function destroy(IncidentCategory $incidentCategory)
    {
        $incidentCategory->delete();
        return response()->json(['message' => 'Category deleted']);
    }
}
