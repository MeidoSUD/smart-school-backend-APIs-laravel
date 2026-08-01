<?php

namespace App\Http\Controllers\Api;

use App\Models\Grade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('GradeController');
    }

    public function index(Request $request): JsonResponse
    {
        $grades = Grade::all();

        return $this->successResponse(['grades' => $grades]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'min_percentage' => 'required|numeric|min:0|max:100',
            'max_percentage' => 'required|numeric|min:0|max:100|gte:min_percentage',
            'exam_type'      => 'nullable|string',
            'point'          => 'nullable|numeric',
            'description'    => 'nullable|string',
            'is_active'      => 'nullable|string',
        ]);

        $grade = Grade::create([
            'name'        => $validated['name'],
            'mark_from'   => $validated['min_percentage'],
            'mark_upto'   => $validated['max_percentage'],
            'exam_type'   => $validated['exam_type'] ?? null,
            'point'       => $validated['point'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active'   => $validated['is_active'] ?? null,
        ]);

        return $this->successResponse(['grade' => $grade], 'Grade created successfully', 201);
    }

    public function show($id): JsonResponse
    {
        $grade = Grade::find($id);

        if (!$grade) {
            return $this->errorResponse('Grade not found', null, 404);
        }

        return $this->successResponse(['grade' => $grade]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $grade = Grade::find($id);

        if (!$grade) {
            return $this->errorResponse('Grade not found', null, 404);
        }

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'min_percentage' => 'required|numeric|min:0|max:100',
            'max_percentage' => 'required|numeric|min:0|max:100|gte:min_percentage',
            'exam_type'      => 'nullable|string',
            'point'          => 'nullable|numeric',
            'description'    => 'nullable|string',
            'is_active'      => 'nullable|string',
        ]);

        $grade->update([
            'name'        => $validated['name'],
            'mark_from'   => $validated['min_percentage'],
            'mark_upto'   => $validated['max_percentage'],
            'exam_type'   => $validated['exam_type'] ?? null,
            'point'       => $validated['point'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active'   => $validated['is_active'] ?? null,
        ]);

        return $this->successResponse(['grade' => $grade], 'Grade updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $grade = Grade::find($id);

        if (!$grade) {
            return $this->errorResponse('Grade not found', null, 404);
        }

        $grade->delete();

        return $this->successResponse(null, 'Grade deleted successfully');
    }
}
