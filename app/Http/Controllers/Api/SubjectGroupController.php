<?php

namespace App\Http\Controllers\Api;

use App\Models\SubjectGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectGroupController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('SubjectGroupController');
    }

    public function index(Request $request): JsonResponse
    {
        $subjectGroups = SubjectGroup::with('subjects')->get();

        return $this->successResponse(['subject_groups' => $subjectGroups]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id'         => 'required|exists:sessions,id',
            'subject_group_name' => 'required|string|max:255',
            'description'        => 'nullable|string',
        ]);

        $subjectGroup = SubjectGroup::create([
            'session_id' => $validated['session_id'],
            'name'       => $validated['subject_group_name'],
            'description' => $validated['description'] ?? null,
        ]);

        $subjectGroup->load('subjects');

        return $this->successResponse(['subject_group' => $subjectGroup], 'Subject group created successfully', 201);
    }

    public function show($id): JsonResponse
    {
        $subjectGroup = SubjectGroup::with('subjects')->find($id);

        if (!$subjectGroup) {
            return $this->errorResponse('Subject group not found', null, 404);
        }

        return $this->successResponse(['subject_group' => $subjectGroup]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $subjectGroup = SubjectGroup::find($id);

        if (!$subjectGroup) {
            return $this->errorResponse('Subject group not found', null, 404);
        }

        $validated = $request->validate([
            'session_id'         => 'required|exists:sessions,id',
            'subject_group_name' => 'required|string|max:255',
            'description'        => 'nullable|string',
        ]);

        $subjectGroup->update([
            'session_id'  => $validated['session_id'],
            'name'        => $validated['subject_group_name'],
            'description' => $validated['description'] ?? null,
        ]);

        $subjectGroup->load('subjects');

        return $this->successResponse(['subject_group' => $subjectGroup], 'Subject group updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $subjectGroup = SubjectGroup::find($id);

        if (!$subjectGroup) {
            return $this->errorResponse('Subject group not found', null, 404);
        }

        $subjectGroup->subjects()->detach();
        $subjectGroup->delete();

        return $this->successResponse(null, 'Subject group deleted successfully');
    }
}
