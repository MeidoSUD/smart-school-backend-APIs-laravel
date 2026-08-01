<?php

namespace App\Http\Controllers\Api;

use App\Models\ClassSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;

class ClassSectionController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('ClassSectionController');
    }

    public function index(Request $request): JsonResponse
    {
        $classSections = DB::table('class_sections')
            ->join('classes', 'classes.id', '=', 'class_sections.class_id')
            ->join('sections', 'sections.id', '=', 'class_sections.section_id')
            ->select('class_sections.*', 'classes.class', 'sections.section')
            ->get();

        return $this->successResponse(['class_sections' => $classSections]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'is_active'  => 'nullable|string',
        ]);

        $id = DB::table('class_sections')->insertGetId($validated);

        $classSection = DB::table('class_sections')
            ->join('classes', 'classes.id', '=', 'class_sections.class_id')
            ->join('sections', 'sections.id', '=', 'class_sections.section_id')
            ->where('class_sections.id', $id)
            ->select('class_sections.*', 'classes.class', 'sections.section')
            ->first();

        return $this->successResponse(['class_section' => $classSection], 'Class section created successfully', 201);
    }

    public function show($id): JsonResponse
    {
        $classSection = DB::table('class_sections')
            ->join('classes', 'classes.id', '=', 'class_sections.class_id')
            ->join('sections', 'sections.id', '=', 'class_sections.section_id')
            ->where('class_sections.id', $id)
            ->select('class_sections.*', 'classes.class', 'sections.section')
            ->first();

        if (!$classSection) {
            return $this->errorResponse('Class section not found', null, 404);
        }

        return $this->successResponse(['class_section' => $classSection]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $classSection = DB::table('class_sections')->where('id', $id)->first();

        if (!$classSection) {
            return $this->errorResponse('Class section not found', null, 404);
        }

        $validated = $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'is_active'  => 'nullable|string',
        ]);

        DB::table('class_sections')->where('id', $id)->update($validated);

        $updated = DB::table('class_sections')
            ->join('classes', 'classes.id', '=', 'class_sections.class_id')
            ->join('sections', 'sections.id', '=', 'class_sections.section_id')
            ->where('class_sections.id', $id)
            ->select('class_sections.*', 'classes.class', 'sections.section')
            ->first();

        return $this->successResponse(['class_section' => $updated], 'Class section updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $classSection = DB::table('class_sections')->where('id', $id)->first();

        if (!$classSection) {
            return $this->errorResponse('Class section not found', null, 404);
        }

        DB::table('class_sections')->where('id', $id)->delete();

        return $this->successResponse(null, 'Class section deleted successfully');
    }
}
