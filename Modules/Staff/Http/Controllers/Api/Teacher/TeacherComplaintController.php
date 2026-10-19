<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * شكاوى — parity مع CI admin/Complaint:
 * index (list+types+sources) / details / store / update / delete
 * ملاحظة CI: image عبر media_storage uploads/front_office/complaints — هنا يُقبل image كمسار string.
 */
class TeacherComplaintController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherComplaintController');
    }

    public function index(Request $request): JsonResponse
    {
        $this->context($request);

        $list = DB::table('complaint')
            ->leftJoin('complaint_type as ct', 'ct.id', '=', 'complaint.complaint_type')
            ->select('complaint.*', 'ct.complaint_type as type_name')
            ->orderByDesc('complaint.id')->paginate(25);
        $types = DB::table('complaint_type')->select('id', 'complaint_type')->get();

        return $this->successResponse(['complaints' => $list, 'complaint_types' => $types]);
    }

    public function details(Request $request, int $id): JsonResponse
    {
        $this->context($request);
        $row = DB::table('complaint')->where('id', $id)->first();
        if (! $row) {
            return $this->errorResponse('Complaint not found', null, 404);
        }

        return $this->successResponse(['complaint' => $row]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->context($request);
        $v = $request->validate([
            'complaint_type' => 'nullable|integer',
            'source' => 'nullable|string|max:100',
            'name' => 'required|string|max:100',
            'contact' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'action_taken' => 'nullable|string',
            'assigned' => 'nullable|string|max:100',
            'note' => 'nullable|string',
            'image' => 'nullable|string|max:255',
        ]);

        $id = DB::table('complaint')->insertGetId($v);

        return $this->successResponse(['id' => $id], 'Complaint created successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->context($request);
        $row = DB::table('complaint')->where('id', $id)->first();
        if (! $row) {
            return $this->errorResponse('Complaint not found', null, 404);
        }

        $v = $request->validate([
            'complaint_type' => 'sometimes|nullable|integer',
            'source' => 'sometimes|nullable|string|max:100',
            'name' => 'sometimes|string|max:100',
            'contact' => 'sometimes|nullable|string|max:30',
            'description' => 'sometimes|nullable|string',
            'action_taken' => 'sometimes|nullable|string',
            'assigned' => 'sometimes|nullable|string|max:100',
            'note' => 'sometimes|nullable|string',
            'image' => 'sometimes|nullable|string|max:255',
        ]);

        DB::table('complaint')->where('id', $id)->update($v);

        return $this->successResponse(null, 'Complaint updated successfully');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->context($request);
        $row = DB::table('complaint')->where('id', $id)->first();
        if (! $row) {
            return $this->errorResponse('Complaint not found', null, 404);
        }
        DB::table('complaint')->where('id', $id)->delete();

        return $this->successResponse(null, 'Complaint deleted successfully');
    }
}
