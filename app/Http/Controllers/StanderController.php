<?php

namespace App\Http\Controllers;

use App\Models\Outcome;
use App\Models\Stander;
use Illuminate\Http\Request;

class StanderController extends Controller
{
    public function index()
    {
        return response()->json(Stander::with('outcomes')->get());
    }

    public function bySubject($subjectId)
    {
        return response()->json(
            Stander::where('subject_id', $subjectId)->with('outcomes')->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
        ]);

        $stander = Stander::create($request->only([
            'subject_id',
            'name',
            'code',
            'session_id',
            'status',
        ]));

        return response()->json([
            'message' => 'Stander created successfully.',
            'data' => $stander,
        ], 201);
    }

    public function storeOutcome(Request $request, $standerId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
            'level' => 'required|integer|min:1|max:3',
        ]);

        $outcome = Outcome::create([
            'stander_id' => $standerId,
            'name' => $request->name,
            'code' => $request->code,
            'level' => $request->level,
            'session_id' => $request->session_id,
            'status' => $request->status ?? 0,
        ]);

        return response()->json([
            'message' => 'Outcome created successfully.',
            'data' => $outcome,
        ], 201);
    }
}
