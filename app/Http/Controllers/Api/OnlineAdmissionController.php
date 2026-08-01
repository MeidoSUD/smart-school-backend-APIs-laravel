<?php

namespace App\Http\Controllers\Api;

use App\Models\OnlineStudent;
use App\Models\ClassSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnlineAdmissionController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('OnlineAdmissionController');
    }

    public function index(Request $request): JsonResponse
    {
        $query = OnlineStudent::query();

        if ($request->has('status') && $request->status !== '') {
            $query->where('form_status', $request->status);
        }

        if ($request->has('class_id') && $request->class_id !== '') {
            $query->where('class_section_id', $request->class_id);
        }

        $admissions = $query->orderByDesc('id')->get();

        return $this->successResponse(['listonlineadmission' => $admissions]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_name' => 'required|string|max:255',
            'mobileno' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'dob' => 'required|date',
            'gender' => 'required|string|max:255',
            'class_id' => 'required|integer|exists:class_sections,id',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer',
            'religion' => 'nullable|string|max:255',
            'cast' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:255',
            'current_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'father_name' => 'nullable|string|max:255',
            'father_phone' => 'nullable|string|max:255',
            'father_occupation' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'mother_phone' => 'nullable|string|max:255',
            'mother_occupation' => 'nullable|string|max:255',
            'guardian_is' => 'nullable|string|max:255',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_relation' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:255',
            'guardian_email' => 'nullable|email|max:255',
            'guardian_occupation' => 'nullable|string|max:255',
            'guardian_address' => 'nullable|string',
            'blood_group' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'previous_school' => 'nullable|string|max:255',
            'height' => 'nullable|string|max:255',
            'weight' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'route_id' => 'nullable|integer',
            'vehroute_id' => 'nullable|integer',
            'hostel_room_id' => 'nullable|integer',
        ]);

        $validated['firstname'] = $validated['student_name'];
        unset($validated['student_name']);

        $validated['class_section_id'] = $validated['class_id'];
        unset($validated['class_id']);

        $validated['reference_no'] = 'REF-' . strtoupper(uniqid());
        $validated['form_status'] = 'pending';
        $validated['paid_status'] = 'unpaid';

        $admission = OnlineStudent::create($validated);

        return $this->successResponse(['admission' => $admission], 'Online admission submitted successfully', 201);
    }

    public function show($id): JsonResponse
    {
        $admission = OnlineStudent::with('classSection')->find($id);

        if (!$admission) {
            return $this->errorResponse('Online admission not found', null, 404);
        }

        return $this->successResponse(['admission' => $admission]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $admission = OnlineStudent::find($id);

        if (!$admission) {
            return $this->errorResponse('Online admission not found', null, 404);
        }

        $validated = $request->validate([
            'form_status' => 'required|string|in:pending,approved,rejected',
        ]);

        $admission->update($validated);

        return $this->successResponse(['admission' => $admission], 'Online admission status updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $admission = OnlineStudent::find($id);

        if (!$admission) {
            return $this->errorResponse('Online admission not found', null, 404);
        }

        $admission->delete();

        return $this->successResponse(null, 'Online admission deleted successfully');
    }
}
